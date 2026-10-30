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
    $tokenGui = is_string($_POST['csrf_token'] ?? null)
        ? $_POST['csrf_token'] : '';

    $tokenHienTai = is_string($_SESSION['csrf_gio_hang'] ?? null)
        ? $_SESSION['csrf_gio_hang'] : '';

    if (
        $tokenGui === '' ||
        $tokenHienTai === '' ||
        !hash_equals($tokenHienTai, $tokenGui)
    ) {
        $_SESSION['flash_gio_hang'] =
            'Phiên thao tác không hợp lệ. Vui lòng thử lại.';
    } else {
        $hanhDong = is_string($_POST['hanh_dong'] ?? null)
            ? $_POST['hanh_dong'] : '';

        $idSanPham = is_string($_POST['id'] ?? null)
            ? trim($_POST['id']) : '';

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

<main class="gio-hang-page-v2">

    <div class="gio-hang-heading-v2">
        <span class="gio-hang-icon-v2" aria-hidden="true">🛒</span>
        <div>
            <h1>Giỏ hàng của bạn</h1>
            <p>Kiểm tra sản phẩm và hoàn tất đơn hàng của bạn.</p>
        </div>
    </div>

    <?php if (is_string($thongBao) && $thongBao !== ''): ?>
        <p class="gio-hang-notice-v2" role="status">
            <?= e($thongBao) ?>
        </p>
    <?php endif; ?>

    <?php if ($loiDuLieu): ?>

        <p role="alert">
            Chưa thể tải giỏ hàng. Vui lòng thử lại sau.
        </p>

    <?php elseif ($danhSachMatHang === []): ?>

        <section class="gio-hang-empty-v2">
            <div class="gio-hang-empty-icon-v2" aria-hidden="true">
                🛍️
            </div>

            <h2>Giỏ hàng đang trống</h2>
            <p>Hãy chọn những sản phẩm bạn muốn mua nhé.</p>

            <a class="gio-hang-primary-button-v2" href="danh-sach.php">
                Khám phá sản phẩm
            </a>
        </section>

    <?php else: ?>

        <div class="gio-hang-layout-v2">

            <section
                class="gio-hang-products-v2"
                aria-labelledby="gio-hang-products-title"
            >

                <div class="gio-hang-section-heading-v2">
                    <h2 id="gio-hang-products-title">
                        Sản phẩm đã chọn
                    </h2>

                    <span>
                        <?= e(count($danhSachMatHang)) ?> sản phẩm
                    </span>
                </div>

                <div class="gio-hang-columns-v2" aria-hidden="true">
                    <span>SẢN PHẨM</span>
                    <span>ĐƠN GIÁ</span>
                    <span>SỐ LƯỢNG</span>
                    <span>THÀNH TIỀN</span>
                    <span></span>
                </div>

                <?php foreach ($danhSachMatHang as $matHang): ?>

                    <?php
                    $sanPham   = $matHang['sanPham'];
                    $id        = $sanPham->getId();
                    $soLuong   = $matHang['soLuong'];
                    $thanhTien = $matHang['thanhTien'];
                    $tonKho    = $sanPham->getTonKho();
                    ?>

                    <article class="gio-hang-product-v2">

                        <div class="gio-hang-product-info-v2">

                            <a
                                class="gio-hang-product-image-v2"
                                href="chi-tiet.php?id=<?= e(rawurlencode((string) $id)) ?>"
                                aria-label="Xem <?= e($sanPham->getTen()) ?>"
                            >
                                <img
                                    src="<?= e($sanPham->getAnh() ?: 'images/MiniPOS.webp') ?>"
                                    alt="<?= e($sanPham->getTen()) ?>"
                                    loading="lazy"
                                    onerror="this.onerror=null;this.src='images/MiniPOS.webp';"
                                >
                            </a>

                            <div class="gio-hang-product-name-v2">

                                <h3>
                                    <a href="chi-tiet.php?id=<?= e(rawurlencode((string) $id)) ?>">
                                        <?= e($sanPham->getTen()) ?>
                                    </a>
                                </h3>

                                <p class="gio-hang-product-code-v2">
                                    Mã sản phẩm: #<?= e($id) ?>
                                </p>

                                <p class="gio-hang-category-v2">
                                    <?= e($sanPham->getDanhMuc()) ?>
                                </p>

                                <?php if ($tonKho > 0): ?>
                                    <span class="gio-hang-stock-v2">
                                        Còn <?= e($tonKho) ?> sản phẩm
                                    </span>
                                <?php else: ?>
                                    <span class="gio-hang-out-stock-v2">
                                        Tạm hết hàng
                                    </span>
                                <?php endif; ?>

                            </div>
                        </div>

                        <div class="gio-hang-unit-price-v2">
                            <span class="gio-hang-mobile-label-v2">
                                Đơn giá
                            </span>

                            <strong>
                                <?= e(vnd($sanPham->getGia())) ?>
                            </strong>
                        </div>

                        <!-- TỰ ĐỘNG CẬP NHẬT SỐ LƯỢNG -->
                        <form
                            class="gio-hang-quantity-form-v2"
                            method="post"
                            action="gio-hang.php"
                        >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= e($_SESSION['csrf_gio_hang']) ?>"
                            >

                            <input
                                type="hidden"
                                name="hanh_dong"
                                value="cap-nhat"
                            >

                            <input
                                type="hidden"
                                name="id"
                                value="<?= e($id) ?>"
                            >

                            <label
                                class="gio-hang-mobile-label-v2"
                                for="so-luong-<?= e($id) ?>"
                            >
                                Số lượng
                            </label>

                            <div class="gio-hang-quantity-control-v2">

                                <button
                                    type="button"
                                    class="giam-so-luong"
                                    aria-label="Giảm số lượng"
                                    <?= $soLuong <= 1 ? 'disabled' : '' ?>
                                >
                                    −
                                </button>

                                <input
                                    id="so-luong-<?= e($id) ?>"
                                    type="number"
                                    name="so_luong"
                                    value="<?= e($soLuong) ?>"
                                    min="1"
                                    max="<?= e(max(1, $tonKho)) ?>"
                                    required
                                >

                                <button
                                    type="button"
                                    class="tang-so-luong"
                                    aria-label="Tăng số lượng"
                                    <?= $soLuong >= $tonKho ? 'disabled' : '' ?>
                                >
                                    +
                                </button>

                            </div>
                        </form>

                        <div class="gio-hang-subtotal-v2">
                            <span class="gio-hang-mobile-label-v2">
                                Thành tiền
                            </span>

                            <strong>
                                <?= e(vnd($thanhTien)) ?>
                            </strong>
                        </div>

                        <form
                            class="gio-hang-remove-form-v2"
                            method="post"
                            action="gio-hang.php"
                        >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= e($_SESSION['csrf_gio_hang']) ?>"
                            >

                            <input
                                type="hidden"
                                name="hanh_dong"
                                value="xoa"
                            >

                            <input
                                type="hidden"
                                name="id"
                                value="<?= e($id) ?>"
                            >

                            <button
                                type="submit"
                                aria-label="Xóa <?= e($sanPham->getTen()) ?>"
                                title="Xóa sản phẩm"
                            >
                                ×
                            </button>

                        </form>

                    </article>

                <?php endforeach; ?>

                <div class="gio-hang-continue-v2">
                    <a
                        class="gio-hang-outline-button-v2"
                        href="danh-sach.php"
                    >
                        ← Tiếp tục mua sắm
                    </a>
                </div>

            </section>

            <!-- TÓM TẮT ĐƠN HÀNG -->
            <aside
                class="gio-hang-summary-v2"
                aria-labelledby="gio-hang-summary-title"
            >

                <div class="gio-hang-summary-heading-v2">
                    <span aria-hidden="true">▤</span>

                    <h2 id="gio-hang-summary-title">
                        Tóm tắt đơn hàng
                    </h2>
                </div>

                <div class="gio-hang-summary-body-v2">

                    <div class="gio-hang-summary-row-v2">
                        <span>Số mặt hàng</span>
                        <strong>
                            <?= e(count($danhSachMatHang)) ?>
                        </strong>
                    </div>

                    <div class="gio-hang-summary-row-v2">
                        <span>Tổng số lượng</span>
                        <strong>
                            <?= e($tongSoLuong) ?>
                        </strong>
                    </div>

                    <div class="gio-hang-summary-total-v2">
                        <span>Tổng thanh toán</span>
                        <strong>
                            <?= e(vnd($tongTien)) ?>
                        </strong>
                    </div>

                    <a
                        class="gio-hang-primary-button-v2"
                        style="width:100%"
                        href="dat-hang.php"
                    >
                        Tiến hành đặt hàng →
                    </a>

                    <p class="gio-hang-checkout-note-v2">
                        Vui lòng kiểm tra số lượng và sản phẩm
                        trước khi đặt hàng.
                    </p>

                    <form
                        class="gio-hang-clear-form-v2"
                        method="post"
                        action="gio-hang.php"
                        onsubmit="return confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng?');"
                    >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= e($_SESSION['csrf_gio_hang']) ?>"
                        >

                        <input
                            type="hidden"
                            name="hanh_dong"
                            value="xoa-tat-ca"
                        >

                        <button type="submit">
                            Xóa toàn bộ giỏ hàng
                        </button>

                    </form>

                </div>

                <div class="gio-hang-support-v2">
                    <div aria-hidden="true">♡</div>

                    <div>
                        <strong>Cần hỗ trợ?</strong>

                        <p>
                            Liên hệ với MiniPos nếu bạn cần
                            trợ giúp về đơn hàng.
                        </p>

                        <a href="lien-he.php">
                            Liên hệ ngay
                        </a>
                    </div>
                </div>

            </aside>

        </div>

    <?php endif; ?>

</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
