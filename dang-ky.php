<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

if (nguoiDung() !== null) {
    header('Location: index.php', true, 303);
    exit;
}

$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));

$loi = [];
$old = ['email' => '', 'ho_ten' => ''];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    xacNhanCsrf();

    $old['email']  = trim((string) ($_POST['email'] ?? ''));
    $old['ho_ten'] = trim((string) ($_POST['ho_ten'] ?? ''));
    $matKhau       = (string) ($_POST['mat_khau'] ?? '');

    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $loi['email'] = 'Email không hợp lệ.';
    }

    if (mb_strlen($old['ho_ten']) < 2) {
        $loi['ho_ten'] = 'Họ tên phải có ít nhất 2 ký tự.';
    }

    if (strlen($matKhau) < 6) {
        $loi['mat_khau'] = 'Mật khẩu phải có ít nhất 6 ký tự.';
    }

    if ($loi === []) {
        $matKhauBam   = password_hash($matKhau, PASSWORD_DEFAULT);
        $khoNguoiDung = new \App\Data\KhoNguoiDung(db());

        try {
            // Không nhận vai_tro từ form; luôn là khach_hang
            $uid = $khoNguoiDung->tao($old['email'], $old['ho_ten'], $matKhauBam);
            $_SESSION['flash_thanh_cong'] = 'Đăng ký tài khoản thành công! Vui lòng đăng nhập.';
            header('Location: dang-nhap.php', true, 303);
            exit;
        } catch (\PDOException $e) {
            if (maLoiSql($e) === 1062) {
                $loi['email'] = 'Email này đã được đăng ký.';
            } else {
                throw $e;
            }
        }
    }
}

$tieuDeTrang  = 'Đăng ký | MiniPos';
$moTaTrang    = 'Tạo tài khoản mới trên hệ thống MiniPos.';
$scriptsTrang = [];

require __DIR__ . '/inc/header.php';
?>

<main class="bao">
    <h1>Đăng ký tài khoản</h1>

    <form action="dang-ky.php" method="post" class="form-xac-thuc" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

        <p>
            <label for="email">Email *:</label>
            <input type="email" id="email" name="email" value="<?= e($old['email']) ?>" required>
            <?php if (isset($loi['email'])): ?>
                <span class="thong-bao-loi" role="alert"><?= e($loi['email']) ?></span>
            <?php endif; ?>
        </p>

        <p>
            <label for="ho_ten">Họ và tên *:</label>
            <input type="text" id="ho_ten" name="ho_ten" value="<?= e($old['ho_ten']) ?>" required>
            <?php if (isset($loi['ho_ten'])): ?>
                <span class="thong-bao-loi" role="alert"><?= e($loi['ho_ten']) ?></span>
            <?php endif; ?>
        </p>

        <p>
            <label for="mat_khau">Mật khẩu (ít nhất 6 ký tự) *:</label>
            <input type="password" id="mat_khau" name="mat_khau" required>
            <?php if (isset($loi['mat_khau'])): ?>
                <span class="thong-bao-loi" role="alert"><?= e($loi['mat_khau']) ?></span>
            <?php endif; ?>
        </p>

        <button type="submit">Đăng ký</button>
    </form>

    <p>Đã có tài khoản? <a href="dang-nhap.php">Đăng nhập tại đây</a>.</p>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>