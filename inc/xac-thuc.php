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

/** Ghi nhận đăng nhập thành công: đổi mã phiên (chống cố định phiên) rồi lưu uid. */
function dangNhap(int $id): void
{
    session_regenerate_id(true);
    $_SESSION['uid'] = $id;
}

/** Đăng xuất: xóa dữ liệu phiên, cookie phiên và hủy phiên. */
function dangXuat(): void
{
    $_SESSION = [];

    $p = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 3600,
        'path'     => $p['path'],
        'domain'   => $p['domain'],
        'secure'   => $p['secure'],
        'httponly' => $p['httponly'],
        'samesite' => $p['samesite'],
    ]);

    session_destroy();
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
        // Ghi nhận truy cập trái quyền, không ghi dữ liệu nhạy cảm.
        ghiLogBaoMat('cam_truy_cap', [
            'trang' => $_SERVER['REQUEST_URI'] ?? '',
            'vai_tro_yeu_cau' => $vaiTro,
        ]);

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
