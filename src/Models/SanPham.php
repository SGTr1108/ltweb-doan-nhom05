<?php

declare(strict_types=1);

namespace App\Models;

use JsonSerializable;

/**
 * Một sản phẩm (một dòng của san_pham JOIN danh_muc).
 * Chỉ jsonSerialize() mới quyết định trường nào ra API — không lộ
 * cột nội bộ như danh_muc_id, ngay_tao.
 */
final class SanPham implements JsonSerializable
{
    public function __construct(
        private int $id,
        private string $ten,
        private int $gia,
        private string $danhMuc,
        private int $tonKho,
        private string $anh,
        private int $danhMucId = 0,
        private string $moTa = '',
        private string $ngayTao = ''
    ) {
        if ($this->id < 0 || $this->ten === '') {
            throw new \InvalidArgumentException(
                'Mã và tên sản phẩm không hợp lệ.'
            );
        }

        if ($this->gia < 0 || $this->tonKho < 0) {
            throw new \InvalidArgumentException(
                'Giá và tồn kho không được âm.'
            );
        }
    }

    /**
     * Dựng từ một dòng kết quả SQL:
     * SELECT sp.*, dm.ten AS danh_muc_ten FROM san_pham sp JOIN danh_muc dm …
     *
     * @param array<string, mixed> $d
     */
    public static function tuMang(array $d): self
    {
        return new self(
            (int) $d['id'],
            (string) $d['ten'],
            (int) $d['gia'],
            (string) ($d['danh_muc_ten'] ?? ''),
            (int) $d['ton_kho'],
            (string) ($d['anh'] ?? ''),
            (int) ($d['danh_muc_id'] ?? 0),
            (string) ($d['mo_ta'] ?? ''),
            (string) ($d['ngay_tao'] ?? '')
        );
    }

    public function getId(): int
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

    public function getDanhMucId(): int
    {
        return $this->danhMucId;
    }

    public function getTonKho(): int
    {
        return $this->tonKho;
    }

    public function getAnh(): string
    {
        return $this->anh;
    }

    public function getMoTa(): string
    {
        return $this->moTa;
    }

    public function getNgayTao(): string
    {
        return $this->ngayTao;
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'id'      => $this->id,
            'ten'     => $this->ten,
            'gia'     => $this->gia,
            'danhMuc' => $this->danhMuc,
            'tonKho'  => $this->tonKho,
            'anh'     => $this->anh,
            'moTa'    => $this->moTa,
        ];
    }
}
