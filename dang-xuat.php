<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Phuong thuc khong duoc ho tro.');
}

$tokenGui = is_string($_POST['csrf_token'] ?? null)
    ? $_POST['csrf_token']
    : '';

$tokenHienTai = is_string($_SESSION['csrf_dang_xuat'] ?? null)
    ? $_SESSION['csrf_dang_xuat']
    : '';

if (
    $tokenGui === ''
    || $tokenHienTai === ''
    || !hash_equals($tokenHienTai, $tokenGui)
) {
    http_response_code(403);
    exit('Yeu cau dang xuat khong hop le. Vui long tai lai trang.');
}

// Xoa thong tin phien dang nhap.
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $thamSoCookie = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        [
            'expires' => time() - 42000,
            'path' => $thamSoCookie['path'],
            'domain' => $thamSoCookie['domain'],
            'secure' => $thamSoCookie['secure'],
            'httponly' => $thamSoCookie['httponly'],
            'samesite' => $thamSoCookie['samesite'] ?? 'Lax',
        ]
    );
}

session_destroy();

// Chuyen ve trang dang nhap.
header('Location: dang-nhap.php', true, 303);
exit;