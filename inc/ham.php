<?php
declare(strict_types=1);

/**
 * Các hàm dùng chung của website MiniPos.
 * e() xuất dữ liệu an toàn; vnd() định dạng giá tiền Việt Nam đồng.
 */

/**
 * Chuyển dữ liệu thành chuỗi HTML an toàn.
 */
function e(string|int|float|null $giaTri): string
{
    return htmlspecialchars(
        (string) $giaTri,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

/**
 * Định dạng số tiền theo đơn vị Việt Nam đồng.
 */
function vnd(int|float $soTien): string
{
    return number_format((float) $soTien, 0, ',', '.') . ' ₫';
}