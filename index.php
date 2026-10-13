<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

$tieuDeTrang = 'Trang chủ | MiniPos';
$moTaTrang = 'MiniPos - Hệ thống quản lý bán hàng, tồn kho và đơn hàng chuyên nghiệp.';
$scriptsTrang = ['js/trang-chu.js'];


// Đọc cookie da_xem và chỉ lấy tối đa bốn sản phẩm hợp lệ.
$sanPhamDaXem = [];
$cookieDaXem = $_COOKIE['da_xem'] ?? '';

if (is_string($cookieDaXem) && $cookieDaXem !== '') {
    try {
        $khoSanPhamDaXem = new \App\Data\KhoSanPham();
        $idsDaXem = [];

        foreach (explode(',', $cookieDaXem) as $idCookie) {
            $idCookie = trim($idCookie);

            if (
                preg_match('/^SP[0-9]{3}$/D', $idCookie) !== 1
                || in_array($idCookie, $idsDaXem, true)
            ) {
                continue;
            }

            $sanPhamDaXemHienTai =
                $khoSanPhamDaXem->timTheoId($idCookie);

            if ($sanPhamDaXemHienTai !== null) {
                $idsDaXem[] = $idCookie;
                $sanPhamDaXem[] = $sanPhamDaXemHienTai;
            }

            if (count($sanPhamDaXem) >= 4) {
                break;
            }
        }
    } catch (\Throwable $loi) {
        error_log(
            'Không thể đọc danh sách sản phẩm đã xem: '
            . $loi->getMessage()
        );

        $sanPhamDaXem = [];
    }
}
require __DIR__ . '/inc/header.php';
?>
<main class="bao">

        <h1>Trang chủ hệ thống MiniPos</h1>

        <!-- HERO -->
        <section class="hero">
            <div class="hero-content">

                <p class="hero-label">
                    GIẢI PHÁP QUẢN LÝ BÁN HÀNG
                </p>

                <h2>
                    MiniPos – Quản lý bán hàng thông minh
                </h2>

                <p>
                    Hỗ trợ quản lý sản phẩm, bán hàng và tồn kho hiệu quả.
                </p>

                <a href="danh-sach.php">
                    Xem sản phẩm
                </a>

            </div>
        </section>


                <!-- SAN PHAM DA XEM GAN DAY -->
        <section class="san-pham-da-xem" aria-labelledby="tieu-de-da-xem">
            <div class="tieu-de-da-xem">
                <div>
                    <h2 id="tieu-de-da-xem">Đã xem gần đây</h2>
                    <p>Những sản phẩm bạn đã xem trong thời gian gần nhất.</p>
                </div>
                <a href="danh-sach.php">Xem tất cả sản phẩm</a>
            </div>

            <?php if ($sanPhamDaXem === []): ?>
                <p class="thong-bao-chua-xem">
                    Bạn chưa xem sản phẩm nào gần đây.
                    Hãy khám phá danh sách sản phẩm để bắt đầu.
                </p>
            <?php else: ?>
                <div class="luoi-san-pham-da-xem">
                    <?php foreach ($sanPhamDaXem as $spDaXem): ?>
                        <article class="the-san-pham-da-xem">
                            <a
                                class="anh-san-pham-da-xem"
                                href="chi-tiet.php?id=<?= e(rawurlencode($spDaXem->getId())) ?>">
                                <img
                                    src="<?= e($spDaXem->getAnh()) ?>"
                                    alt="<?= e('Hình ảnh ' . $spDaXem->getTen()) ?>"
                                    width="220"
                                    height="160"
                                    loading="lazy">
                            </a>

                            <div class="noi-dung-da-xem">
                                <h3>
                                    <a href="chi-tiet.php?id=<?= e(rawurlencode($spDaXem->getId())) ?>">
                                        <?= e($spDaXem->getTen()) ?>
                                    </a>
                                </h3>

                                <p class="gia-da-xem">
                                    <?= e(vnd($spDaXem->getGia())) ?>
                                </p>

                                <p class="ton-kho-da-xem">
                                    Tồn kho: <?= e($spDaXem->getTonKho()) ?>
                                </p>

                                <a
                                    class="nut-xem-lai-san-pham"
                                    href="chi-tiet.php?id=<?= e(rawurlencode($spDaXem->getId())) ?>">
                                    Xem lại sản phẩm
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
<!-- TỔNG QUAN HỆ THỐNG -->
        <section>

            <h2>Tổng quan hệ thống</h2>

            <p>
                Hệ thống MiniPos được xây dựng nhằm hỗ trợ cửa hàng quản lý
                sản phẩm, nhập xuất hàng, theo dõi tồn kho và tối ưu hóa quá
                trình bán hàng. Giao diện được thiết kế tối giản, giúp nhân
                viên thực hiện thao tác tính tiền nhanh chóng.
            </p>

            <img
                src="images/MiniPOS.webp"
                alt="Giao diện hệ thống MiniPos"
                width="800"
                height="533"
                loading="lazy">

        </section>


        <!-- REST API -->
        <section>

            <h2>Thông tin thời tiết</h2>
            

            <noscript> 
                <p> JavaScript đang bị tắt. Vui lòng bật JavaScript để xem dữ liệu thời tiết hiện tại. 
                </p> 
            </noscript>
            
            <p
                id="trang-thai-thoi-tiet"
                aria-live="polite">
                Đang tải dữ liệu thời tiết...
            </p>

            <div id="du-lieu-thoi-tiet"></div>

            <button
                id="thu-lai-thoi-tiet"
                type="button"
                hidden>
                Thử lại
            </button>

        </section>


        <!-- THẺ GIỚI THIỆU -->
        <section class="the-gioi-thieu">

            <article class="the">

                <h2>Sản phẩm</h2>

                <p>
                    Quản lý thông tin và danh mục sản phẩm một cách rõ ràng,
                    thuận tiện.
                </p>

            </article>


            <article class="the">

                <h2>Bán hàng</h2>

                <p>
                    Hỗ trợ quá trình bán hàng, tính tiền và quản lý đơn hàng
                    nhanh chóng.
                </p>

            </article>


            <article class="the">

                <h2>Tồn kho</h2>

                <p>
                    Theo dõi số lượng hàng hóa và tình trạng tồn kho hiệu quả.
                </p>

            </article>

        </section>


        <!-- TÍNH NĂNG NỔI BẬT -->
        <section>

            <h2>Tính năng nổi bật</h2>

            <ul>

                <li>
                    Quản lý sản phẩm và danh mục hàng hóa.
                </li>

                <li>
                    Thực hiện bán hàng, tính tiền và in hóa đơn.
                </li>

                <li>
                    Theo dõi tồn kho theo thời gian thực.
                </li>

                <li>
                    Phân quyền Admin và Nhân viên bán hàng độc lập.
                </li>

            </ul>

        </section>


        <!-- LỢI ÍCH CỦA HỆ THỐNG -->
        <section>

            <h2>Lợi ích của hệ thống</h2>

            <ul>

                <li>
                    Tiết kiệm thời gian trong quá trình bán hàng.
                </li>

                <li>
                    Hỗ trợ quản lý hàng hóa và tồn kho hiệu quả.
                </li>

                <li>
                    Giảm sai sót khi nhập liệu và tính tiền.
                </li>

                <li>
                    Cung cấp giao diện dễ sử dụng cho nhân viên.
                </li>

            </ul>

        </section>

    </main>
<?php
require __DIR__ . '/inc/footer.php';