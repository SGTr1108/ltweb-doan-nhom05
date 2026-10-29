<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\KhoSanPham;
use InvalidArgumentException;
use RuntimeException;

final class GioHang
{
    private const KHOA_SESSION = 'gio_hang';

    public function __construct(
        private KhoSanPham $khoSanPham
    ) {
        if (
            session_status() === PHP_SESSION_NONE
            && !session_start()
        ) {
            throw new RuntimeException(
                'Không thể khởi tạo phiên làm việc.'
            );
        }

        if (
            !isset($_SESSION[self::KHOA_SESSION])
            || !is_array($_SESSION[self::KHOA_SESSION])
        ) {
            $_SESSION[self::KHOA_SESSION] = [];
        }
    }

    /**
     * Thêm sản phẩm vào giỏ hàng.
     */
    public function them(string $id, int $soLuong = 1): void
    {
        if ($soLuong < 1) {
            throw new InvalidArgumentException(
                'Số lượng thêm phải lớn hơn 0.'
            );
        }

        $sanPham = $this->khoSanPham->timTheoId($id);

        if ($sanPham === null) {
            throw new InvalidArgumentException(
                'Sản phẩm không tồn tại.'
            );
        }

        if ($sanPham->getTonKho() < 1) {
            throw new RuntimeException(
                'Sản phẩm đã hết hàng.'
            );
        }

        $khoa = $sanPham->getId();
        $soLuongHienTai = (int) ($_SESSION[self::KHOA_SESSION][$khoa] ?? 0);
        $soLuongMoi = $soLuongHienTai + $soLuong;

        if ($soLuongMoi > $sanPham->getTonKho()) {
            throw new RuntimeException(
                'Số lượng vượt quá hàng tồn kho.'
            );
        }

        $_SESSION[self::KHOA_SESSION][$khoa] = $soLuongMoi;
    }

    /**
     * Cập nhật số lượng một sản phẩm trong giỏ.
     */
    public function capNhatSoLuong(string $id, int $soLuong): void
    {
        if ($soLuong < 1) {
            throw new InvalidArgumentException(
                'Số lượng phải lớn hơn hoặc bằng 1.'
            );
        }

        $sanPham = $this->khoSanPham->timTheoId($id);

        if ($sanPham === null) {
            throw new InvalidArgumentException(
                'Sản phẩm không tồn tại.'
            );
        }

        if ($soLuong > $sanPham->getTonKho()) {
            throw new RuntimeException(
                'Số lượng vượt quá hàng tồn kho.'
            );
        }

        $_SESSION[self::KHOA_SESSION][$sanPham->getId()] = $soLuong;
    }

    /**
     * Xóa một sản phẩm khỏi giỏ.
     */
    public function xoa(string $id): void
    {
        unset($_SESSION[self::KHOA_SESSION][(int) $id]);
    }

    /**
     * Lấy các sản phẩm trong giỏ cùng số lượng và thành tiền.
     * Cả giỏ lấy bằng MỘT câu WHERE id IN (?, …), không truy vấn trong vòng lặp.
     *
     * @return list<array{
     *     sanPham: \App\Models\SanPham,
     *     soLuong: int,
     *     thanhTien: int
     * }>
     */
    public function layDanhSach(): array
    {
        $gio = $_SESSION[self::KHOA_SESSION];
        $dsSanPham = $this->khoSanPham->timTheoNhieuId(array_keys($gio));
        $danhSach = [];

        foreach ($gio as $id => $soLuong) {
            $sanPham = $dsSanPham[(int) $id] ?? null;
            $soLuong = (int) $soLuong;

            if ($sanPham === null || $soLuong < 1) {
                unset($_SESSION[self::KHOA_SESSION][$id]);
                continue;
            }

            $danhSach[] = [
                'sanPham' => $sanPham,
                'soLuong' => $soLuong,
                'thanhTien' => $sanPham->getGia() * $soLuong,
            ];
        }

        return $danhSach;
    }

    /**
     * Giỏ hàng dạng [id sản phẩm => số lượng] để truyền cho KhoDonHang::tao().
     *
     * @return array<int, int>
     */
    public function layMang(): array
    {
        return array_map('intval', $_SESSION[self::KHOA_SESSION]);
    }

    /**
     * Tính tổng tiền giỏ hàng.
     */
    public function tinhTongTien(): int
    {
        $tongTien = 0;

        foreach ($this->layDanhSach() as $matHang) {
            $tongTien += $matHang['thanhTien'];
        }

        return $tongTien;
    }

    /**
     * Đếm tổng số sản phẩm trong giỏ (không cần truy vấn CSDL).
     */
    public function demSoLuong(): int
    {
        return array_sum(
            array_map(
                'intval',
                $_SESSION[self::KHOA_SESSION]
            )
        );
    }

    /**
     * Xóa toàn bộ giỏ hàng.
     */
    public function xoaTatCa(): void
    {
        $_SESSION[self::KHOA_SESSION] = [];
    }
}
