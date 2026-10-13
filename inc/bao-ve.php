<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

// Chỉ cho phép tiếp tục khi phiên đăng nhập hợp lệ.
$nguoiDung = $_SESSION['nguoi_dung'] ?? null;

if (
    !is_array($nguoiDung)
    || empty($nguoiDung['ten_dang_nhap'])
) {
    $_SESSION['flash_dang_nhap'] =
        'Vui lòng đăng nhập để tiếp tục.';

    // Xác định đường dẫn gốc của ứng dụng.
    $thuMucGoc = rtrim(
        dirname($_SERVER['SCRIPT_NAME'] ?? '/'),
        '/\\'
    );

    $urlDangNhap = $thuMucGoc . '/dang-nhap.php';

    header('Location: ' . $urlDangNhap, true, 303);
    exit;
}