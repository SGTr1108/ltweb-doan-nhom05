<?php
declare(strict_types=1);

namespace App\Models;

final class SanPham
{
    public function __construct(
        private string $id,
        private string $ten,
        private int $gia,
        private string $danhMuc,
        private int $tonKho,
        private string $anh
    ) {
        if ($this->id === '' || $this->ten === '') {
            throw new \InvalidArgumentException(
                'Mã và tên sản phẩm không được để trống.'
            );
        }

        if ($this->gia < 0 || $this->tonKho < 0) {
            throw new \InvalidArgumentException(
                'Giá và tồn kho không được âm.'
            );
        }
    }

    public static function fromArray(array $duLieu): self
    {
        $cacTruong = [
            'id', 'ten', 'gia', 'danhMuc', 'tonKho', 'anh'
        ];

        foreach ($cacTruong as $truong) {
            if (!array_key_exists($truong, $duLieu)) {
                throw new \InvalidArgumentException(
                    "Thiếu trường dữ liệu: {$truong}"
                );
            }
        }

        return new self(
            (string) $duLieu['id'],
            (string) $duLieu['ten'],
            (int) $duLieu['gia'],
            (string) $duLieu['danhMuc'],
            (int) $duLieu['tonKho'],
            (string) $duLieu['anh']
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTen(): string
    {
        return $this->ten;
    }

    public function getGia(): int
    {
        return $this->gia;
    }

    public function getDanhMuc(): string
    {
        return $this->danhMuc;
    }

    public function getTonKho(): int
    {
        return $this->tonKho;
    }

    public function getAnh(): string
    {
        return $this->anh;
    }
}