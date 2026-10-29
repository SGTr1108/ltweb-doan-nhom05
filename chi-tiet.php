<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';


$maSanPham = isset($_GET['id']) && is_string($_GET['id'])
    ? trim($_GET['id'])
    : '';

$khoSanPham = new \App\Data\KhoSanPham(db());
$sanPham = $maSanPham !== ''
    ? $khoSanPham->timTheoId($maSanPham)
    : null;

if ($sanPham === null) {
    http_response_code(404);
}

if ($sanPham !== null) {
    $lichSuXem = [];

    if (isset($_COOKIE['da_xem']) && is_string($_COOKIE['da_xem'])) {
        $lichSuXem = array_filter(
            explode(',', $_COOKIE['da_xem']),
            static fn(string $id): bool => preg_match('/^\d+$/', $id) === 1
        );
    }

    // Đưa sản phẩm vừa xem lên đầu danh sách, không lặp mã.
    $lichSuXem = array_values(array_unique([
        $sanPham->getId(),
        ...$lichSuXem
    ]));

    // Chỉ lưu tối đa 4 sản phẩm gần nhất.
    $lichSuXem = array_slice($lichSuXem, 0, 4);

    setcookie('da_xem', implode(',', $lichSuXem), [
        'expires' => time() + 30 * 24 * 60 * 60,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

$tieuDeTrang = $sanPham !== null
    ? 'Chi tiết ' . $sanPham->getTen() . ' | MiniPos'
    : 'Không tìm thấy sản phẩm | MiniPos';

$moTaTrang = $sanPham !== null
    ? 'Thông tin sản phẩm ' . $sanPham->getTen()
    . ', mã ' . $sanPham->getId()
    . ', giá bán và số lượng tồn kho.'
    : 'Không tìm thấy sản phẩm được yêu cầu trên MiniPos.';


$lichSuNhapKho = [
    'SP001' => [
        ['ngay' => '08/10/2026', 'soLuong' => 100, 'nhaCungCap' => 'Nhà phân phối Khánh Bình'],
        ['ngay' => '01/10/2026', 'soLuong' => 50, 'nhaCungCap' => 'Nhà phân phối snack Khánh Bình'],
    ],
    'SP002' => [
        ['ngay' => '10/09/2026', 'soLuong' => 100, 'nhaCungCap' => 'Công ty Nước giải khát Aquafina (minh họa)'],
        ['ngay' => '25/08/2026', 'soLuong' => 50, 'nhaCungCap' => 'Công ty Nước giải khát Aquafina (minh họa)'],
    ],
    'SP003' => [
        ['ngay' => '07/10/2026', 'soLuong' => 60, 'nhaCungCap' => 'Nhà phân phối mì ăn liền (minh họa)'],
        ['ngay' => '28/09/2026', 'soLuong' => 40, 'nhaCungCap' => 'Nhà phân phối mì ăn liền (minh họa)'],
    ],
    'SP004' => [
        ['ngay' => '06/10/2026', 'soLuong' => 30, 'nhaCungCap' => 'Nhà cung cấp trà sữa (minh họa)'],
        ['ngay' => '29/09/2026', 'soLuong' => 20, 'nhaCungCap' => 'Nhà cung cấp trà sữa (minh họa)'],
    ],
    'SP005' => [
        ['ngay' => '08/10/2026', 'soLuong' => 80, 'nhaCungCap' => 'Nhà phân phối bánh kẹo (minh họa)'],
        ['ngay' => '30/09/2026', 'soLuong' => 40, 'nhaCungCap' => 'Nhà phân phối bánh kẹo (minh họa)'],
    ],
    'SP006' => [
        ['ngay' => '07/10/2026', 'soLuong' => 50, 'nhaCungCap' => 'Nhà phân phối cà phê (minh họa)'],
        ['ngay' => '27/09/2026', 'soLuong' => 30, 'nhaCungCap' => 'Nhà phân phối cà phê (minh họa)'],
    ],
    'SP007' => [
        ['ngay' => '05/10/2026', 'soLuong' => 70, 'nhaCungCap' => 'Nhà phân phối trà xanh (minh họa)'],
        ['ngay' => '26/09/2026', 'soLuong' => 40, 'nhaCungCap' => 'Nhà phân phối trà xanh (minh họa)'],
    ],
    'SP008' => [
        ['ngay' => '08/10/2026', 'soLuong' => 60, 'nhaCungCap' => 'Nhà phân phối bánh quy (minh họa)'],
        ['ngay' => '30/09/2026', 'soLuong' => 35, 'nhaCungCap' => 'Nhà phân phối bánh quy (minh họa)'],
    ],
    'SP009' => [
        ['ngay' => '06/10/2026', 'soLuong' => 70, 'nhaCungCap' => 'Nhà phân phối nước giải khát (minh họa)'],
        ['ngay' => '28/09/2026', 'soLuong' => 45, 'nhaCungCap' => 'Nhà phân phối nước giải khát (minh họa)'],
    ],
    'SP010' => [
        ['ngay' => '07/10/2026', 'soLuong' => 40, 'nhaCungCap' => 'Nhà cung cấp thực phẩm (minh họa)'],
        ['ngay' => '25/09/2026', 'soLuong' => 25, 'nhaCungCap' => 'Nhà cung cấp thực phẩm (minh họa)'],
    ],
    'SP011' => [
        ['ngay' => '08/10/2026', 'soLuong' => 90, 'nhaCungCap' => 'Nhà phân phối bánh tráng (minh họa)'],
        ['ngay' => '29/09/2026', 'soLuong' => 50, 'nhaCungCap' => 'Nhà phân phối bánh tráng (minh họa)'],
    ],
    'SP012' => [
        ['ngay' => '06/10/2026', 'soLuong' => 55, 'nhaCungCap' => 'Nhà cung cấp thực phẩm đông lạnh (minh họa)'],
        ['ngay' => '24/09/2026', 'soLuong' => 35, 'nhaCungCap' => 'Nhà cung cấp thực phẩm đông lạnh (minh họa)'],
    ],
];
$scriptsTrang = [];

require __DIR__ . '/inc/header.php';
?>

<main class="bao">
    <?php if ($sanPham === null): ?>

        <section>
            <h1>Không tìm thấy sản phẩm</h1>
            <p>Mã sản phẩm không tồn tại hoặc đã bị thay đổi.</p>
            <p>
                <a href="danh-sach.php">Quay lại danh sách sản phẩm</a>
            </p>
        </section>

    <?php else: ?>

        <div class="bo-cuc-chi-tiet">
            <article class="noi-dung-chi-tiet" id="chi-tiet-san-pham">

                <h1>
                    Chi tiết sản phẩm: <?= e($sanPham->getTen()) ?>
                </h1>

                <p>
                    Thông tin chi tiết của sản phẩm
                    <?= e($sanPham->getTen()) ?> đang được quản lý
                    trên hệ thống MiniPos.
                </p>

                <figure>
                    <img
                        id="anh-san-pham"
                        src="<?= e($sanPham->getAnh()) ?>"
                        alt="Hình ảnh <?= e($sanPham->getTen()) ?>"
                        width="350"
                        height="400">

                    <figcaption>
                        Hình ảnh sản phẩm <?= e($sanPham->getTen()) ?>.
                    </figcaption>
                </figure>

                <h2>Thông số sản phẩm</h2>

                <div class="bang-vo">
                    <table>
                        <caption>
                            Thông tin sản phẩm <?= e($sanPham->getId()) ?>
                        </caption>

                        <thead>
                            <tr>
                                <th scope="col">Thông tin</th>
                                <th scope="col">Chi tiết</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <th scope="row">Mã sản phẩm</th>
                                <td><?= e($sanPham->getId()) ?></td>
                            </tr>

                            <tr>
                                <th scope="row">Tên sản phẩm</th>
                                <td><?= e($sanPham->getTen()) ?></td>
                            </tr>

                            <tr>
                                <th scope="row">Giá bán</th>
                                <td><?= e(vnd($sanPham->getGia())) ?></td>
                            </tr>

                            <tr>
                                <th scope="row">Số lượng tồn kho</th>
                                <td><?= e($sanPham->getTonKho()) ?></td>
                            </tr>

                            <tr>
                                <th scope="row">Danh mục</th>
                                <td><?= e($sanPham->getDanhMuc()) ?></td>
                            </tr>

                            <tr>
                                <th scope="row">Đơn vị tính</th>
                                <td>
                                    <?= $sanPham->getId() === 'SP002'
                                        ? 'Chai'
                                        : 'Chưa khai báo' ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h2>Lịch sử nhập kho</h2>
                <p>Dữ liệu dưới đây là dữ liệu minh họa, chưa phải lịch sử giao dịch thực tế.</p>

                <?php $lichSu = $lichSuNhapKho[$sanPham->getId()] ?? []; ?>

                <div class="bang-vo">
                    <table>
                        <caption>Lịch sử nhập hàng: <?= e($sanPham->getTen()) ?></caption>
                        <thead>
                            <tr>
                                <th scope="col">Ngày nhập</th>
                                <th scope="col">Số lượng</th>
                                <th scope="col">Nhà cung cấp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lichSu as $dong): ?>
                                <tr>
                                    <td><?= e($dong['ngay']) ?></td>
                                    <td><?= e($dong['soLuong']) ?></td>
                                    <td><?= e($dong['nhaCungCap']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </article>

            <aside class="thong-tin-phu">
                <h2>Thông tin nhanh</h2>

                <ul id="thong-tin-nhanh">
                    <li>Mã sản phẩm: <?= e($sanPham->getId()) ?></li>
                    <li>Tên sản phẩm: <?= e($sanPham->getTen()) ?></li>
                    <li>Giá bán: <?= e(vnd($sanPham->getGia())) ?></li>
                    <li>Tồn kho: <?= e($sanPham->getTonKho()) ?></li>
                    <li>Danh mục: <?= e($sanPham->getDanhMuc()) ?></li>
                </ul>

                <button
                    type="button"
                    class="nut-yeu-thich"
                    data-yeu-thich="<?= e($sanPham->getId()) ?>"
                    aria-pressed="false">
                    ♡ Yêu thích
                </button>

                <h2>Liên kết</h2>

                <p>
                    <a href="danh-sach.php">Quay lại danh sách sản phẩm</a>
                </p>

                <p>
                    <a href="lien-he.php">Báo cáo và liên hệ</a>
                </p>
            </aside>
        </div>

    <?php endif; ?>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>