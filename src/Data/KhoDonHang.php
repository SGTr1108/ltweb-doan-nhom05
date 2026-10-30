<?php

declare(strict_types=1);

namespace App\Data;

use PDO;
use Throwable;

final class KhoDonHang
{
    public const TRANG_THAI = [
        'cho_xu_ly'  => 'Chờ xử lý',
        'dang_giao'  => 'Đang giao',
        'hoan_thanh' => 'Hoàn thành',
        'da_huy'     => 'Đã hủy',
    ];

    public function __construct(private PDO $db) {}

    /**
     * Tạo hóa đơn bán hàng tại quầy trong MỘT giao dịch.
     */
    public function tao(
        int $nhanVienId,
        array $gioHang,
        ?string $tenKhach = null,
        ?string $sdtKhach = null,
        int $tienKhachDua = 0,
        ?string $ghiChu = null
    ): int {
        if ($gioHang === []) {
            throw new \InvalidArgumentException('Hóa đơn chưa có sản phẩm nào.');
        }

        ksort($gioHang);
        $this->db->beginTransaction();

        try {
            $st = $this->db->prepare(
                'INSERT INTO don_hang (nhan_vien_id, ten_khach_hang, sdt_khach_hang, tien_khach_dua, ghi_chu, trang_thai) 
                 VALUES (?, ?, ?, ?, ?, "hoan_thanh")'
            );
            $st->execute([
                $nhanVienId,
                $tenKhach !== '' ? $tenKhach : null,
                $sdtKhach !== '' ? $sdtKhach : null,
                $tienKhachDua,
                $ghiChu !== '' ? $ghiChu : null,
            ]);
            $donId = (int) $this->db->lastInsertId();

            $truKho = $this->db->prepare(
                'UPDATE san_pham SET ton_kho = ton_kho - ? WHERE id = ? AND ton_kho >= ?'
            );
            $themChiTiet = $this->db->prepare(
                'INSERT INTO chi_tiet_don (don_hang_id, san_pham_id, so_luong, don_gia)
                 SELECT ?, id, ?, gia FROM san_pham WHERE id = ?'
            );

            foreach ($gioHang as $spId => $soLuong) {
                $spId    = (int) $spId;
                $soLuong = (int) $soLuong;

                if ($spId < 1 || $soLuong < 1) {
                    throw new \InvalidArgumentException('Số lượng sản phẩm không hợp lệ.');
                }

                $truKho->execute([$soLuong, $spId, $soLuong]);
                if ($truKho->rowCount() !== 1) {
                    throw new KhongDuHangException('Một sản phẩm trong hóa đơn không còn đủ tồn kho.');
                }

                $themChiTiet->execute([$donId, $soLuong, $spId]);
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
     * Danh sách đơn hàng do nhân viên lập.
     */
    public function cuaNhanVien(int $nhanVienId): array
    {
        $st = $this->db->prepare(
            'SELECT dh.id, dh.trang_thai, dh.ngay_dat, dh.ten_khach_hang, dh.sdt_khach_hang, dh.tien_khach_dua,
                    SUM(ct.so_luong)              AS so_mon,
                    SUM(ct.so_luong * ct.don_gia) AS tong_tien
               FROM don_hang dh
               JOIN chi_tiet_don ct ON ct.don_hang_id = dh.id
              WHERE dh.nhan_vien_id = ?
           GROUP BY dh.id, dh.trang_thai, dh.ngay_dat, dh.ten_khach_hang, dh.sdt_khach_hang, dh.tien_khach_dua
           ORDER BY dh.ngay_dat DESC, dh.id DESC'
        );
        $st->execute([$nhanVienId]);

        return array_map($this->epKieuDong(...), $st->fetchAll());
    }

    /**
     * Tất cả hóa đơn trong hệ thống.
     */
    public function tatCa(): array
    {
        $st = $this->db->query(
            'SELECT dh.id, dh.trang_thai, dh.ngay_dat, dh.ten_khach_hang, dh.sdt_khach_hang, dh.tien_khach_dua,
                    nd.ho_ten AS ten_nhan_vien,
                    SUM(ct.so_luong)              AS so_mon,
                    SUM(ct.so_luong * ct.don_gia) AS tong_tien
               FROM don_hang dh
               JOIN nguoi_dung nd  ON nd.id = dh.nhan_vien_id
               JOIN chi_tiet_don ct ON ct.don_hang_id = dh.id
           GROUP BY dh.id, dh.trang_thai, dh.ngay_dat, dh.ten_khach_hang, dh.sdt_khach_hang, dh.tien_khach_dua, nd.ho_ten
           ORDER BY dh.ngay_dat DESC, dh.id DESC'
        );

        return array_map($this->epKieuDong(...), $st->fetchAll());
    }

    /**
     * Chi tiết một hóa đơn.
     */
    public function chiTiet(int $donId, ?int $nhanVienId = null): ?array
    {
        $sql = 'SELECT dh.id, dh.nhan_vien_id, dh.trang_thai, dh.ghi_chu, dh.ngay_dat,
                       dh.ten_khach_hang, dh.sdt_khach_hang, dh.tien_khach_dua,
                       nd.email AS email_nhan_vien, nd.ho_ten AS ten_nhan_vien
                  FROM don_hang dh
                  JOIN nguoi_dung nd ON nd.id = dh.nhan_vien_id
                 WHERE dh.id = ?';
        $params = [$donId];

        if ($nhanVienId !== null) {
            $sql .= ' AND dh.nhan_vien_id = ?';
            $params[] = $nhanVienId;
        }

        $st = $this->db->prepare($sql);
        $st->execute($params);
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
        $don['tien_thoi_lai'] = max(0, (int)$don['tien_khach_dua'] - (int)$don['tong_tien']);

        return ['don' => $don, 'dong' => $dong];
    }

    public function doiTrangThai(int $donId, string $trangThai): bool
    {
        if (!array_key_exists($trangThai, self::TRANG_THAI)) {
            return false;
        }

        $st = $this->db->prepare('UPDATE don_hang SET trang_thai = ? WHERE id = ?');
        $st->execute([$trangThai, $donId]);

        if ($st->rowCount() > 0) {
            return true;
        }

        $st = $this->db->prepare('SELECT 1 FROM don_hang WHERE id = ?');
        $st->execute([$donId]);

        return $st->fetchColumn() !== false;
    }

    private function epKieuDong(array $dong): array
    {
        foreach (['id', 'so_mon', 'tong_tien', 'so_luong', 'don_gia', 'thanh_tien', 'san_pham_id', 'tien_khach_dua'] as $cot) {
            if (isset($dong[$cot])) {
                $dong[$cot] = (int) $dong[$cot];
            }
        }

        return $dong;
    }
}
