<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

$tieuDeTrang  = 'Giỏ hàng | MiniPos';
$moTaTrang    = 'Kiểm tra sản phẩm, số lượng và tổng tiền trong giỏ hàng MiniPos.';
$scriptsTrang = ['js/gio-hang.js'];

$thongBao = $_SESSION['flash_gio_hang'] ?? '';
unset($_SESSION['flash_gio_hang']);

$_SESSION['csrf_gio_hang'] ??= bin2hex(random_bytes(32));

$khoSanPham = new \App\Data\KhoSanPham(db());
$gioHang    = new \App\Services\GioHang($khoSanPham);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $tokenGui     = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '';
    $tokenHienTai = is_string($_SESSION['csrf_gio_hang'] ?? null) ? $_SESSION['csrf_gio_hang'] : '';

    if ($tokenGui === '' || $tokenHienTai === '' || !hash_equals($tokenHienTai, $tokenGui)) {
        $_SESSION['flash_gio_hang'] = 'Phiên thao tác không hợp lệ. Vui lòng thử lại.';
    } else {
        $hanhDong  = is_string($_POST['hanh_dong'] ?? null) ? $_POST['hanh_dong'] : '';
        $idSanPham = is_string($_POST['id'] ?? null) ? trim($_POST['id']) : '';

        try {
            switch ($hanhDong) {
                case 'them':
                    $soLuong = filter_var($_POST['so_luong'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    if ($idSanPham === '' || $soLuong === false) {
                        throw new \InvalidArgumentException('Mã sản phẩm hoặc số lượng không hợp lệ.');
                    }
                    $gioHang->them($idSanPham, $soLuong);
                    $_SESSION['flash_gio_hang'] = 'Đã thêm sản phẩm vào giỏ hàng.';
                    break;

                case 'cap-nhat':
                    $soLuong = filter_var($_POST['so_luong'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    if ($idSanPham === '' || $soLuong === false) {
                        throw new \InvalidArgumentException('Vui lòng nhập số lượng hợp lệ.');
                    }
                    $gioHang->capNhatSoLuong($idSanPham, $soLuong);
                    $_SESSION['flash_gio_hang'] = 'Đã cập nhật số lượng sản phẩm.';
                    break;

                case 'xoa':
                    if ($idSanPham === '') {
                        throw new \InvalidArgumentException('Không xác định được sản phẩm cần xóa.');
                    }
                    $gioHang->xoa($idSanPham);
                    $_SESSION['flash_gio_hang'] = 'Đã xóa sản phẩm khỏi giỏ hàng.';
                    break;

                case 'xoa-tat-ca':
                    $gioHang->xoaTatCa();
                    $_SESSION['flash_gio_hang'] = 'Đã xóa toàn bộ giỏ hàng.';
                    break;

                default:
                    throw new \InvalidArgumentException('Thao tác không hợp lệ.');
            }
        } catch (\InvalidArgumentException | \RuntimeException $loi) {
            $_SESSION['flash_gio_hang'] = $loi->getMessage();
        } catch (\Throwable $loi) {
            error_log('Lỗi xử lý giỏ hàng: ' . $loi->getMessage());
            $_SESSION['flash_gio_hang'] = 'Không thể xử lý giỏ hàng. Vui lòng thử lại.';
        }
    }

    header('Location: gio-hang.php', true, 303);
    exit;
}

$danhSachMatHang = [];
$tongTien        = 0;
$tongSoLuong     = 0;
$loiDuLieu       = false;

try {
    $danhSachMatHang = $gioHang->layDanhSach();
    $tongTien        = $gioHang->tinhTongTien();
    $tongSoLuong     = $gioHang->demSoLuong();
} catch (\Throwable $loi) {
    error_log('Lỗi tải giỏ hàng: ' . $loi->getMessage());
    $loiDuLieu = true;
}

require __DIR__ . '/inc/header.php';
?>

<main class="bao trang-gio-hang">
    <h1>Giỏ hàng của bạn</h1>

    <?php if (is_string($thongBao) && $thongBao !== ''): ?>
        <p class="gio-hang-notice-v2" role="status"><?= e($thongBao) ?></p>
    <?php endif; ?>

    <?php if ($loiDuLieu): ?>
        <p role="alert">Chưa thể tải giỏ hàng. Vui lòng thử lại sau.</p>
    <?php elseif ($danhSachMatHang === []): ?>
        <section class="gio-hang-empty-v2">
            <h2>Giỏ hàng đang trống</h2>
            <p>Hãy chọn những sản phẩm bạn muốn mua nhé.</p>
            <a href="danh-sach.php">Khám phá sản phẩm</a>
        </section>
    <?php else: ?>
        <div class="gio-hang-layout-v2">
            <section class="gio-hang-products-v2">
                <table class="bang-gio-hang">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($danhSachMatHang as $matHang): ?>
                            <?php
                            $sanPham   = $matHang['sanPham'];
                            $id        = $sanPham->getId();
                            $soLuong   = $matHang['soLuong'];
                            $thanhTien = $matHang['thanhTien'];
                            ?>
                            <tr>
                                <td>
                                    <a href="chi-tiet.php?id=<?= e(rawurlencode((string)$id)) ?>">
                                        <?= e($sanPham->getTen()) ?>
                                    </a>
                                </td>
                                <td><?= e(vnd($sanPham->getGia())) ?></td>
                                <td>
                                    <form method="post" action="gio-hang.php" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_gio_hang']) ?>">
                                        <input type="hidden" name="hanh_dong" value="cap-nhat">
                                        <input type="hidden" name="id" value="<?= e($id) ?>">
                                        <input type="number" name="so_luong" value="<?= e($soLuong) ?>" min="1" max="<?= e($sanPham->getTonKho()) ?>">
                                        <button type="submit">Cập nhật</button>
                                    </form>
                                </td>
                                <td><?= e(vnd($thanhTien)) ?></td>
                                <td>
                                    <form method="post" action="gio-hang.php" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_gio_hang']) ?>">
                                        <input type="hidden" name="hanh_dong" value="xoa">
                                        <input type="hidden" name="id" value="<?= e($id) ?>">
                                        <button type="submit">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <aside class="gio-hang-summary-v2">
                <h2>Tóm tắt đơn hàng</h2>
                <p>Số mặt hàng: <strong><?= e(count($danhSachMatHang)) ?></strong></p>
                <p>Tổng số lượng: <strong><?= e($tongSoLuong) ?></strong></p>
                <p><strong>Tổng tiền: <?= e(vnd($tongTien)) ?></strong></p>

                <!-- Đường dẫn tiến hành đặt hàng -->
                <p style="margin-top: 15px;">
                    <a href="dat-hang.php" class="button" style="display:inline-block; padding: 10px 20px; background: #007bff; color: #fff; text-decoration: none; border-radius: 4px;">
                        Tiến hành đặt hàng →
                    </a>
                </p>

                <form method="post" action="gio-hang.php" style="margin-top: 10px;">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_gio_hang']) ?>">
                    <input type="hidden" name="hanh_dong" value="xoa-tat-ca">
                    <button type="submit">Xóa toàn bộ giỏ hàng</button>
                </form>
            </aside>
        </div>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>