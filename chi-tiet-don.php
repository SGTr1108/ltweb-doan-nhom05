<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

$user = yeuCauDangNhap();

$donId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if ($donId === false || $donId < 1) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$khoDonHang = new \App\Data\KhoDonHang(db());

// Nếu quan_tri -> null để xem mọi đơn; khach_hang -> truyền uid cá nhân
$nguoiDungIdLoc = ($user['vai_tro'] === 'quan_tri') ? null : (int) $user['id'];
$chiTiet        = $khoDonHang->chiTiet($donId, $nguoiDungIdLoc);

if ($chiTiet === null) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$don  = $chiTiet['don'];
$dong = $chiTiet['dong'];

$tieuDeTrang  = "Chi tiết đơn hàng #{$don['id']} | MiniPos";
$moTaTrang    = "Xem thông tin chi tiết đơn hàng #{$don['id']}.";
$scriptsTrang = [];

require __DIR__ . '/inc/header.php';
?>

<main class="bao">
    <h1>Chi tiết đơn hàng #<?= e($don['id']) ?></h1>

    <section>
        <h2>Thông tin chung</h2>
        <p><strong>Khách hàng:</strong> <?= e($don['ho_ten']) ?> (<?= e($don['email']) ?>)</p>
        <p><strong>Ngày đặt:</strong> <?= e($don['ngay_dat']) ?></p>
        <p><strong>Trạng thái:</strong> <?= e(\App\Data\KhoDonHang::TRANG_THAI[$don['trang_thai']] ?? $don['trang_thai']) ?></p>
        <?php if (!empty($don['ghi_chu'])): ?>
            <p><strong>Ghi chú:</strong> <?= e($don['ghi_chu']) ?></p>
        <?php endif; ?>
    </section>

    <section>
        <h2>Danh sách sản phẩm</h2>
        <div class="bang-vo">
            <table>
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Đơn giá lúc đặt</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dong as $matHang): ?>
                        <tr>
                            <td><?= e($matHang['ten']) ?></td>
                            <td><?= e(vnd($matHang['don_gia'])) ?></td>
                            <td><?= e($matHang['so_luong']) ?></td>
                            <td><?= e(vnd($matHang['thanh_tien'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p><strong>Tổng cộng: <?= e(vnd($don['tong_tien'])) ?></strong></p>
    </section>

    <p><a href="don-hang.php">Quay lại danh sách đơn hàng</a></p>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>