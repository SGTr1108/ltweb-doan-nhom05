<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

$_SESSION['csrf_gio_hang'] ??= bin2hex(random_bytes(32));

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

// Xây dựng mảng điều kiện
$dieuKien = [];
if ($q !== '') {
    $dieuKien['q']        = $q;
    $dieuKien['tu_khoa']  = $q;
}
if ($dm !== null && $dm > 0) {
    $dieuKien['danh_muc_id'] = $dm;
}
if ($giaToiDa !== null && $giaToiDa > 0) {
    $dieuKien['gia_toi_da'] = $giaToiDa;
}

// 1. Lấy tất cả sản phẩm theo thứ tự sắp xếp
$danhSachGoc = $khoSanPham->trang($dieuKien, $sx, 100, 0);
if (empty($danhSachGoc)) {
    $danhSachGoc = $khoSanPham->tatCa();
}

// 2. Lọc chính xác ở phía PHP (bảo đảm loại bỏ 100% sản phẩm không khớp khỏi HTML)
$danhSach = array_filter($danhSachGoc, function ($sanPham) use ($q, $dm, $giaToiDa) {
    // Lọc theo từ khóa (tên hoặc mã ID)
    if ($q !== '') {
        $ten = mb_strtolower($sanPham->getTen(), 'UTF-8');
        $ma  = (string) $sanPham->getId();
        $tk  = mb_strtolower($q, 'UTF-8');
        if (mb_strpos($ten, $tk) === false && mb_strpos($ma, $tk) === false) {
            return false;
        }
    }

    // Lọc theo danh mục
    if ($dm !== null && $dm > 0) {
        $spDm = method_exists($sanPham, 'getDanhMucId') ? (int) $sanPham->getDanhMucId() : 0;
        if ($spDm !== $dm) {
            return false;
        }
    }

    // Lọc theo giá tối đa
    if ($giaToiDa !== null && $giaToiDa > 0) {
        if ($sanPham->getGia() > $giaToiDa) {
            return false;
        }
    }

    return true;
});

// Reset lại chỉ số mảng sau khi lọc
$danhSach = array_values($danhSach);
$tongDong = count($danhSach);
$danhMucList = $khoDanhMuc->tatCa();

$tieuDeTrang  = 'Danh sách sản phẩm | MiniPos';
$moTaTrang    = 'Danh sách sản phẩm MiniPos với tìm kiếm, lọc và sắp xếp tức thì.';
$scriptsTrang = ['js/gio-hang.js', 'js/trang-danh-sach.js'];

require __DIR__ . '/inc/header.php';
?>

<!-- Đảm bảo các sản phẩm bị ẩn triệt để, không bị CSS khác ghi đè -->
<style>
    .luoi-san-pham .the-san-pham[hidden],
    .luoi-san-pham .the-san-pham.an,
    .luoi-san-pham .the-san-pham.hidden {
        display: none !important;
    }
</style>

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

<script>
    // Lọc tức thì trên giao diện khi người dùng nhập từ khóa / chọn danh mục
    document.addEventListener('DOMContentLoaded', function() {
        const inputQ = document.getElementById('tim-kiem');
        const selectDm = document.getElementById('loc-danh-muc');
        const inputGia = document.getElementById('gia-toi-da');
        const luoi = document.getElementById('luoi-san-pham');
        const trangThai = document.getElementById('trang-thai-san-pham');

        if (!luoi) return;

        function locTucThi() {
            const q = (inputQ ? inputQ.value : '').trim().toLowerCase();
            const dm = selectDm ? selectDm.value : '';
            const giaMax = inputGia && inputGia.value !== '' ? parseFloat(inputGia.value) : Infinity;

            const cards = luoi.querySelectorAll('.the-san-pham');
            let dem = 0;

            cards.forEach(card => {
                const ten = (card.dataset.ten || '').toLowerCase();
                const ma = (card.dataset.ma || '').toLowerCase();
                const cardDm = card.dataset.danhMuc || '';
                const gia = parseFloat(card.dataset.gia) || 0;

                const khopQ = !q || ten.includes(q) || ma.includes(q);
                const khopDm = !dm || cardDm === dm;
                const khopGia = gia <= giaMax;

                if (khopQ && khopDm && khopGia) {
                    card.classList.remove('an', 'hidden');
                    card.removeAttribute('hidden');
                    card.style.setProperty('display', 'flex', 'important');
                    dem++;
                } else {
                    card.classList.add('an');
                    card.setAttribute('hidden', 'true');
                    card.style.setProperty('display', 'none', 'important');
                }
            });

            if (trangThai) {
                trangThai.textContent = 'Có ' + dem + ' sản phẩm.';
            }
        }

        if (inputQ) inputQ.addEventListener('input', locTucThi);
        if (selectDm) selectDm.addEventListener('change', locTucThi);
        if (inputGia) inputGia.addEventListener('input', locTucThi);
    });
</script>

<?php require __DIR__ . '/inc/footer.php'; ?>