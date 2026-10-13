<?php
declare(strict_types=1);

namespace App\Data;

use App\Models\SanPham;
use JsonException;
use RuntimeException;

final class KhoSanPham
{
    private string $duongDan;

    public function __construct()
    {
        $this->duongDan = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR
            . 'data'
            . DIRECTORY_SEPARATOR
            . 'san-pham.json';
    }

    /**
     * Đọc danh sách sản phẩm từ tệp JSON.
     *
     * @return list<SanPham>
     */
    public function layDanhSach(): array
    {
        if (!is_file($this->duongDan)) {
            throw new RuntimeException(
                'Không tìm thấy tệp dữ liệu sản phẩm.'
            );
        }

        $noiDung = file_get_contents($this->duongDan);

        if ($noiDung === false) {
            throw new RuntimeException(
                'Không thể đọc tệp dữ liệu sản phẩm.'
            );
        }

        try {
            $duLieu = json_decode(
                $noiDung,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $loi) {
            throw new RuntimeException(
                'Tệp dữ liệu sản phẩm không phải JSON hợp lệ.',
                0,
                $loi
            );
        }

        if (!is_array($duLieu)) {
            throw new RuntimeException(
                'Dữ liệu sản phẩm phải là một danh sách.'
            );
        }

        $danhSach = [];

        foreach ($duLieu as $banGhi) {
            if (!is_array($banGhi)) {
                throw new RuntimeException(
                    'Một bản ghi sản phẩm không hợp lệ.'
                );
            }

            $danhSach[] = SanPham::fromArray($banGhi);
        }

        return $danhSach;
    }

    /**
     * Tìm sản phẩm theo mã.
     */
    public function timTheoId(string $id): ?SanPham
    {
        foreach ($this->layDanhSach() as $sanPham) {
            if ($sanPham->getId() === $id) {
                return $sanPham;
            }
        }

        return null;
    }
}