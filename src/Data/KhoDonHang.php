<?php

declare(strict_types=1);

namespace App\Data;

use PDO;
use Throwable;

/**
 * Lớp truy cập dữ liệu của don_hang + chi_tiet_don.
 * Đặt hàng chạy trong MỘT giao dịch: hoặc đủ (đơn + chi tiết + trừ kho),
 * hoặc không có gì thay đổi.
 */
final class KhoDonHang
{
    /** Danh sách trạng thái cho phép (trùng ENUM trong schema.sql). */
    public const TRANG_THAI = [
        'cho_xu_ly'  => 'Chờ xử lý',
        'dang_giao'  => 'Đang giao',
        'hoan_thanh' => 'Hoàn thành',
        'da_huy'     => 'Đã hủy',
    ];

    public function __construct(private PDO $db) {}

    /**
     * Tạo đơn từ giỏ hàng; trả id đơn mới.
     *
     * Với mỗi món: UPDATE … SET ton_kho = ton_kho - ? WHERE id = ? AND ton_kho >= ?
     * rồi kiểm tra rowCount() — điều kiện tồn kho nằm NGAY TRONG câu UPDATE nên
     * hai khách mua món cuối cùng cùng lúc thì chỉ một người thành công
     * (khác với SELECT kiểm tra trước rồi UPDATE sau — hở giữa hai bước).
     * Chi tiết đơn lấy đơn giá từ san_pham.gia tại thời điểm đặt (INSERT … SELECT).
     * Thiếu hàng → rollBack toàn bộ và ném KhongDuHangException.
     *
     * @param array<int|string, int> $gioHang khóa: id sản phẩm, giá trị: số lượng
     * @throws KhongDuHangException
     */
    public function tao(int $nguoiDungId, array $gioHang, ?string $ghiChu = null): int
    {
        if ($gioHang === []) {
            throw new \InvalidArgumentException('Giỏ hàng trống.');
        }

        // Sắp theo id sản phẩm: mọi giao dịch khóa dòng cùng một thứ tự → ít deadlock.
        ksort($gioHang);

        $this->db->beginTransaction();

        try {
            $st = $this->db->prepare(
                'INSERT INTO don_hang (nguoi_dung_id, ghi_chu) VALUES (?, ?)'
            );
            $st->execute([$nguoiDungId, $ghiChu]);
            $donId = (int) $this->db->lastInsertId();

            $truKho = $this->db->prepare(
                'UPDATE san_pham SET ton_kho = ton_kho - ?
                  WHERE id = ? AND ton_kho >= ?'
            );
            $themDong = $this->db->prepare(
                'INSERT INTO chi_tiet_don (don_hang_id, san_pham_id, so_luong, don_gia)
                 SELECT ?, id, ?, gia FROM san_pham WHERE id = ?'
            );

            foreach ($gioHang as $spId => $soLuong) {
                $spId    = (int) $spId;
                $soLuong = (int) $soLuong;

                if ($spId < 1 || $soLuong < 1) {
                    throw new \InvalidArgumentException('Giỏ hàng không hợp lệ.');
                }

                $truKho->execute([$soLuong, $spId, $soLuong]);

                if ($truKho->rowCount() !== 1) {
                    throw new KhongDuHangException(
                        'Một sản phẩm trong giỏ không còn đủ hàng.'
                    );
                }

                $themDong->execute([$donId, $soLuong, $spId]);
            }

            $this->db->commit();

            return $donId;
        } catch (Throwable $loi) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $loi;
        }
    }

    /**
     * "Đơn hàng của tôi": danh sách đơn kèm tổng tiền, mới nhất trước.
     *
     * @return list<array{id:int, trang_thai:string, ngay_dat:string, so_mon:int, tong_tien:int}>
     */
    public function cuaNguoiDung(int $nguoiDungId): array
    {
        $st = $this->db->prepare(
            'SELECT dh.id, dh.trang_thai, dh.ngay_dat,
                    SUM(ct.so_luong)              AS so_mon,
                    SUM(ct.so_luong * ct.don_gia) AS tong_tien
               FROM don_hang dh
               JOIN chi_tiet_don ct ON ct.don_hang_id = dh.id
              WHERE dh.nguoi_dung_id = ?
           GROUP BY dh.id, dh.trang_thai, dh.ngay_dat
           ORDER BY dh.ngay_dat DESC, dh.id DESC'
        );
        $st->execute([$nguoiDungId]);

        return array_map($this->epKieuDong(...), $st->fetchAll());
    }

    /**
     * Quản trị viên: mọi đơn kèm tên khách và tổng tiền.
     *
     * @return list<array<string, mixed>>
     */
    public function tatCa(): array
    {
        $st = $this->db->query(
            'SELECT dh.id, dh.trang_thai, dh.ngay_dat,
                    nd.email, nd.ho_ten,
                    SUM(ct.so_luong)              AS so_mon,
                    SUM(ct.so_luong * ct.don_gia) AS tong_tien
               FROM don_hang dh
               JOIN nguoi_dung nd  ON nd.id = dh.nguoi_dung_id
               JOIN chi_tiet_don ct ON ct.don_hang_id = dh.id
           GROUP BY dh.id, dh.trang_thai, dh.ngay_dat, nd.email, nd.ho_ten
           ORDER BY dh.ngay_dat DESC, dh.id DESC'
        );

        return array_map($this->epKieuDong(...), $st->fetchAll());
    }

    /**
     * Chi tiết một đơn. $nguoiDungId khác null → chỉ đơn của người đó
     * (AND nguoi_dung_id = ?); đơn của người khác trả null → trang báo 404,
     * không lộ là đơn có tồn tại. Quản trị viên truyền null để xem mọi đơn.
     *
     * @return array{don: array<string, mixed>, dong: list<array<string, mixed>>}|null
     */
    public function chiTiet(int $donId, ?int $nguoiDungId): ?array
    {
        $sql = 'SELECT dh.id, dh.nguoi_dung_id, dh.trang_thai, dh.ghi_chu, dh.ngay_dat,
                       nd.email, nd.ho_ten
                  FROM don_hang dh
                  JOIN nguoi_dung nd ON nd.id = dh.nguoi_dung_id
                 WHERE dh.id = ?';
        $ts = [$donId];

        if ($nguoiDungId !== null) {
            $sql .= ' AND dh.nguoi_dung_id = ?';
            $ts[] = $nguoiDungId;
        }

        $st = $this->db->prepare($sql);
        $st->execute($ts);
        $don = $st->fetch();

        if (!$don) {
            return null;
        }

        $st = $this->db->prepare(
            'SELECT ct.san_pham_id, sp.ten, ct.so_luong, ct.don_gia,
                    ct.so_luong * ct.don_gia AS thanh_tien
               FROM chi_tiet_don ct
               JOIN san_pham sp ON sp.id = ct.san_pham_id
              WHERE ct.don_hang_id = ?
           ORDER BY sp.ten'
        );
        $st->execute([$donId]);

        $dong = array_map($this->epKieuDong(...), $st->fetchAll());
        $don['tong_tien'] = array_sum(array_column($dong, 'thanh_tien'));

        return ['don' => $don, 'dong' => $dong];
    }

    /**
     * Đổi trạng thái (chỉ quản trị viên gọi). Trạng thái mới phải thuộc
     * danh sách cho phép, nếu không trả false và không chạy SQL.
     */
    public function doiTrangThai(int $donId, string $trangThai): bool
    {
        if (!array_key_exists($trangThai, self::TRANG_THAI)) {
            return false;
        }

        $st = $this->db->prepare('UPDATE don_hang SET trang_thai = ? WHERE id = ?');
        $st->execute([$trangThai, $donId]);

        // rowCount() = 0 cũng xảy ra khi trạng thái không đổi, nên kiểm tra tồn tại riêng.
        if ($st->rowCount() > 0) {
            return true;
        }

        $st = $this->db->prepare('SELECT 1 FROM don_hang WHERE id = ?');
        $st->execute([$donId]);

        return $st->fetchColumn() !== false;
    }

    /**
     * SUM() trả chuỗi (DECIMAL) → ép các cột số về int.
     *
     * @param array<string, mixed> $dong
     * @return array<string, mixed>
     */
    private function epKieuDong(array $dong): array
    {
        foreach (['id', 'so_mon', 'tong_tien', 'so_luong', 'don_gia', 'thanh_tien', 'san_pham_id'] as $cot) {
            if (isset($dong[$cot])) {
                $dong[$cot] = (int) $dong[$cot];
            }
        }

        return $dong;
    }
}
