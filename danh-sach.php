<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';
if (empty($_SESSION['csrf_gio_hang'])) {
    $_SESSION['csrf_gio_hang'] = bin2hex(random_bytes(32));
}

$khoSanPham = new \App\Data\KhoSanPham();
$danhSachTatCa = $khoSanPham->layDanhSach();

$tuKhoa = trim((string) ($_GET['q'] ?? ''));
$danhMucChon = trim((string) ($_GET['danh-muc'] ?? 'tat-ca'));
$cachSapXep = (string) ($_GET['sap-xep'] ?? 'mac-dinh');

$danhMuc = array_values(array_unique(array_map(
    static fn (\App\Models\SanPham $sanPham): string =>
        $sanPham->getDanhMuc(),
    $danhSachTatCa
)));

if (
    $danhMucChon !== 'tat-ca'
    && !in_array($danhMucChon, $danhMuc, true)
) {
    $danhMucChon = 'tat-ca';
}

$cacCachSapXep = [
    'mac-dinh',
    'gia-tang',
    'gia-giam',
    'ten-az',
    'ten-za',
];

if (!in_array($cachSapXep, $cacCachSapXep, true)) {
    $cachSapXep = 'mac-dinh';
}

$chuanHoa = static function (string $chuoi): string {
    if (function_exists('mb_strtolower')) {
        $chuoi = mb_strtolower($chuoi, 'UTF-8');
    } else {
        $chuoi = strtolower($chuoi);
    }

    $chuoi = strtr($chuoi, ['đ' => 'd']);

    if (function_exists('iconv')) {
        $khongDau = iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            $chuoi
        );

        if ($khongDau !== false) {
            $chuoi = $khongDau;
        }
    }

    return $chuoi;
};

$danhSach = $danhSachTatCa;

if ($tuKhoa !== '') {
    $tuKhoaChuanHoa = $chuanHoa($tuKhoa);

    $danhSach = array_values(array_filter(
        $danhSach,
        static function (
            \App\Models\SanPham $sanPham
        ) use ($tuKhoaChuanHoa, $chuanHoa): bool {
            return str_contains(
                $chuanHoa($sanPham->getTen()),
                $tuKhoaChuanHoa
            ) || str_contains(
                $chuanHoa($sanPham->getId()),
                $tuKhoaChuanHoa
            );
        }
    ));
}

if ($danhMucChon !== 'tat-ca') {
    $danhSach = array_values(array_filter(
        $danhSach,
        static fn (\App\Models\SanPham $sanPham): bool =>
            $sanPham->getDanhMuc() === $danhMucChon
    ));
}

switch ($cachSapXep) {
    case 'gia-tang':
        usort(
            $danhSach,
            static fn ($a, $b): int =>
                $a->getGia() <=> $b->getGia()
        );
        break;

    case 'gia-giam':
        usort(
            $danhSach,
            static fn ($a, $b): int =>
                $b->getGia() <=> $a->getGia()
        );
        break;

    case 'ten-az':
        usort(
            $danhSach,
            static fn ($a, $b): int =>
                strcmp(
                    $chuanHoa($a->getTen()),
                    $chuanHoa($b->getTen())
                )
        );
        break;

    case 'ten-za':
        usort(
            $danhSach,
            static fn ($a, $b): int =>
                strcmp(
                    $chuanHoa($b->getTen()),
                    $chuanHoa($a->getTen())
                )
        );
        break;
}

$tieuDeTrang = 'Danh sách sản phẩm | MiniPos';
$moTaTrang = 'Danh sách sản phẩm MiniPos, bao gồm mã, giá bán, danh mục và tồn kho.';
$scriptsTrang = ['js/trang-danh-sach.js'];

require __DIR__ . '/inc/header.php';
?>

<main class="bao">
    <h1>Danh sách sản phẩm</h1>

    <section>
        <h2>Danh sách các mặt hàng</h2>

        <p>
            Các sản phẩm đang được kinh doanh và quản lý
            trên hệ thống MiniPos.
        </p>

        <form
            method="get"
            action="danh-sach.php"
            class="bo-loc-san-pham">

            <label for="tim-kiem">Tìm kiếm sản phẩm</label>
            <input
                type="search"
                id="tim-kiem"
                name="q"
                value="<?= e($tuKhoa) ?>"
                placeholder="Nhập tên hoặc mã sản phẩm">

            <label for="loc-danh-muc">Lọc theo danh mục</label>
            <select id="loc-danh-muc" name="danh-muc">
                <option
                    value="tat-ca"
                    <?= $danhMucChon === 'tat-ca' ? 'selected' : '' ?>>
                    Tất cả danh mục
                </option>

                <?php foreach ($danhMuc as $muc): ?>
                    <option
                        value="<?= e($muc) ?>"
                        <?= $danhMucChon === $muc ? 'selected' : '' ?>>
                        <?= e($muc) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="sap-xep">Sắp xếp</label>
            <select id="sap-xep" name="sap-xep">
                <option value="mac-dinh"
                    <?= $cachSapXep === 'mac-dinh' ? 'selected' : '' ?>>
                    Mặc định
                </option>
                <option value="gia-tang"
                    <?= $cachSapXep === 'gia-tang' ? 'selected' : '' ?>>
                    Giá tăng dần
                </option>
                <option value="gia-giam"
                    <?= $cachSapXep === 'gia-giam' ? 'selected' : '' ?>>
                    Giá giảm dần
                </option>
                <option value="ten-az"
                    <?= $cachSapXep === 'ten-az' ? 'selected' : '' ?>>
                    Tên A-Z
                </option>
                <option value="ten-za"
                    <?= $cachSapXep === 'ten-za' ? 'selected' : '' ?>>
                    Tên Z-A
                </option>
            </select>

            <button type="submit">Áp dụng</button>
            <a href="danh-sach.php">Xóa bộ lọc</a>
        </form>

        <p id="trang-thai-san-pham" aria-live="polite">
            Có <?= e(count($danhSach)) ?> sản phẩm phù hợp.
        </p>

        <div class="luoi-san-pham">
            <?php if ($danhSach === []): ?>
                <p>Không tìm thấy sản phẩm phù hợp.</p>
            <?php else: ?>
                <?php foreach ($danhSach as $sanPham): ?>
                    <article class="the-san-pham" data-ten="<?= e($sanPham->getTen()) ?>" data-ma="<?= e($sanPham->getId()) ?>" data-danh-muc="<?= e($sanPham->getDanhMuc()) ?>" data-gia="<?= e($sanPham->getGia()) ?>">
                        <img
                            src="<?= e($sanPham->getAnh()) ?>"
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
                                <a href="chi-tiet.php?id=<?= e(rawurlencode($sanPham->getId())) ?>">
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

                                <button
                                    type="button"
                                    class="nut-yeu-thich"
                                    data-yeu-thich="<?= e($sanPham->getId()) ?>"
                                    aria-pressed="false">
                                    ♡ Yêu thích
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>