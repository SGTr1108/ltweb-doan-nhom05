<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

if (nguoiDung() !== null) {
    header('Location: index.php', true, 303);
    exit;
}

$loiChung = '';
$emailCu  = '';


if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $emailCu = trim((string) ($_POST['email'] ?? ''));
    $matKhau = (string) ($_POST['mat_khau'] ?? '');

    // Chuẩn hóa thông tin dùng để giới hạn đăng nhập.
    $ip = substr((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 0, 45);
    $emailKhoa = substr(strtolower($emailCu), 0, 100);

    $pdo = db();

    // Xóa các lần đăng nhập sai đã quá 15 phút.
    $pdo->exec(
        'DELETE FROM dang_nhap_sai
         WHERE thoi_gian < DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
    );

    // Đếm số lần đăng nhập sai của IP + email trong 15 phút.
    $st = $pdo->prepare(
        'SELECT COUNT(*)
         FROM dang_nhap_sai
         WHERE ip = ? AND email = ?
           AND thoi_gian >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
    );
    $st->execute([$ip, $emailKhoa]);
    $soLanSai = (int) $st->fetchColumn();

    if ($soLanSai >= 5) {
        // Đã sai đủ 5 lần: tạm khóa, không kiểm tra mật khẩu nữa.
        http_response_code(429);
        $loiChung = 'Email hoặc mật khẩu không chính xác. Vui lòng thử lại sau 15 phút.';
    } else {
        $khoNguoiDung = new \App\Data\KhoNguoiDung($pdo);
        $user = $khoNguoiDung->timTheoEmail($emailCu);

        // Email không tồn tại và mật khẩu sai đều có cùng thông báo.
        if ($user === null || !password_verify($matKhau, $user['mat_khau'])) {
            $soLanSai++;

            // Lưu lần đăng nhập sai vào CSDL.
            $st = $pdo->prepare(
                'INSERT INTO dang_nhap_sai (ip, email) VALUES (?, ?)'
            );
            $st->execute([$ip, $emailKhoa]);

            // Ghi log bảo mật, chỉ lưu mã băm email, không lưu email/mật khẩu.
            ghiLogBaoMat('dang_nhap_sai', [
                'email_hash' => hash('sha256', $emailKhoa),
                'so_lan' => $soLanSai,
            ]);

            $loiChung = 'Email hoặc mật khẩu không chính xác.';
        } else {
            // Đăng nhập thành công: xóa lịch sử sai của IP + email.
            $st = $pdo->prepare(
                'DELETE FROM dang_nhap_sai WHERE ip = ? AND email = ?'
            );
            $st->execute([$ip, $emailKhoa]);

            dangNhap((int) $user['id']);

            if (password_needs_rehash($user['mat_khau'], PASSWORD_DEFAULT)) {
                $khoNguoiDung->capNhatMatKhau(
                    (int) $user['id'],
                    password_hash($matKhau, PASSWORD_DEFAULT)
                );
            }

            header('Location: index.php', true, 303);
            exit;
        }
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
        <?= csrfField() ?>

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