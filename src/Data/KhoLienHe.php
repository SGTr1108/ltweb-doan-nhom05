<?php

declare(strict_types=1);

namespace App\Data;

use PDO;

/**
 * Lớp truy cập dữ liệu của lien_he (thay storage/lien-he.jsonl).
 * Giữ nguyên tên khóa của Bài tập 5 (manhanvien, mucdouutien, noidung…)
 * nên lien-he.php và quan-tri.php gần như không phải sửa.
 */
final class KhoLienHe
{
    public function __construct(private PDO $db) {}

    /**
     * Mọi phiếu, mới nhất trước.
     *
     * @return list<array<string, mixed>>
     */
    public function layDanhSach(): array
    {
        $st = $this->db->query(
            'SELECT ma_phieu, thoi_gian_tao,
                    ma_nhan_vien   AS manhanvien,
                    email,
                    ngay_bao_cao   AS ngaybaocao,
                    muc_do_uu_tien AS mucdouutien,
                    noi_dung       AS noidung,
                    loai_yeu_cau   AS loaiyeucau,
                    anh_dinh_kem
               FROM lien_he
           ORDER BY thoi_gian_tao DESC, id DESC'
        );

        return $st->fetchAll();
    }

    /**
     * Thêm một phiếu. $phieu có các khóa như lien-he.php dựng:
     * ma_phieu, manhanvien, email, ngaybaocao, mucdouutien, noidung,
     * loaiyeucau, anh_dinh_kem (thoi_gian_tao do CSDL tự đặt).
     *
     * @param array<string, mixed> $phieu
     */
    public function them(array $phieu): void
    {
        $st = $this->db->prepare(
            'INSERT INTO lien_he
                 (ma_phieu, ma_nhan_vien, email, ngay_bao_cao,
                  muc_do_uu_tien, loai_yeu_cau, noi_dung, anh_dinh_kem)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $st->execute([
            (string) $phieu['ma_phieu'],
            (string) $phieu['manhanvien'],
            (string) $phieu['email'],
            (string) $phieu['ngaybaocao'],
            (int) $phieu['mucdouutien'],
            (string) $phieu['loaiyeucau'],
            (string) $phieu['noidung'],
            $phieu['anh_dinh_kem'] ?? null,
        ]);
    }

    /** Tổng số phiếu (trang quản trị). */
    public function dem(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM lien_he')->fetchColumn();
    }
}
