<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

// Kiểm tra quyền quan_tri
$user = yeuCauQuyen('quan_tri');

$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));

$khoSanPham = new \App\Data\KhoSanPham(db());
$khoDonHang = new \App\Data\KhoDonHang(db());
$khoLienHe  = new \App\Data\KhoLienHe(db());

// Cập nhật trạng thái đơn hàng (POST)
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['cap_nhat_trang_thai'])) {
    xacNhanCsrf();
    $donId        = filter_var($_POST['don_id'] ?? null, FILTER_VALIDATE_INT);
    $trangThaiMoi = (string) ($_POST['trang_thai'] ?? '');

    if ($donId !== false && $donId > 0 && $khoDonHang->doiTrangThai($donId, $trangThaiMoi)) {
        $_SESSION['flash_quan_tri'] = "Đã cập nhật trạng thái đơn hàng #{$donId}.";
    } else {
        $_SESSION['flash_quan_tri_loi'] = "Cập nhật trạng thái đơn hàng #{$donId} thất bại.";
    }

    header('Location: quan-tri.php', true, 303);
    exit;
}

// Phân trang sản phẩm
$trang = max(1, filter_var($_GET['trang'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
$soDong = 10;
$boQua  = ($trang - 1) * $soDong;

$tongSanPham     = $khoSanPham->dem([]);
$tongTrang       = max(1, (int) ceil($tongSanPham / $soDong));
$danhSachSanPham = $khoSanPham->trang([], 'moi', $soDong, $boQua);

$danhSachDonHang = $khoDonHang->tatCa();
$danhSachLienHe  = $khoLienHe->layDanhSach();

$tieuDeTrang  = 'Trang Quản Trị | MiniPos';
$moTaTrang    = 'Trang quản trị sản phẩm, đơn hàng và phản hồi liên hệ.';
$scriptsTrang = [];

require __DIR__ . '/inc/header.php';
?>

<main class="bao trang-quan-tri">
    <h1>Trang Quản Trị Hệ Thống MiniPos</h1>

    <p>Xin chào, <strong><?= e($user['ho_ten']) ?></strong> (Quản trị viên).</p>

    <?php if (isset($_SESSION['flash_quan_tri'])): ?>
        <p class="thong-bao-thanh-cong" role="status"><?= e($_SESSION['flash_quan_tri']) ?></p>
        <?php unset($_SESSION['flash_quan_tri']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_quan_tri_loi'])): ?>
        <p class="thong-bao-loi" role="alert"><?= e($_SESSION['flash_quan_tri_loi']) ?></p>
        <?php unset($_SESSION['flash_quan_tri_loi']); ?>
    <?php endif; ?>

    <!-- SECTION 1: QUẢN LÝ SẢN PHẨM -->
    <section>
        <h2>Quản lý sản phẩm</h2>
        <p><a href="them-san-pham.php" class="nut-chuc-nang">+ Thêm sản phẩm mới</a></p>

        <div class="bang-vo">
            <table>
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($danhSachSanPham as $sp): ?>
                        <tr>
                            <td><?= e($sp->getId()) ?></td>
                            <td><?= e($sp->getTen()) ?></td>
                            <td><?= e($sp->getDanhMuc()) ?></td>
                            <td><?= e(vnd($sp->getGia())) ?></td>
                            <td><?= e($sp->getTonKho()) ?></td>
                            <td>
                                <a href="sua-san-pham.php?id=<?= e($sp->getId()) ?>">Sửa</a> |
                                <form action="xoa-san-pham.php" method="post" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">
                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="id" value="<?= e($sp->getId()) ?>">
                                    <button type="submit" class="nut-xoa-link">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($tongTrang > 1): ?>
            <p>Trang:
                <?php for ($i = 1; $i <= $tongTrang; $i++): ?>
                    <a href="quan-tri.php?trang=<?= $i ?>" style="<?= $i === $trang ? 'font-weight:bold;' : '' ?>">[<?= $i ?>]</a>
                <?php endfor; ?>
            </p>
        <?php endif; ?>
    </section>

    <!-- SECTION 2: QUẢN LÝ ĐƠN HÀNG -->
    <section>
        <h2>Quản lý đơn hàng toàn hệ thống</h2>
        <div class="bang-vo">
            <table>
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Nhân viên lập</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
                        <th>Số món</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái hiện tại</th>
                        <th>Cập nhật trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($danhSachDonHang as $don): ?>
                        <tr>
                            <td>#<?= e($don['id']) ?></td>
                            <td><?= e($don['ten_nhan_vien'] ?? 'N/A') ?></td>
                            <td>
                                <?= e($don['ten_khach_hang'] ?: 'Khách vãng lai') ?>
                                <?php if (!empty($don['sdt_khach_hang'])): ?>
                                    <br><small>(<?= e($don['sdt_khach_hang']) ?>)</small>
                                <?php endif; ?>
                            </td>
                            <td><?= e($don['ngay_dat']) ?></td>
                            <td><?= e($don['so_mon']) ?></td>
                            <td><?= e(vnd($don['tong_tien'])) ?></td>
                            <td><?= e(\App\Data\KhoDonHang::TRANG_THAI[$don['trang_thai']] ?? $don['trang_thai']) ?></td>
                            <td>
                                <form action="quan-tri.php" method="post" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="cap_nhat_trang_thai" value="1">
                                    <input type="hidden" name="don_id" value="<?= e($don['id']) ?>">
                                    <select name="trang_thai">
                                        <?php foreach (\App\Data\KhoDonHang::TRANG_THAI as $ma => $ten): ?>
                                            <option value="<?= e($ma) ?>" <?= $don['trang_thai'] === $ma ? 'selected' : '' ?>>
                                                <?= e($ten) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit">Lưu</button>
                                </form>
                                | <a href="chi-tiet-don.php?id=<?= e($don['id']) ?>">Xem</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- SECTION 3: PHIẾU LIÊN HỆ -->
    <section>
        <h2>Phiếu báo cáo & Liên hệ đã nhận</h2>
        <div class="bang-vo">
            <table>
                <thead>
                    <tr>
                        <th>Mã phiếu</th>
                        <th>Thời gian</th>
                        <th>Mã NV</th>
                        <th>Email</th>
                        <th>Ưu tiên</th>
                        <th>Loại</th>
                        <th>Nội dung</th>
                        <th>Ảnh</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($danhSachLienHe as $phieu): ?>
                        <tr>
                            <td><?= e($phieu['ma_phieu']) ?></td>
                            <td><?= e($phieu['thoi_gian_tao']) ?></td>
                            <td><?= e($phieu['manhanvien']) ?></td>
                            <td><?= e($phieu['email']) ?></td>
                            <td><?= e($phieu['mucdouutien']) ?>/5</td>
                            <td><?= e($phieu['loaiyeucau']) ?></td>
                            <td><?= e($phieu['noidung']) ?></td>
                            <td>
                                <?php if (!empty($phieu['anh_dinh_kem'])): ?>
                                    <a href="uploads/<?= e($phieu['anh_dinh_kem']) ?>" target="_blank">Xem ảnh</a>
                                <?php else: ?>
                                    Không
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>