<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';


$khoDanhMuc = new \App\Data\KhoDanhMuc(db());
$khoSanPham = new \App\Data\KhoSanPham(db());

// Lấy giá trị bộ lọc từ URL
$q        = trim((string) ($_GET['q'] ?? $_GET['tu_khoa'] ?? $_GET['tim-kiem'] ?? ''));
$dm       = filter_var($_GET['danh-muc'] ?? $_GET['danh_muc_id'] ?? $_GET['danh_muc'] ?? null, FILTER_VALIDATE_INT) ?: null;
$giaToiDa = filter_var($_GET['giaToiDa'] ?? $_GET['gia_toi_da'] ?? $_GET['gia'] ?? null, FILTER_VALIDATE_INT) ?: null;
$sx       = (string) ($_GET['sap-xep'] ?? $_GET['sap_xep'] ?? $_GET['sx'] ?? 'moi');

if (!in_array($sx, ['moi', 'gia-tang', 'gia-giam', 'ten'], true)) {
    $sx = 'moi';
}

// Bộ lọc dùng chung cho dem() và trang() (đúng khóa mà KhoSanPham::dieuKien đọc)
$loc = ['q' => $q, 'dm' => $dm, 'giaToiDa' => $giaToiDa];

// Phân trang: COUNT(*) để biết tổng, LIMIT ? OFFSET ? để lấy đúng một trang
$soSpMotTrang = 12;
$tongDong     = $khoSanPham->dem($loc);
$soTrang      = max(1, (int) ceil($tongDong / $soSpMotTrang));
$trang        = filter_var($_GET['trang'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
$trang        = min($trang, $soTrang);
$danhSach     = $khoSanPham->trang($loc, $sx, $soSpMotTrang, ($trang - 1) * $soSpMotTrang);

// Giữ bộ lọc khi chuyển trang
$thamSoLoc = array_filter(
    ['q' => $q, 'danh-muc' => $dm, 'giaToiDa' => $giaToiDa, 'sap-xep' => $sx],
    static fn($giaTri) => $giaTri !== null && $giaTri !== ''
);
$lienKetTrang = static fn(int $t): string => 'danh-sach.php?' . http_build_query($thamSoLoc + ['trang' => $t]);

$danhMucList = $khoDanhMuc->tatCa();

$tieuDeTrang  = 'Danh sách sản phẩm | MiniPos';
$moTaTrang    = 'Danh sách sản phẩm MiniPos với tìm kiếm, lọc và sắp xếp tức thì.';
$scriptsTrang = ['js/gio-hang.js', 'js/tim-kiem.js'];

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
            Có <?= e($tongDong) ?> sản phẩm<?= $soTrang > 1 ? ' (trang ' . e($trang) . '/' . e($soTrang) . ')' : '' ?>.
        </p>

        <div class="luoi-san-pham" id="luoi-san-pham"
            data-csrf="<?= e(csrfToken()) ?>">
            <?php if ($danhSach === []): ?>
                <p>Không tìm thấy sản phẩm nào phù hợp với bộ lọc.</p>
            <?php else: ?>
                <?php foreach ($danhSach as $sanPham): ?>
                    <?php
                    $danhMucIdVal = method_exists($sanPham, 'getDanhMucId')
                        ? $sanPham->getDanhMucId()
                        : (method_exists($sanPham, 'getDanhMuc') ? $sanPham->getDanhMuc() : '');
                    ?>
                    <article class="the-san-pham"
                        data-gia="<?= e($sanPham->getGia()) ?>"
                        data-ten="<?= e($sanPham->getTen()) ?>"
                        data-ma="<?= e($sanPham->getId()) ?>"
                        data-danh-muc="<?= e($danhMucIdVal) ?>">

                        <img src="<?= e($sanPham->getAnh() ?: 'images/MiniPOS.webp') ?>"
                            alt="Hình ảnh <?= e($sanPham->getTen()) ?>"
                            width="800" height="533" loading="lazy"
                            data-anh-du-phong="images/MiniPOS.webp">

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
                                    <?= csrfField() ?>
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
        <?php if ($soTrang > 1): ?>
            <nav class="phan-trang" id="phan-trang" aria-label="Phân trang sản phẩm">
                <ul>
                    <?php if ($trang > 1): ?>
                        <li><a href="<?= e($lienKetTrang($trang - 1)) ?>" rel="prev">Trước</a></li>
                    <?php endif; ?>
                    <?php for ($t = 1; $t <= $soTrang; $t++): ?>
                        <li>
                            <a href="<?= e($lienKetTrang($t)) ?>"
                                <?= $t === $trang ? 'aria-current="page"' : '' ?>><?= e($t) ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($trang < $soTrang): ?>
                        <li><a href="<?= e($lienKetTrang($trang + 1)) ?>" rel="next">Sau</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>