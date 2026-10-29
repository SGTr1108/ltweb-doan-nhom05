<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

$user = yeuCauDangNhap();

$khoSanPham  = new \App\Data\KhoSanPham(db());
$gioHang     = new \App\Services\GioHang($khoSanPham);
$mangGioHang = $gioHang->layMang();

if ($mangGioHang === []) {
    $_SESSION['flash_gio_hang'] = 'Giỏ hàng của bạn đang trống.';
    header('Location: gio-hang.php', true, 303);
    exit;
}

$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
$loiDatHang = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    xacNhanCsrf();
    $ghiChu = trim((string) ($_POST['ghi_chu'] ?? ''));

    try {
        $khoDonHang = new \App\Data\KhoDonHang(db());
        $donId      = $khoDonHang->tao((int) $user['id'], $mangGioHang, $ghiChu !== '' ? $ghiChu : null);

        $gioHang->xoaTatCa();
        $_SESSION['flash_don_hang'] = "Đặt hàng thành công! Mã đơn hàng của bạn là #{$donId}.";
        header('Location: don-hang.php', true, 303);
        exit;
    } catch (\App\Data\KhongDuHangException $e) {
        $loiDatHang = $e->getMessage();
    } catch (\Throwable $e) {
        error_log('Lỗi tạo đơn hàng: ' . $e->getMessage());
        $loiDatHang = 'Không thể xử lý đơn hàng lúc này. Vui lòng thử lại sau.';
    }
}

$danhSachMatHang = $gioHang->layDanhSach();
$tongTien        = $gioHang->tinhTongTien();

$tieuDeTrang  = 'Đặt hàng | MiniPos';
$moTaTrang    = 'Xác nhận và đặt hàng từ giỏ hàng.';
$scriptsTrang = [];

require __DIR__ . '/inc/header.php';
?>

<main class="bao">
    <h1>Xác nhận đặt hàng</h1>

    <?php if ($loiDatHang !== ''): ?>
        <p class="thong-bao-loi" role="alert"><?= e($loiDatHang) ?></p>
    <?php endif; ?>

    <section>
        <h2>Thông tin người nhận</h2>
        <p><strong>Họ tên:</strong> <?= e($user['ho_ten']) ?></p>
        <p><strong>Email:</strong> <?= e($user['email']) ?></p>
    </section>

    <section>
        <h2>Mặt hàng đã chọn</h2>
        <table class="bang-gio-hang">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Đơn giá</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($danhSachMatHang as $matHang): ?>
                    <tr>
                        <td><?= e($matHang['sanPham']->getTen()) ?></td>
                        <td><?= e(vnd($matHang['sanPham']->getGia())) ?></td>
                        <td><?= e($matHang['soLuong']) ?></td>
                        <td><?= e(vnd($matHang['thanhTien'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p><strong>Tổng tiền: <?= e(vnd($tongTien)) ?></strong></p>
    </section>

    <form action="dat-hang.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
        <p>
            <label for="ghi_chu">Ghi chú đơn hàng (không bắt buộc):</label><br>
            <textarea id="ghi_chu" name="ghi_chu" rows="3" cols="50"></textarea>
        </p>
        <button type="submit">Xác nhận đặt hàng</button>
        <a href="gio-hang.php">Quay lại giỏ hàng</a>
    </form>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>