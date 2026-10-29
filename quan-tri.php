<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/bao-ve.php';

$tieuDeTrang = 'Quản trị | MiniPos';
$moTaTrang = 'Khu vực quản trị hệ thống MiniPos.';
$scriptsTrang = [];

$nguoiDung = $_SESSION['nguoi_dung'];
$loiDuLieu = false;
$danhSachSanPham = [];
$danhSachPhieu = [];

// Chỉ quản trị viên được truy cập trang này.
if (($nguoiDung['vai_tro'] ?? '') !== 'admin') {
    http_response_code(403);
    $tieuDeTrang = '403 - Không có quyền truy cập | MiniPos';

    require __DIR__ . '/inc/header.php';
?>
    <main class="bao trang-quan-tri">
        <h1>403 - Không có quyền truy cập</h1>
        <p>Tài khoản hiện tại không có quyền quản trị.</p>
        <p><a href="index.php">Quay lại trang chủ</a></p>
    </main>
<?php
    require __DIR__ . '/inc/footer.php';
    exit;
}

// Tạo token để biểu mẫu đăng xuất sử dụng.
if (empty($_SESSION['csrf_dang_xuat'])) {
    $_SESSION['csrf_dang_xuat'] = bin2hex(random_bytes(32));
}

try {
    $khoSanPham = new \App\Data\KhoSanPham();
    $danhSachSanPham = $khoSanPham->layDanhSach();

    $khoLienHe = new \App\Data\KhoLienHe();
    $danhSachPhieu = $khoLienHe->layDanhSach();

    // Đảo ngược mảng để hiển thị phiếu mới nhất (nằm ở cuối tệp) lên trước
    $danhSachPhieu = array_reverse($danhSachPhieu);
} catch (\Throwable $loi) {
    error_log(
        'Lỗi tải dữ liệu trang quản trị: ' . $loi->getMessage()
    );
    $loiDuLieu = true;
}

require __DIR__ . '/inc/header.php';
?>

<main class="bao trang-quan-tri">
    <h1>Trang quản trị MiniPos</h1>

    <div class="thanh-cong-cu-quan-tri">
        <p class="loi-chao-quan-tri">
            Xin chào,
            <strong><?= e((string) ($nguoiDung['ten_hien_thi'] ?? 'Quản trị viên')) ?></strong>.
        </p>

        <form class="form-dang-xuat-quan-tri" method="post" action="dang-xuat.php">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION['csrf_dang_xuat']) ?>">
            <button type="submit">Đăng xuất</button>
        </form>
    </div>

    <?php if ($loiDuLieu): ?>
        <p role="alert">
            Không thể tải dữ liệu quản trị. Vui lòng thử lại sau.
        </p>
    <?php else: ?>
        <section>
            <h2>Tổng quan</h2>
            <p>
                Số sản phẩm:
                <strong><?= count($danhSachSanPham) ?></strong>
            </p>
            <p>
                Số phiếu báo cáo:
                <strong><?= count($danhSachPhieu) ?></strong>
            </p>
        </section>

        <section>
            <h2>Danh sách sản phẩm</h2>

            <?php if ($danhSachSanPham === []): ?>
                <p>Chưa có dữ liệu sản phẩm.</p>
            <?php else: ?>
                <div class="bang-vo">
                    <table>
                        <thead>
                            <tr>
                                <th scope="col">Mã sản phẩm</th>
                                <th scope="col">Tên sản phẩm</th>
                                <th scope="col">Danh mục</th>
                                <th scope="col">Giá</th>
                                <th scope="col">Tồn kho</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($danhSachSanPham as $sanPham): ?>
                                <tr>
                                    <td><?= e($sanPham->getId()) ?></td>
                                    <td><?= e($sanPham->getTen()) ?></td>
                                    <td><?= e($sanPham->getDanhMuc()) ?></td>
                                    <td><?= e(vnd($sanPham->getGia())) ?></td>
                                    <td><?= e($sanPham->getTonKho()) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <section>
            <h2>Phiếu báo cáo</h2>

            <?php if ($danhSachPhieu === []): ?>
                <p>Chưa có phiếu báo cáo nào.</p>
            <?php else: ?>
                <div class="bang-vo">
                    <table>
                        <thead>
                            <tr>
                                <th scope="col">Mã phiếu</th>
                                <th scope="col">Thời gian</th>
                                <th scope="col">Mã nhân viên</th>
                                <th scope="col">Email</th>
                                <th scope="col">Mức ưu tiên</th>
                                <th scope="col">Nội dung</th>
                                <th scope="col">Loại yêu cầu</th>
                                <th scope="col">Ảnh đính kèm</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($danhSachPhieu as $phieu): ?>
                                <tr>
                                    <td><?= e((string) ($phieu['ma_phieu'] ?? '')) ?></td>
                                    <td><?= e((string) ($phieu['thoi_gian_tao'] ?? '')) ?></td>
                                    <td><?= e((string) ($phieu['manhanvien'] ?? '')) ?></td>
                                    <td><?= e((string) ($phieu['email'] ?? '')) ?></td>
                                    <td><?= e((string) ($phieu['mucdouutien'] ?? '')) ?></td>
                                    <td><?= e((string) ($phieu['noidung'] ?? '')) ?></td>
                                    <td><?= e((string) ($phieu['loaiyeucau'] ?? '')) ?></td>
                                    <td>
                                        <?php if (!empty($phieu['anh_dinh_kem'])): ?>
                                            <a href="uploads/<?= e((string) $phieu['anh_dinh_kem']) ?>" target="_blank">
                                                <img src="uploads/<?= e((string) $phieu['anh_dinh_kem']) ?>"
                                                    alt="Ảnh đính kèm"
                                                    style="max-width: 80px; height: auto; border-radius: 4px;">
                                            </a>
                                        <?php else: ?>
                                            Không
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <p><a href="index.php">Về trang chủ</a></p>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>