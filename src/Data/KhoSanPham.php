<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\SanPham;
use PDO;

/**
 * Lớp truy cập dữ liệu của sản phẩm: MỌI câu SQL về san_pham nằm ở đây.
 * Dữ liệu từ người dùng luôn đi qua prepare + execute/bindValue.
 */
final class KhoSanPham
{
    /** Cột và bảng dùng chung cho các câu SELECT trả về SanPham. */
    private const SELECT = 'SELECT sp.*, dm.ten AS danh_muc_ten
                            FROM san_pham sp
                            JOIN danh_muc dm ON dm.id = sp.danh_muc_id';

    public function __construct(private PDO $db) {}

    /** @return list<SanPham> */
    public function tatCa(): array
    {
        $st = $this->db->query(self::SELECT . ' ORDER BY sp.id');

        return array_map(SanPham::tuMang(...), $st->fetchAll());
    }

    /** Giữ tên cũ của Bài tập 5 để các trang chưa phải sửa. */
    public function layDanhSach(): array
    {
        return $this->tatCa();
    }

    /**
     * Tìm một sản phẩm theo id. Id không phải số nguyên dương
     * (vd "SP001", "abc") → null, để trang trả 404.
     */
    public function timTheoId(int|string $id): ?SanPham
    {
        $so = $this->laySoNguyenDuong($id);

        if ($so === null) {
            return null;
        }

        $st = $this->db->prepare(self::SELECT . ' WHERE sp.id = ?');
        $st->execute([$so]);
        $dong = $st->fetch();

        return $dong ? SanPham::tuMang($dong) : null;
    }

    /**
     * Nhiều sản phẩm bằng MỘT câu WHERE id IN (?, …) — dùng cho giỏ hàng,
     * khối "Đã xem gần đây", tránh truy vấn trong vòng lặp (N + 1).
     *
     * @param array<int|string> $dsId
     * @return array<int, SanPham> khóa là id sản phẩm
     */
    public function timTheoNhieuId(array $dsId): array
    {
        $ids = [];

        foreach ($dsId as $id) {
            $so = $this->laySoNguyenDuong($id);

            if ($so !== null) {
                $ids[$so] = $so;
            }
        }

        if ($ids === []) {
            return [];
        }

        $ids = array_values($ids);
        $dau = implode(', ', array_fill(0, count($ids), '?'));
        $st  = $this->db->prepare(self::SELECT . " WHERE sp.id IN ($dau)");
        $st->execute($ids);

        $kq = [];

        foreach ($st->fetchAll() as $dong) {
            $kq[(int) $dong['id']] = SanPham::tuMang($dong);
        }

        return $kq;
    }

    /**
     * Sản phẩm mới nhất (trang chủ).
     *
     * @return list<SanPham>
     */
    public function moiNhat(int $soLuong = 4): array
    {
        $st = $this->db->prepare(
            self::SELECT . ' ORDER BY sp.ngay_tao DESC, sp.id DESC LIMIT ?'
        );
        $st->bindValue(1, $soLuong, PDO::PARAM_INT);
        $st->execute();

        return array_map(SanPham::tuMang(...), $st->fetchAll());
    }

    /**
     * Các sản phẩm cùng danh mục (trang chi tiết), trừ chính nó.
     *
     * @return list<SanPham>
     */
    public function cungDanhMuc(
        int $danhMucId,
        int $loaiTru,
        int $soLuong = 4
    ): array {
        $st = $this->db->prepare(
            self::SELECT . ' WHERE sp.danh_muc_id = ? AND sp.id <> ?
                             ORDER BY sp.id LIMIT ?'
        );
        $st->bindValue(1, $danhMucId, PDO::PARAM_INT);
        $st->bindValue(2, $loaiTru, PDO::PARAM_INT);
        $st->bindValue(3, $soLuong, PDO::PARAM_INT);
        $st->execute();

        return array_map(SanPham::tuMang(...), $st->fetchAll());
    }

    /**
     * Bộ lọc của trang danh sách → [$where, $ts]. dem() và trang() cùng gọi
     * hàm này nên hai câu SQL luôn có đúng một điều kiện lọc.
     *
     * $loc: q (string), dm (int|false|null), giaToiDa (int|false|null)
     *
     * @param array<string, mixed> $loc
     * @return array{0: string, 1: list<int|string>}
     */
    private function dieuKien(array $loc): array
    {
        $dk = [];
        $ts = [];

        if (($loc['q'] ?? '') !== '') {
            $dk[] = 'sp.ten LIKE ?';
            // Thoát % _ \ để người dùng gõ "50%" chỉ khớp đúng chuỗi "50%".
            $ts[] = '%' . addcslashes((string) $loc['q'], '%_\\') . '%';
        }

        if (!empty($loc['dm'])) {
            $dk[] = 'sp.danh_muc_id = ?';
            $ts[] = (int) $loc['dm'];
        }

        if (!empty($loc['giaToiDa'])) {
            $dk[] = 'sp.gia <= ?';
            $ts[] = (int) $loc['giaToiDa'];
        }

        $where = $dk ? 'WHERE ' . implode(' AND ', $dk) : '';

        return [$where, $ts];
    }

    /**
     * Số dòng khớp bộ lọc (dùng tính số trang).
     *
     * @param array<string, mixed> $loc
     */
    public function dem(array $loc): int
    {
        [$where, $ts] = $this->dieuKien($loc);
        $st = $this->db->prepare("SELECT COUNT(*) FROM san_pham sp $where");
        $st->execute($ts);

        return (int) $st->fetchColumn();
    }

    /**
     * Một trang sản phẩm. $sx chỉ chọn trong danh sách cho phép, giá trị lạ
     * rơi về mặc định — chuỗi ORDER BY không bao giờ lấy trực tiếp từ người dùng.
     *
     * @param array<string, mixed> $loc
     * @return list<SanPham>
     */
    public function trang(
        array $loc,
        string $sx,
        int $soDong,
        int $boQua
    ): array {
        [$where, $ts] = $this->dieuKien($loc);

        $orderBy = match ($sx) {
            'gia-tang' => 'sp.gia ASC, sp.id',
            'gia-giam' => 'sp.gia DESC, sp.id',
            'ten'      => 'sp.ten ASC, sp.id',
            default    => 'sp.id DESC',        // 'moi' hoặc giá trị lạ
        };

        $st = $this->db->prepare(
            self::SELECT . " $where ORDER BY $orderBy LIMIT ? OFFSET ?"
        );

        $i = 1;

        foreach ($ts as $giaTri) {
            $st->bindValue($i++, $giaTri, is_int($giaTri) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $st->bindValue($i++, max(0, $soDong), PDO::PARAM_INT);
        $st->bindValue($i, max(0, $boQua), PDO::PARAM_INT);
        $st->execute();

        return array_map(SanPham::tuMang(...), $st->fetchAll());
    }

    /**
     * Thêm sản phẩm; trả id mới. Dữ liệu đã được kiểm tra bằng PHP trước.
     * Ném PDOException khi vi phạm ràng buộc (1452: danh mục không tồn tại…).
     *
     * @param array{danh_muc_id:int, ten:string, gia:int, ton_kho:int, anh:?string, mo_ta:?string} $d
     */
    public function them(array $d): int
    {
        $st = $this->db->prepare(
            'INSERT INTO san_pham (danh_muc_id, ten, gia, ton_kho, anh, mo_ta)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $st->execute([
            $d['danh_muc_id'],
            $d['ten'],
            $d['gia'],
            $d['ton_kho'],
            $d['anh'] ?? null,
            $d['mo_ta'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Sửa sản phẩm. Truyền 'anh' => null để giữ ảnh cũ.
     *
     * @param array{danh_muc_id:int, ten:string, gia:int, ton_kho:int, anh?:?string, mo_ta:?string} $d
     */
    public function capNhat(int $id, array $d): bool
    {
        $st = $this->db->prepare(
            'UPDATE san_pham
                SET danh_muc_id = ?, ten = ?, gia = ?, ton_kho = ?,
                    anh = COALESCE(?, anh), mo_ta = ?
              WHERE id = ?'
        );
        $st->execute([
            $d['danh_muc_id'],
            $d['ten'],
            $d['gia'],
            $d['ton_kho'],
            $d['anh'] ?? null,
            $d['mo_ta'] ?? null,
            $id,
        ]);

        return $st->rowCount() > 0;
    }

    /**
     * Xóa sản phẩm. Đã có trong chi_tiet_don → PDOException, mã 1451
     * (trang bắt bằng maLoiSql() và báo "không xóa được").
     */
    public function xoa(int $id): bool
    {
        $st = $this->db->prepare('DELETE FROM san_pham WHERE id = ?');
        $st->execute([$id]);

        return $st->rowCount() > 0;
    }

    /** Chuỗi/số → số nguyên dương, hoặc null nếu không hợp lệ. */
    private function laySoNguyenDuong(int|string $id): ?int
    {
        $so = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $so === false ? null : $so;
    }
}
