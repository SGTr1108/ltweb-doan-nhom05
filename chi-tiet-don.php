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

// Quản trị viên xem mọi đơn; Nhân viên chỉ xem hóa đơn do mình lập
$nhanVienIdLoc = ($user['vai_tro'] === 'quan_tri') ? null : (int) $user['id'];
$chiTiet       = $khoDonHang->chiTiet($donId, $nhanVienIdLoc);

if ($chiTiet === null) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$don  = $chiTiet['don'];
$dong = $chiTiet['dong'];

$tieuDeTrang  = "Chi tiết hóa đơn #{$don['id']} | MiniPos";
$moTaTrang    = "Xem thông tin chi tiết hóa đơn #{$don['id']}.";
$scriptsTrang = [];

require __DIR__ . '/inc/header.php';
?>

<main class="bao">
    <h1>Chi tiết hóa đơn #<?= e($don['id']) ?></h1>

    <section>
        <h2>Thông tin chung</h2>
        <p><strong>Nhân viên lập đơn:</strong> <?= e($don['ten_nhan_vien'] ?? 'N/A') ?> (<?= e($don['email_nhan_vien'] ?? '') ?>)</p>
        <p><strong>Khách hàng:</strong> <?= e($don['ten_khach_hang'] ?: 'Khách vãng lai') ?> <?= !empty($don['sdt_khach_hang']) ? '- SĐT: ' . e($don['sdt_khach_hang']) : '' ?></p>
        <p><strong>Ngày lập:</strong> <?= e($don['ngay_dat']) ?></p>
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
                        <th>Đơn giá</th>
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
        <p><strong>Tổng tiền hóa đơn: <?= e(vnd($don['tong_tien'])) ?></strong></p>
        <p><strong>Tiền khách đưa:</strong> <?= e(vnd($don['tien_khach_dua'] ?? 0)) ?></p>
        <p><strong>Tiền thối lại:</strong> <?= e(vnd($don['tien_thoi_lai'] ?? 0)) ?></p>
    </section>

    <p><a href="don-hang.php">Quay lại danh sách hóa đơn</a></p>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>