<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

// Bắt buộc đăng nhập
$user = yeuCauDangNhap();

$khoDonHang = new \App\Data\KhoDonHang(db());

// Phân quyền: Admin xem tất cả hóa đơn, Nhân viên xem hóa đơn do mình lập
$isQuanTri = (($user['vai_tro'] ?? '') === 'quan_tri');
$danhSachDon = $isQuanTri
    ? $khoDonHang->tatCa()
    : $khoDonHang->cuaNhanVien((int) $user['id']);

$tieuDeTrang  = $isQuanTri ? 'Quản lý hóa đơn toàn hệ thống | MiniPos' : 'Hóa đơn do tôi lập | MiniPos';
$moTaTrang    = 'Danh sách và lịch sử hóa đơn bán hàng quầy.';
$scriptsTrang = [];

require __DIR__ . '/inc/header.php';
?>

<main class="bao">
    <h1><?= $isQuanTri ? 'Quản lý toàn bộ hóa đơn' : 'Hóa đơn do tôi lập' ?></h1>

    <?php if (isset($_SESSION['flash_don_hang'])): ?>
        <p class="thong-bao-thanh-cong" role="status"><?= e($_SESSION['flash_don_hang']) ?></p>
        <?php unset($_SESSION['flash_don_hang']); ?>
    <?php endif; ?>

    <?php if ($danhSachDon === []): ?>
        <p>Chưa có hóa đơn nào. <a href="ban-hang.php">Sang màn hình bán hàng</a> để lập hóa đơn mới!</p>
    <?php else: ?>
        <div class="bang-vo">
            <table>
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Ngày đặt</th>
                        <?php if ($isQuanTri): ?>
                            <th>Nhân viên lập</th>
                        <?php endif; ?>
                        <th>Khách hàng</th>
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
                            <?php if ($isQuanTri): ?>
                                <td><?= e($don['ten_nhan_vien'] ?? 'N/A') ?></td>
                            <?php endif; ?>
                            <td>
                                <?= e($don['ten_khach_hang'] ?: 'Khách vãng lai') ?>
                                <?php if (!empty($don['sdt_khach_hang'])): ?>
                                    <br><small>(<?= e($don['sdt_khach_hang']) ?>)</small>
                                <?php endif; ?>
                            </td>
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