<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

$_SESSION['csrf_gio_hang'] ??= bin2hex(random_bytes(32));

$khoDanhMuc = new \App\Data\KhoDanhMuc(db());
$khoSanPham = new \App\Data\KhoSanPham(db());

// Lấy tham số từ URL
$q        = trim((string) ($_GET['q'] ?? ''));
$dm       = filter_var($_GET['danh-muc'] ?? null, FILTER_VALIDATE_INT) ?: null;
$giaToiDa = filter_var($_GET['giaToiDa'] ?? null, FILTER_VALIDATE_INT) ?: null;
$sx       = (string) ($_GET['sap-xep'] ?? 'moi');
$trang    = max(1, filter_var($_GET['trang'] ?? 1, FILTER_VALIDATE_INT) ?: 1);

$soDong = 8;
$boQua  = ($trang - 1) * $soDong;

// Chuẩn bị mảng lọc cho KhoSanPham (truy vấn SQL trực tiếp)
$loc = [
    'q'        => $q,
    'dm'       => $dm,
    'giaToiDa' => $giaToiDa,
];

$tongDong  = $khoSanPham->dem($loc);
$tongTrang = max(1, (int) ceil($tongDong / $soDong));
$danhSach  = $khoSanPham->trang($loc, $sx, $soDong, $boQua);
$danhMucList = $khoDanhMuc->tatCa();

$tieuDeTrang  = 'Danh sách sản phẩm | MiniPos';
$moTaTrang    = 'Danh sách sản phẩm MiniPos, bao gồm tìm kiếm, lọc danh mục và sắp xếp.';
$scriptsTrang = ['js/tim-kiem.js', 'js/gio-hang.js'];

require __DIR__ . '/inc/header.php';
?>

<main class="bao">
    <h1>Danh sách sản phẩm</h1>

    <section>
        <h2>Danh sách các mặt hàng</h2>

        <form method="get" action="danh-sach.php" class="bo-loc-san-pham">
            <label for="tim-kiem">Tìm kiếm sản phẩm</label>
            <input
                type="search"
                id="tim-kiem"
                name="q"
                value="<?= e($q) ?>"
                placeholder="Nhập tên sản phẩm...">

            <label for="loc-danh-muc">Danh mục</label>
            <select id="loc-danh-muc" name="danh-muc">
                <option value="">Tất cả danh mục</option>
                <?php foreach ($danhMucList as $muc): ?>
                    <option
                        value="<?= e($muc['id']) ?>"
                        <?= $dm === (int) $muc['id'] ? 'selected' : '' ?>>
                        <?= e($muc['ten']) ?> (<?= e($muc['so_san_pham']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="gia-toi-da">Giá tối đa</label>
            <input
                type="number"
                id="gia-toi-da"
                name="giaToiDa"
                placeholder="VNĐ"
                value="<?= e($giaToiDa) ?>">

            <label for="sap-xep">Sắp xếp</label>
            <select id="sap-xep" name="sap-xep">
                <option value="moi" <?= $sx === 'moi' ? 'selected' : '' ?>>Mới nhất</option>
                <option value="gia-tang" <?= $sx === 'gia-tang' ? 'selected' : '' ?>>Giá tăng dần</option>
                <option value="gia-giam" <?= $sx === 'gia-giam' ? 'selected' : '' ?>>Giá giảm dần</option>
                <option value="ten" <?= $sx === 'ten' ? 'selected' : '' ?>>Tên A-Z</option>
            </select>

            <button type="submit">Áp dụng</button>
            <a href="danh-sach.php">Xóa bộ lọc</a>
        </form>

        <p id="trang-thai-san-pham" aria-live="polite">
            Có <?= e($tongDong) ?> sản phẩm phù hợp.
        </p>

        <div class="luoi-san-pham">
            <?php if ($danhSach === []): ?>
                <p>Không tìm thấy sản phẩm phù hợp.</p>
            <?php else: ?>
                <?php foreach ($danhSach as $sanPham): ?>
                    <article class="the-san-pham">
                        <img
                            src="<?= e($sanPham->getAnh() ?: 'images/MiniPOS.webp') ?>"
                            alt="Hình ảnh <?= e($sanPham->getTen()) ?>"
                            width="800"
                            height="533">

                        <div class="noi-dung-san-pham">
                            <h3><?= e($sanPham->getTen()) ?></h3>

                            <p class="ma-san-pham">
                                Mã sản phẩm: <?= e($sanPham->getId()) ?>
                            </p>

                            <p class="gia-san-pham">
                                <?= e(vnd($sanPham->getGia())) ?>
                            </p>

                            <p>
                                Tồn kho: <?= e($sanPham->getTonKho()) ?>
                            </p>

                            <div class="hang-nut-san-pham">
                                <a href="chi-tiet.php?id=<?= e(rawurlencode((string) $sanPham->getId())) ?>">
                                    Xem chi tiết
                                </a>

                                <form method="post" action="gio-hang.php" class="form-them-gio-hang">
                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_gio_hang']) ?>">
                                    <input type="hidden" name="hanh_dong" value="them">
                                    <input type="hidden" name="id" value="<?= e($sanPham->getId()) ?>">
                                    <input type="hidden" name="so_luong" value="1">
                                    <button type="submit" <?= $sanPham->getTonKho() < 1 ? 'disabled' : '' ?>>
                                        Thêm vào giỏ
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Phân trang -->
        <?php if ($tongTrang > 1): ?>
            <nav class="phan-trang" style="margin-top: 20px;">
                Trang:
                <?php for ($i = 1; $i <= $tongTrang; $i++): ?>
                    <a
                        href="danh-sach.php?<?= http_build_query(array_merge($_GET, ['trang' => $i])) ?>"
                        style="<?= $i === $trang ? 'font-weight:bold; text-decoration:underline;' : '' ?>">
                        [<?= $i ?>]
                    </a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>