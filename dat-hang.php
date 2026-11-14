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

$loiDatHang = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $tenKhach     = trim(chuoi($_POST['ten_khach'] ?? ''));
    $sdtKhach     = trim(chuoi($_POST['sdt_khach'] ?? ''));
    $tienKhachDua = filter_var(
        $_POST['tien_khach_dua'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 0]]
    );
    $ghiChu       = trim(chuoi($_POST['ghi_chu'] ?? ''));
    $tongTien     = $gioHang->tinhTongTien();

    if ($tienKhachDua === false) {
        $loiDatHang = 'Số tiền khách đưa phải là số nguyên không âm.';
    } elseif ($tienKhachDua < $tongTien) {
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

    <div class="dat-hang-bo-cuc">
        <!-- Danh sách sản phẩm -->
        <div class="dat-hang-danh-sach">
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
            <p class="dat-hang-tong-tien"><strong>Tổng tiền: <?= e(vnd($tongTien)) ?></strong></p>
        </div>

        <!-- Form nhập tiền khách đưa -->
        <div class="dat-hang-thanh-toan">
            <h2>Thông tin thanh toán</h2>
            <form action="dat-hang.php" method="post">
                <?= csrfField() ?>

                <p>
                    <label for="ten_khach">Tên khách hàng (tùy chọn):</label><br>
                    <input type="text" id="ten_khach" name="ten_khach" placeholder="Khách vãng lai" class="dat-hang-o-nhap">
                </p>

                <p>
                    <label for="sdt_khach">Số điện thoại (tùy chọn):</label><br>
                    <input type="text" id="sdt_khach" name="sdt_khach" placeholder="0901234567" class="dat-hang-o-nhap">
                </p>

                <p>
                    <label for="tien_khach_dua"><strong>Tiền khách đưa (VNĐ) *:</strong></label><br>
                    <input type="number" id="tien_khach_dua" name="tien_khach_dua" min="<?= $tongTien ?>" step="1000" value="<?= $tongTien ?>" required class="dat-hang-o-nhap dat-hang-o-tien">
                </p>

                <p>
                    <label for="ghi_chu">Ghi chú hóa đơn:</label><br>
                    <textarea id="ghi_chu" name="ghi_chu" rows="2" class="dat-hang-o-rong"></textarea>
                </p>

                <button type="submit" class="dat-hang-nut-xac-nhan">
                    Xác nhận thanh toán & In hóa đơn
                </button>
                <p class="dat-hang-quay-lai"><a href="gio-hang.php">Quay lại giỏ hàng</a></p>
            </form>
        </div>
    </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>