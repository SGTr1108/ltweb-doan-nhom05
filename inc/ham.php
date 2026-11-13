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
 * Chỉ nhận dữ liệu chuỗi từ request; giá trị dạng mảng/object được coi là rỗng.
 */
function chuoi(mixed $giaTri): string
{
    return is_string($giaTri) ? $giaTri : '';
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
 * Ghi sự kiện bảo mật vào logs/bao-mat.log.
 * Chỉ cho phép 3 sự kiện được quy định.
 * Không ghi mật khẩu, mã phiên, mã CSRF hoặc dữ liệu tùy ý.
 */
function ghiLogBaoMat(string $suKien, array $chiTiet = []): void
{
    $suKienHopLe = [
        'csrf_sai',
        'cam_truy_cap',
        'dang_nhap_sai',
    ];

    if (!in_array($suKien, $suKienHopLe, true)) {
        return;
    }

    // Chỉ lấy các trường được phép ghi.
    $chiTietAnToan = [];

    if (isset($chiTiet['trang'])) {
        $trang = parse_url((string) $chiTiet['trang'], PHP_URL_PATH);
        $chiTietAnToan['trang'] = is_string($trang) ? $trang : '-';
    }

    if (isset($chiTiet['vai_tro_yeu_cau'])) {
        $chiTietAnToan['vai_tro_yeu_cau'] =
            (string) $chiTiet['vai_tro_yeu_cau'];
    }

    if (
        isset($chiTiet['email_hash'])
        && preg_match('/^[a-f0-9]{64}$/', (string) $chiTiet['email_hash'])
    ) {
        $chiTietAnToan['email_hash'] = $chiTiet['email_hash'];
    }

    if (isset($chiTiet['so_lan'])) {
        $chiTietAnToan['so_lan'] = (int) $chiTiet['so_lan'];
    }

    // IP lấy trực tiếp từ kết nối; uid chỉ có khi đã đăng nhập.
    $banGhi = [
        'ip'  => $_SERVER['REMOTE_ADDR'] ?? '-',
        'uid' => $_SESSION['uid'] ?? null,
    ];

    $banGhi += $chiTietAnToan;

    $json = json_encode(
        $banGhi,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    if ($json === false) {
        return;
    }

    $dong = date('Y-m-d H:i:s') . " $suKien $json";

    error_log(
        $dong . PHP_EOL,
        3,
        __DIR__ . '/../logs/bao-mat.log'
    );
}
