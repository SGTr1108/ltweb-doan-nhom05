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

/**
 * Trả JSON kèm mã trạng thái rồi dừng.
 */
function traJson(array $duLieu, int $maTrangThai = 200): never
{
    http_response_code($maTrangThai);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($duLieu, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Một sự kiện bảo mật = một dòng trong logs/bao-mat.log.
 * Không bao giờ ghi mật khẩu, mã phiên, mã CSRF.
 */
function ghiLogBaoMat(string $suKien, array $chiTiet = []): void
{
    $chiTiet += [
        'ip'  => $_SERVER['REMOTE_ADDR'] ?? '-',
        'uid' => $_SESSION['uid'] ?? null,
    ];
    $json = json_encode($chiTiet, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $dong = date('Y-m-d H:i:s') . " $suKien $json";
    error_log($dong . PHP_EOL, 3, __DIR__ . '/../logs/bao-mat.log');
}
