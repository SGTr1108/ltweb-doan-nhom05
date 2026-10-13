<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

$tieuDeTrang = 'Giỏ hàng | MiniPos';
$moTaTrang = 'Kiểm tra sản phẩm, số lượng và tổng tiền trong giỏ hàng MiniPos.';
$scriptsTrang = ['js/gio-hang.js'];

$thongBao = $_SESSION['flash_gio_hang'] ?? '';
unset($_SESSION['flash_gio_hang']);

if (empty($_SESSION['csrf_gio_hang'])) {
    $_SESSION['csrf_gio_hang'] = bin2hex(random_bytes(32));
}

$khoSanPham = new \App\Data\KhoSanPham();
$gioHang = new \App\Services\GioHang($khoSanPham);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $tokenGui = is_string($_POST['csrf_token'] ?? null)
        ? $_POST['csrf_token']
        : '';

    $tokenHienTai = is_string($_SESSION['csrf_gio_hang'] ?? null)
        ? $_SESSION['csrf_gio_hang']
        : '';

    if (
        $tokenGui === ''
        || $tokenHienTai === ''
        || !hash_equals($tokenHienTai, $tokenGui)
    ) {
        $_SESSION['flash_gio_hang'] =
            'Phiên thao tác không hợp lệ. Vui lòng thử lại.';
    } else {
        $hanhDong = is_string($_POST['hanh_dong'] ?? null)
            ? $_POST['hanh_dong']
            : '';

        $idSanPham = is_string($_POST['id'] ?? null)
            ? trim($_POST['id'])
            : '';

        try {
            switch ($hanhDong) {
                case 'them':
                    $soLuong = filter_var(
                        $_POST['so_luong'] ?? 1,
                        FILTER_VALIDATE_INT,
                        ['options' => ['min_range' => 1]]
                    );

                    if ($idSanPham === '' || $soLuong === false) {
                        throw new \InvalidArgumentException(
                            'Mã sản phẩm hoặc số lượng không hợp lệ.'
                        );
                    }

                    $gioHang->them($idSanPham, $soLuong);
                    $_SESSION['flash_gio_hang'] =
                        'Đã thêm sản phẩm vào giỏ hàng.';
                    break;

                case 'cap-nhat':
                    $soLuong = filter_var(
                        $_POST['so_luong'] ?? null,
                        FILTER_VALIDATE_INT,
                        ['options' => ['min_range' => 1]]
                    );

                    if ($idSanPham === '' || $soLuong === false) {
                        throw new \InvalidArgumentException(
                            'Vui lòng nhập số lượng hợp lệ.'
                        );
                    }

                    $gioHang->capNhatSoLuong($idSanPham, $soLuong);
                    $_SESSION['flash_gio_hang'] =
                        'Đã cập nhật số lượng sản phẩm.';
                    break;

                case 'xoa':
                    if ($idSanPham === '') {
                        throw new \InvalidArgumentException(
                            'Không xác định được sản phẩm cần xóa.'
                        );
                    }

                    $gioHang->xoa($idSanPham);
                    $_SESSION['flash_gio_hang'] =
                        'Đã xóa sản phẩm khỏi giỏ hàng.';
                    break;

                case 'xoa-tat-ca':
                    $gioHang->xoaTatCa();
                    $_SESSION['flash_gio_hang'] =
                        'Đã xóa toàn bộ giỏ hàng.';
                    break;

                default:
                    throw new \InvalidArgumentException(
                        'Thao tác không hợp lệ.'
                    );
            }
        } catch (\InvalidArgumentException | \RuntimeException $loi) {
            $_SESSION['flash_gio_hang'] = $loi->getMessage();
        } catch (\Throwable $loi) {
            error_log('Lỗi xử lý giỏ hàng: ' . $loi->getMessage());
            $_SESSION['flash_gio_hang'] =
                'Không thể xử lý giỏ hàng. Vui lòng thử lại.';
        }
    }

    header('Location: gio-hang.php', true, 303);
    exit;
}

$danhSachMatHang = [];
$tongTien = 0;
$tongSoLuong = 0;
$loiDuLieu = false;

try {
    $danhSachMatHang = $gioHang->layDanhSach();
    $tongTien = $gioHang->tinhTongTien();
    $tongSoLuong = $gioHang->demSoLuong();
} catch (\Throwable $loi) {
    error_log('Lỗi tải giỏ hàng: ' . $loi->getMessage());
    $loiDuLieu = true;
}

require __DIR__ . '/inc/header.php';
?>

<main class="bao trang-gio-hang gio-hang-page-v2">
    <div class="gio-hang-heading-v2">
        <div class="gio-hang-icon-v2" aria-hidden="true">
    <svg viewBox="0 0 24 24" width="42" height="42" fill="none"
         stroke="currentColor" stroke-width="1.7"
         stroke-linecap="round" stroke-linejoin="round">
        <circle cx="9" cy="20" r="1.4"/>
        <circle cx="18" cy="20" r="1.4"/>
        <path d="M2.5 3.5h2.2l2.5 12.1a2 2 0 0 0 2 1.6h8.2a2 2 0 0 0 1.9-1.5L21 8H6"/>
        <path d="M8 11h11M9 14h9M11 8l.5 7M16 8l-.4 7"/>
    </svg>
</div>
        <div>
            <h1>Giỏ hàng của bạn</h1>
            <p>Kiểm tra lại sản phẩm và số lượng trước khi thanh toán nhé!</p>
        </div>
    </div>

    <?php if (is_string($thongBao) && $thongBao !== ''): ?>
        <p class="gio-hang-notice-v2" role="status">
            <?= e($thongBao) ?>
        </p>
    <?php endif; ?>

    <?php if ($loiDuLieu): ?>
        <section class="gio-hang-empty-v2" role="alert">
            <h2>Chưa thể tải giỏ hàng</h2>
            <p>Đã xảy ra lỗi khi đọc dữ liệu. Vui lòng thử lại sau.</p>
            <a href="danh-sach.php" class="gio-hang-outline-button-v2">
                Quay lại danh sách sản phẩm
            </a>
        </section>

    <?php elseif ($danhSachMatHang === []): ?>
        <section class="gio-hang-empty-v2">
            <div class="gio-hang-empty-icon-v2" aria-hidden="true">🛍️</div>
            <h2>Giỏ hàng đang trống</h2>
            <p>Hãy chọn những sản phẩm bạn muốn mua nhé.</p>
            <a href="danh-sach.php" class="gio-hang-primary-button-v2">
                Khám phá sản phẩm
            </a>
        </section>

    <?php else: ?>
        <div class="gio-hang-layout-v2">
            <section class="gio-hang-products-v2">
                <div class="gio-hang-section-heading-v2">
                    <h2>Sản phẩm trong giỏ</h2>
                    <span><?= e(count($danhSachMatHang)) ?> mặt hàng</span>
                </div>

                <div class="gio-hang-products-list-v2">
                    <div class="gio-hang-columns-v2" aria-hidden="true">
                        <span>Sản phẩm</span>
                        <span>Đơn giá</span>
                        <span>Số lượng</span>
                        <span>Thành tiền</span>
                        <span>Thao tác</span>
                    </div>

                    <?php foreach ($danhSachMatHang as $matHang): ?>
                        <?php
                        $sanPham = $matHang['sanPham'];
                        $id = $sanPham->getId();
                        $soLuong = $matHang['soLuong'];
                        $thanhTien = $matHang['thanhTien'];
                        ?>

                        <article class="gio-hang-product-v2">
                            <div class="gio-hang-product-info-v2">
                                <a
                                    class="gio-hang-product-image-v2"
                                    href="chi-tiet.php?id=<?= e(rawurlencode($id)) ?>"
                                    aria-label="<?= e('Xem chi tiết ' . $sanPham->getTen()) ?>">

                                    <img
                                        src="<?= e($sanPham->getAnh()) ?>"
                                        alt="<?= e($sanPham->getTen()) ?>"
                                        width="80"
                                        height="80"
                                        loading="lazy">
                                </a>

                                <div class="gio-hang-product-name-v2">
                                    <h3>
                                        <a href="chi-tiet.php?id=<?= e(rawurlencode($id)) ?>">
                                            <?= e($sanPham->getTen()) ?>
                                        </a>
                                    </h3>

                                    <p class="gio-hang-product-code-v2">
                                        Mã SP: <?= e($id) ?>
                                    </p>

                                    <p class="gio-hang-category-v2">
                                        <?= e($sanPham->getDanhMuc()) ?>
                                    </p>

                                    <?php if ($sanPham->getTonKho() > 0): ?>
                                        <span class="gio-hang-stock-v2">
                                            Còn hàng
                                        </span>
                                    <?php else: ?>
                                        <span class="gio-hang-out-stock-v2">
                                            Hết hàng
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="gio-hang-unit-price-v2">
                                <span class="gio-hang-mobile-label-v2">Đơn giá</span>
                                <strong><?= e(vnd($sanPham->getGia())) ?></strong>
                            </div>

                            <form
                                class="gio-hang-quantity-form-v2"
                                method="post"
                                action="gio-hang.php">

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= e($_SESSION['csrf_gio_hang']) ?>">

                                <input
                                    type="hidden"
                                    name="hanh_dong"
                                    value="cap-nhat">

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= e($id) ?>">

                                <label for="so-luong-<?= e($id) ?>"
                                    class="gio-hang-mobile-label-v2">
                                    Số lượng
                                </label>

                                <div class="gio-hang-quantity-control-v2">
                                    <button
                                        type="button"
                                        class="gio-hang-step-v2"
                                        data-gio-hang-step="-1"
                                        aria-label="Giảm số lượng"
                                        <?= $soLuong <= 1 ? 'disabled' : '' ?>>−</button>

                                    <input
                                        type="number"
                                        id="so-luong-<?= e($id) ?>"
                                        name="so_luong"
                                        min="1"
                                        max="<?= e($sanPham->getTonKho()) ?>"
                                        value="<?= e($soLuong) ?>"
                                        aria-label="<?= e('Số lượng ' . $sanPham->getTen()) ?>"
                                        required>

                                    <button
                                        type="button"
                                        class="gio-hang-step-v2"
                                        data-gio-hang-step="1"
                                        aria-label="Tăng số lượng"
                                        <?= $soLuong >= $sanPham->getTonKho() ? 'disabled' : '' ?>>+</button>
                                </div>


                            </form>

                            <div class="gio-hang-subtotal-v2">
                                <span class="gio-hang-mobile-label-v2">Thành tiền</span>
                                <strong><?= e(vnd($thanhTien)) ?></strong>
                            </div>

                            <form
                                class="gio-hang-remove-form-v2"
                                method="post"
                                action="gio-hang.php">

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= e($_SESSION['csrf_gio_hang']) ?>">

                                <input
                                    type="hidden"
                                    name="hanh_dong"
                                    value="xoa">

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= e($id) ?>">

                                <button
                                    type="submit"
                                    aria-label="<?= e('Xóa ' . $sanPham->getTen() . ' khỏi giỏ hàng') ?>"
                                    title="Xóa sản phẩm">
                                    <svg viewBox="0 0 24 24" width="18" height="18"
     fill="none" stroke="currentColor" stroke-width="1.8"
     stroke-linecap="round" stroke-linejoin="round"
     aria-hidden="true">
    <path d="M3.5 6.5h17"/>
    <path d="M9 6.5V4h6v2.5"/>
    <path d="m6 6.5 1 13h10l1-13"/>
    <path d="M10 10v6M14 10v6"/>
</svg>
                                </button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="gio-hang-continue-v2">
                    <a href="danh-sach.php" class="gio-hang-outline-button-v2">
                        ← Tiếp tục mua sắm
                    </a>
                </div>
            </section>

            <aside class="gio-hang-summary-v2">
                <div class="gio-hang-summary-heading-v2">
                    <span aria-hidden="true">▤</span>
                    <h2>Tóm tắt đơn hàng</h2>
                </div>

                <div class="gio-hang-summary-body-v2">
                    <div class="gio-hang-summary-row-v2">
                        <span>Số mặt hàng</span>
                        <strong><?= e(count($danhSachMatHang)) ?></strong>
                    </div>

                    <div class="gio-hang-summary-row-v2">
                        <span>Tổng số lượng</span>
                        <strong><?= e($tongSoLuong) ?></strong>
                    </div>

                    <div class="gio-hang-summary-row-v2">
                        <span>Tạm tính</span>
                        <strong><?= e(vnd($tongTien)) ?></strong>
                    </div>

                    <div class="gio-hang-summary-row-v2">
                        <span>Phí vận chuyển</span>
                        <span class="gio-hang-summary-muted-v2">Chưa tính</span>
                    </div>

                    <div class="gio-hang-summary-total-v2">
                        <span>Tổng tiền sản phẩm</span>
                        <strong><?= e(vnd($tongTien)) ?></strong>
                    </div>

                    <button
                        type="button"
                        class="gio-hang-checkout-disabled-v2"
                        disabled
                        title="Chức năng thanh toán chưa được triển khai">
                        Thanh toán trực tuyến
                    </button>

                    <p class="gio-hang-checkout-note-v2">
                        Chức năng thanh toán trực tuyến chưa được triển khai.
                    </p>

                    <form
                        class="gio-hang-clear-form-v2"
                        method="post"
                        action="gio-hang.php">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= e($_SESSION['csrf_gio_hang']) ?>">

                        <input
                            type="hidden"
                            name="hanh_dong"
                            value="xoa-tat-ca">

                        <button type="submit">Xóa toàn bộ giỏ hàng</button>
                    </form>
                </div>

                <div class="gio-hang-support-v2">
                    <div aria-hidden="true">ℹ</div>
                    <div>
                        <strong>Cần hỗ trợ?</strong>
                        <p>Gửi thông tin qua trang liên hệ để được hỗ trợ.</p>
                        <a href="lien-he.php">Báo cáo và liên hệ</a>
                    </div>
                </div>
            </aside>
        </div>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>