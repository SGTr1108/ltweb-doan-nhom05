<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

$_SESSION['csrf_gio_hang'] ??= bin2hex(random_bytes(32));

$khoDanhMuc = new \App\Data\KhoDanhMuc(db());
$khoSanPham = new \App\Data\KhoSanPham(db());

// Lấy giá trị bộ lọc từ URL để giữ trạng thái khi tải lại trang.
$q = trim((string) ($_GET['q'] ?? ''));
$dm = filter_var($_GET['danh-muc'] ?? null, FILTER_VALIDATE_INT) ?: null;
$giaToiDa = filter_var($_GET['giaToiDa'] ?? null, FILTER_VALIDATE_INT) ?: null;
$sx = (string) ($_GET['sap-xep'] ?? 'moi');
if (!in_array($sx, ['moi', 'gia-tang', 'gia-giam', 'ten'], true)) {
    $sx = 'moi';
}

// Tải toàn bộ sản phẩm trong một lần: 35 sản phẩm hiển thị trên cùng trang.
$tongDong = $khoSanPham->dem([]);
$danhSach = $khoSanPham->trang([], $sx, max(1, $tongDong), 0);
$danhMucList = $khoDanhMuc->tatCa();

$tieuDeTrang = 'Danh sách sản phẩm | MiniPos';
$moTaTrang = 'Danh sách sản phẩm MiniPos với tìm kiếm, lọc và sắp xếp tức thì.';
$scriptsTrang = ['js/gio-hang.js', 'js/trang-danh-sach.js'];

require __DIR__ . '/inc/header.php';
?>

<main class="bao trang-danh-sach-san-pham">
    <h1>Danh sách sản phẩm</h1>
    <section aria-labelledby="tieu-de-danh-sach">
        <h2 id="tieu-de-danh-sach">Danh sách các mặt hàng</h2>

        <form method="get" action="danh-sach.php" class="bo-loc-san-pham" id="bo-loc-san-pham">
            <label for="tim-kiem">Tìm kiếm sản phẩm</label>
            <input type="search" id="tim-kiem" name="q"
                   value="<?= e($q) ?>" placeholder="Nhập tên hoặc mã sản phẩm...">

            <label for="loc-danh-muc">Danh mục</label>
            <select id="loc-danh-muc" name="danh-muc">
                <option value="">Tất cả danh mục</option>
                <?php foreach ($danhMucList as $muc): ?>
                    <option value="<?= e($muc['id']) ?>"
                        <?= $dm === (int) $muc['id'] ? 'selected' : '' ?>>
                        <?= e($muc['ten']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="gia-toi-da">Giá tối đa (VNĐ)</label>
            <input type="number" id="gia-toi-da" name="giaToiDa"
                   min="0" step="1000" placeholder="Không giới hạn"
                   value="<?= $giaToiDa !== null ? e($giaToiDa) : '' ?>">

            <label for="sap-xep">Sắp xếp</label>
            <select id="sap-xep" name="sap-xep">
                <option value="moi" <?= $sx === 'moi' ? 'selected' : '' ?>>Mới nhất</option>
                <option value="gia-tang" <?= $sx === 'gia-tang' ? 'selected' : '' ?>>Giá tăng dần</option>
                <option value="gia-giam" <?= $sx === 'gia-giam' ? 'selected' : '' ?>>Giá giảm dần</option>
                <option value="ten" <?= $sx === 'ten' ? 'selected' : '' ?>>Tên A-Z</option>
            </select>

            <button type="submit">Áp dụng</button>
            <a href="danh-sach.php" class="xoa-bo-loc">Xóa bộ lọc</a>
        </form>

        <p id="trang-thai-san-pham" aria-live="polite">
            Có <?= e($tongDong) ?> sản phẩm.
        </p>

        <div class="luoi-san-pham" id="luoi-san-pham">
            <?php if ($danhSach === []): ?>
                <p>Hiện chưa có sản phẩm.</p>
            <?php else: ?>
                <?php foreach ($danhSach as $sanPham): ?>
                    <article class="the-san-pham"
                        data-gia="<?= e($sanPham->getGia()) ?>"
                        data-ten="<?= e($sanPham->getTen()) ?>"
                        data-ma="<?= e($sanPham->getId()) ?>"
                        data-danh-muc="<?= e($sanPham->getDanhMucId()) ?>">

                        <img src="<?= e($sanPham->getAnh() ?: 'images/MiniPOS.webp') ?>"
                             alt="Hình ảnh <?= e($sanPham->getTen()) ?>"
                             width="800" height="533" loading="lazy"
                             onerror="this.onerror=null;this.src='images/MiniPOS.webp';">

                        <div class="noi-dung-san-pham">
                            <h3><?= e($sanPham->getTen()) ?></h3>
                            <p class="ma-san-pham">Mã sản phẩm: <?= e($sanPham->getId()) ?></p>
                            <p class="gia-san-pham"><?= e(vnd($sanPham->getGia())) ?></p>
                            <p>Tồn kho: <?= e($sanPham->getTonKho()) ?></p>

                            <div class="hang-nut-san-pham">
                                <a class="nut-xem-chi-tiet"
                                   href="chi-tiet.php?id=<?= e(rawurlencode((string) $sanPham->getId())) ?>">
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
    </section>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
