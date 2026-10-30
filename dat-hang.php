<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

// Yêu cầu nhân viên / admin đăng nhập
$user = yeuCauDangNhap();

$khoSanPham  = new \App\Data\KhoSanPham(db());
$gioHang     = new \App\Services\GioHang($khoSanPham);
$mangGioHang = $gioHang->layMang();

if ($mangGioHang === []) {
    $_SESSION['flash_gio_hang'] = 'Giỏ hàng của bạn đang trống.';
    header('Location: gio-hang.php', true, 303);
    exit;
}

$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
$loiDatHang = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    xacNhanCsrf();

    $tenKhach     = trim((string) ($_POST['ten_khach'] ?? ''));
    $sdtKhach     = trim((string) ($_POST['sdt_khach'] ?? ''));
    $tienKhachDua = (int) ($_POST['tien_khach_dua'] ?? 0);
    $ghiChu       = trim((string) ($_POST['ghi_chu'] ?? ''));
    $tongTien     = $gioHang->tinhTongTien();

    if ($tienKhachDua < $tongTien) {
        $loiDatHang = 'Số tiền khách đưa chưa đủ để thanh toán hóa đơn.';
    } else {
        try {
            $khoDonHang = new \App\Data\KhoDonHang(db());
            $donId = $khoDonHang->tao(
                (int) $user['id'],
                $mangGioHang,
                $tenKhach,
                $sdtKhach,
                $tienKhachDua,
                $ghiChu
            );

            $gioHang->xoaTatCa();
            $_SESSION['flash_don_hang'] = "Thanh toán thành công! Mã hóa đơn: #{$donId}";
            header("Location: chi-tiet-don.php?id={$donId}", true, 303);
            exit;
        } catch (\App\Data\KhongDuHangException $e) {
            $loiDatHang = $e->getMessage();
        } catch (\Throwable $e) {
            error_log('Lỗi tạo hóa đơn: ' . $e->getMessage());
            $loiDatHang = 'Không thể xử lý hóa đơn lúc này. Vui lòng thử lại sau.';
        }
    }
}

$danhSachMatHang = $gioHang->layDanhSach();
$tongTien        = $gioHang->tinhTongTien();

$tieuDeTrang  = 'Xác nhận hóa đơn | MiniPos';
$moTaTrang    = 'Thanh toán và lập hóa đơn bán hàng.';
$scriptsTrang = [];

require __DIR__ . '/inc/header.php';
?>

<main class="bao">
    <h1>Xác nhận & Lập hóa đơn bán hàng</h1>

    <?php if ($loiDatHang !== ''): ?>
        <p class="thong-bao-loi" role="alert"><?= e($loiDatHang) ?></p>
    <?php endif; ?>

    <p>Nhân viên thực hiện: <strong><?= e($user['ho_ten']) ?></strong></p>

    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
        <!-- Danh sách sản phẩm -->
        <div style="flex: 2; min-width: 300px;">
            <h2>Mặt hàng đã chọn</h2>
            <table class="bang-gio-hang">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Đơn giá</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($danhSachMatHang as $matHang): ?>
                        <tr>
                            <td><?= e($matHang['sanPham']->getTen()) ?></td>
                            <td><?= e(vnd($matHang['sanPham']->getGia())) ?></td>
                            <td><?= e($matHang['soLuong']) ?></td>
                            <td><?= e(vnd($matHang['thanhTien'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p style="font-size: 1.2rem; margin-top: 10px;"><strong>Tổng tiền: <?= e(vnd($tongTien)) ?></strong></p>
        </div>

        <!-- Form nhập tiền khách đưa -->
        <div style="flex: 1; min-width: 280px; background: #f9f9f9; padding: 15px; border-radius: 8px;">
            <h2>Thông tin thanh toán</h2>
            <form action="dat-hang.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                <p>
                    <label for="ten_khach">Tên khách hàng (tùy chọn):</label><br>
                    <input type="text" id="ten_khach" name="ten_khach" placeholder="Khách vãng lai" style="width: 100%; padding: 6px;">
                </p>

                <p>
                    <label for="sdt_khach">Số điện thoại (tùy chọn):</label><br>
                    <input type="text" id="sdt_khach" name="sdt_khach" placeholder="0901234567" style="width: 100%; padding: 6px;">
                </p>

                <p>
                    <label for="tien_khach_dua"><strong>Tiền khách đưa (VNĐ) *:</strong></label><br>
                    <input type="number" id="tien_khach_dua" name="tien_khach_dua" min="<?= $tongTien ?>" step="1000" value="<?= $tongTien ?>" required style="width: 100%; padding: 8px; font-weight: bold;">
                </p>

                <p>
                    <label for="ghi_chu">Ghi chú hóa đơn:</label><br>
                    <textarea id="ghi_chu" name="ghi_chu" rows="2" style="width: 100%;"></textarea>
                </p>

                <button type="submit" style="width: 100%; padding: 10px; background: #28a745; color: white; border: none; font-size: 1.1rem; cursor: pointer; border-radius: 4px;">
                    Xác nhận thanh toán & In hóa đơn
                </button>
                <p style="margin-top: 10px; text-align: center;"><a href="gio-hang.php">Quay lại giỏ hàng</a></p>
            </form>
        </div>
    </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>