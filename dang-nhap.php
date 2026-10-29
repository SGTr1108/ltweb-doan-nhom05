<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

if (nguoiDung() !== null) {
    header('Location: index.php', true, 303);
    exit;
}

$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));

$loiChung = '';
$emailCu  = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    xacNhanCsrf();

    $emailCu = trim((string) ($_POST['email'] ?? ''));
    $matKhau = (string) ($_POST['mat_khau'] ?? '');

    $khoNguoiDung = new \App\Data\KhoNguoiDung(db());
    $user         = $khoNguoiDung->timTheoEmail($emailCu);

    // Thông báo lỗi chung bảo mật
    if ($user === null || !password_verify($matKhau, $user['mat_khau'])) {
        $loiChung = 'Email hoặc mật khẩu không chính xác.';
    } else {
        session_regenerate_id(true);
        $_SESSION['uid'] = (int) $user['id'];

        if (password_needs_rehash($user['mat_khau'], PASSWORD_DEFAULT)) {
            $khoNguoiDung->capNhatMatKhau((int) $user['id'], password_hash($matKhau, PASSWORD_DEFAULT));
        }

        header('Location: index.php', true, 303);
        exit;
    }
}

$tieuDeTrang  = 'Đăng nhập | MiniPos';
$moTaTrang    = 'Đăng nhập tài khoản MiniPos.';
$scriptsTrang = [];

require __DIR__ . '/inc/header.php';
?>

<main class="bao">
    <h1>Đăng nhập</h1>

    <?php if (isset($_SESSION['flash_thanh_cong'])): ?>
        <p class="thong-bao-thanh-cong" role="status"><?= e($_SESSION['flash_thanh_cong']) ?></p>
        <?php unset($_SESSION['flash_thanh_cong']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_loi'])): ?>
        <p class="thong-bao-loi" role="alert"><?= e($_SESSION['flash_loi']) ?></p>
        <?php unset($_SESSION['flash_loi']); ?>
    <?php endif; ?>

    <?php if ($loiChung !== ''): ?>
        <p class="thong-bao-loi" role="alert"><?= e($loiChung) ?></p>
    <?php endif; ?>

    <form action="dang-nhap.php" method="post" class="form-xac-thuc">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

        <p>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= e($emailCu) ?>" required>
        </p>

        <p>
            <label for="mat_khau">Mật khẩu:</label>
            <input type="password" id="mat_khau" name="mat_khau" required>
        </p>

        <button type="submit">Đăng nhập</button>
    </form>

    <p>Chưa có tài khoản? <a href="dang-ky.php">Đăng ký ngay</a>.</p>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>