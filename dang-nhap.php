<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

$tieuDeTrang = 'Đăng nhập | MiniPos';
$moTaTrang = 'Đăng nhập hệ thống MiniPos.';
$scriptsTrang = [];

$loiDangNhap = '';
$tenDangNhap = '';

$thongBao = $_SESSION['flash_dang_nhap'] ?? '';
unset($_SESSION['flash_dang_nhap']);

if (empty($_SESSION['csrf_dang_nhap'])) {
    $_SESSION['csrf_dang_nhap'] = bin2hex(random_bytes(32));
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $tenDangNhap = trim(
        is_string($_POST['ten_dang_nhap'] ?? null)
            ? $_POST['ten_dang_nhap']
            : ''
    );

    $matKhau = is_string($_POST['mat_khau'] ?? null)
        ? $_POST['mat_khau']
        : '';

    $tokenGui = is_string($_POST['csrf_token'] ?? null)
        ? $_POST['csrf_token']
        : '';

    $tokenHienTai = is_string($_SESSION['csrf_dang_nhap'] ?? null)
        ? $_SESSION['csrf_dang_nhap']
        : '';

    if (
        $tokenHienTai === ''
        || $tokenGui === ''
        || !hash_equals($tokenHienTai, $tokenGui)
    ) {
        $loiDangNhap = 'Phiên biểu mẫu không hợp lệ. Vui lòng tải lại trang.';
    } elseif ($tenDangNhap === '' || $matKhau === '') {
        $loiDangNhap = 'Vui lòng nhập tên đăng nhập và mật khẩu.';
    } else {
        $cacTaiKhoan = require __DIR__ . '/inc/tai-khoan.php';
        $taiKhoan = is_array($cacTaiKhoan)
            ? ($cacTaiKhoan[$tenDangNhap] ?? null)
            : null;

        if (
            is_array($taiKhoan)
            && isset($taiKhoan['mat_khau_hash'])
            && is_string($taiKhoan['mat_khau_hash'])
            && password_verify($matKhau, $taiKhoan['mat_khau_hash'])
        ) {
            session_regenerate_id(true);

            $_SESSION['nguoi_dung'] = [
                'ten_dang_nhap' => $tenDangNhap,
                'ten_hien_thi' => (string) (
                    $taiKhoan['ten_hien_thi'] ?? $tenDangNhap
                ),
                'vai_tro' => (string) (
                    $taiKhoan['vai_tro'] ?? 'user'
                ),
            ];

            unset($_SESSION['csrf_dang_nhap']);

            header('Location: quan-tri.php', true, 303);
            exit;
        }

                // Ghi log đăng nhập thất bại, không ghi mật khẩu vào log.
        $tenDangNhapLog = preg_replace(
            '/[\r\n\t]+/',
            '_',
            $tenDangNhap
        ) ?? '';

        $tenDangNhapLog = substr($tenDangNhapLog, 0, 80);

        $ipDangNhap = filter_var(
            $_SERVER['REMOTE_ADDR'] ?? '',
            FILTER_VALIDATE_IP
        );

        error_log(sprintf(
            '[%s] Dang nhap that bai; tai_khoan="%s"; ip=%s',
            date('c'),
            $tenDangNhapLog,
            $ipDangNhap !== false ? $ipDangNhap : 'khong-ro'
        ));

        $loiDangNhap = 'Tên đăng nhập hoặc mật khẩu không chính xác.';
    }
}

require __DIR__ . '/inc/header.php';
?>

<main class="bao">
    <h1>Đăng nhập MiniPos</h1>

    <p>Đăng nhập để truy cập khu vực quản trị hệ thống.</p>

    <?php if (is_string($thongBao) && $thongBao !== ''): ?>
        <p role="status"><?= e($thongBao) ?></p>
    <?php endif; ?>

    <?php if ($loiDangNhap !== ''): ?>
        <p role="alert"><?= e($loiDangNhap) ?></p>
    <?php endif; ?>

    <form method="post" action="dang-nhap.php">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= e($_SESSION['csrf_dang_nhap']) ?>">

        <p>
            <label for="ten-dang-nhap">Tên đăng nhập:</label>
            <input
                type="text"
                id="ten-dang-nhap"
                name="ten_dang_nhap"
                value="<?= e($tenDangNhap) ?>"
                autocomplete="username"
                required>
        </p>

        <p>
            <label for="mat-khau">Mật khẩu:</label>
            <input
                type="password"
                id="mat-khau"
                name="mat_khau"
                autocomplete="current-password"
                required>
        </p>

        <button type="submit">Đăng nhập</button>
    </form>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>