<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

yeuCauPost();

// Kiểm tra linh hoạt cả csrf_token lẫn csrf_dang_xuat từ Session
$tokenGui      = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '';
$tokenSession  = is_string($_SESSION['csrf_token'] ?? null) ? $_SESSION['csrf_token'] : '';
$tokenDangXuat = is_string($_SESSION['csrf_dang_xuat'] ?? null) ? $_SESSION['csrf_dang_xuat'] : '';

$hopLe = ($tokenGui !== '') && (
    ($tokenSession !== '' && hash_equals($tokenSession, $tokenGui)) ||
    ($tokenDangXuat !== '' && hash_equals($tokenDangXuat, $tokenGui))
);

if (!$hopLe) {
    http_response_code(400);
    die('Lỗi CSRF: Phiên làm việc không hợp lệ hoặc đã hết hạn.');
}

// Xóa thông tin đăng nhập và hủy phiên làm việc
unset($_SESSION['uid'], $_SESSION['nguoi_dung'], $_SESSION['csrf_token'], $_SESSION['csrf_dang_xuat']);
session_destroy();

header('Location: index.php', true, 303);
exit;
