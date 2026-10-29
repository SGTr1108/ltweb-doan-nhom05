<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

/** Lấy người dùng đăng nhập hiện tại từ Session ID (chỉ lưu uid trong session). */
function nguoiDung(): ?array
{
    $uid = $_SESSION['uid'] ?? null;
    if ($uid === null || !is_int($uid)) {
        return null;
    }

    static $user = null;
    if ($user === null) {
        $khoNguoiDung = new \App\Data\KhoNguoiDung(db());
        $user = $khoNguoiDung->timTheoId($uid);
    }

    return $user;
}

/** Bắt buộc phải đăng nhập. */
function yeuCauDangNhap(): array
{
    $user = nguoiDung();
    if ($user === null) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['loi' => 'Vui lòng đăng nhập để thực hiện thao tác này.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $_SESSION['flash_loi'] = 'Vui lòng đăng nhập để tiếp tục.';
        header('Location: dang-nhap.php', true, 303);
        exit;
    }

    return $user;
}

/** Bắt buộc quyền vai_tro ('quan_tri'). */
function yeuCauQuyen(string $vaiTro): array
{
    $user = yeuCauDangNhap();
    if (($user['vai_tro'] ?? '') !== $vaiTro) {
        http_response_code(403);
        require __DIR__ . '/../403.php';
        exit;
    }

    return $user;
}

/** Chặn phương thức không phải POST. */
function yeuCauPost(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['loi' => 'Phương thức không được hỗ trợ. Bắt buộc dùng POST.'], JSON_UNESCAPED_UNICODE);
        } else {
            echo '<h1>405 Method Not Allowed - Phương thức phải là POST</h1>';
        }
        exit;
    }
}

/** Kiểm tra CSRF token. */
function xacNhanCsrf(): void
{
    $tokenGui = $_POST['csrf_token'] ?? '';
    $tokenSession = $_SESSION['csrf_token'] ?? '';

    if (!is_string($tokenGui) || $tokenGui === '' || !hash_equals($tokenSession, $tokenGui)) {
        http_response_code(400);
        die('Lỗi CSRF: Phiên làm việc không hợp lệ hoặc đã hết hạn.');
    }
}
