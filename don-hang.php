<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

$user = yeuCauDangNhap();

$khoDonHang  = new \App\Data\KhoDonHang(db());
$danhSachDon = $khoDonHang->cuaNguoiDung((int) $user['id']);

$tieuDeTrang  = 'Đơn hàng của tôi | MiniPos';
$moTaTrang    = 'Lịch sử và danh sách đơn hàng đã đặt.';
$scriptsTrang = [];

require __DIR__ . '/inc/header.php';
?>

<main class="bao">
    <h1>Đơn hàng của tôi</h1>

    <?php if (isset($_SESSION['flash_don_hang'])): ?>
        <p class="thong-bao-thanh-cong" role="status"><?= e($_SESSION['flash_don_hang']) ?></p>
        <?php unset($_SESSION['flash_don_hang']); ?>
    <?php endif; ?>

    <?php if ($danhSachDon === []): ?>
        <p>Bạn chưa có đơn hàng nào. <a href="danh-sach.php">Khám phá sản phẩm</a> để mua sắm ngay!</p>
    <?php else: ?>
        <div class="bang-vo">
            <table>
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Ngày đặt</th>
                        <th>Số món</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($danhSachDon as $don): ?>
                        <tr>
                            <td>#<?= e($don['id']) ?></td>
                            <td><?= e($don['ngay_dat']) ?></td>
                            <td><?= e($don['so_mon']) ?></td>
                            <td><?= e(vnd($don['tong_tien'])) ?></td>
                            <td><?= e(\App\Data\KhoDonHang::TRANG_THAI[$don['trang_thai']] ?? $don['trang_thai']) ?></td>
                            <td>
                                <a href="chi-tiet-don.php?id=<?= e($don['id']) ?>">Xem chi tiết</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>