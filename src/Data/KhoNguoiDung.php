<?php

declare(strict_types=1);

namespace App\Data;

use PDO;

/**
 * Lớp truy cập dữ liệu của nguoi_dung. Mật khẩu chỉ là chuỗi băm
 * password_hash(); vai_tro không bao giờ lấy từ form (tao() luôn là khach_hang).
 */
final class KhoNguoiDung
{
    public function __construct(private PDO $db) {}

    /**
     * Dùng khi đăng nhập: có cả cột mat_khau (chuỗi băm) để password_verify.
     *
     * @return array{id:int, email:string, ho_ten:string, mat_khau:string, vai_tro:string}|null
     */
    public function timTheoEmail(string $email): ?array
    {
        $st = $this->db->prepare(
            'SELECT id, email, ho_ten, mat_khau, vai_tro
               FROM nguoi_dung WHERE email = ?'
        );
        $st->execute([$email]);
        $dong = $st->fetch();

        return $dong ?: null;
    }

    /**
     * Người dùng hiện tại theo uid trong session — KHÔNG trả mat_khau.
     *
     * @return array{id:int, email:string, ho_ten:string, vai_tro:string}|null
     */
    public function timTheoId(int $id): ?array
    {
        $st = $this->db->prepare(
            'SELECT id, email, ho_ten, vai_tro FROM nguoi_dung WHERE id = ?'
        );
        $st->execute([$id]);
        $dong = $st->fetch();

        return $dong ?: null;
    }

    /**
     * Đăng ký tài khoản khách hàng; trả id mới. Email trùng → PDOException
     * mã 1062 (trang bắt bằng maLoiSql() và báo "email đã được đăng ký").
     */
    public function tao(string $email, string $hoTen, string $matKhauBam): int
    {
        $st = $this->db->prepare(
            "INSERT INTO nguoi_dung (email, ho_ten, mat_khau, vai_tro)
             VALUES (?, ?, ?, 'khach_hang')"
        );
        $st->execute([$email, $hoTen, $matKhauBam]);

        return (int) $this->db->lastInsertId();
    }

    /** Lưu lại chuỗi băm mới khi password_needs_rehash() báo true. */
    public function capNhatMatKhau(int $id, string $matKhauBam): void
    {
        $st = $this->db->prepare('UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?');
        $st->execute([$matKhauBam, $id]);
    }
}
