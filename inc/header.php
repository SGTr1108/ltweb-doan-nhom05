<?php
// inc/header.php — Khung đầu trang dùng chung cho MiniPos.
// Hiển thị menu, số lượng giỏ hàng và trạng thái đăng nhập.
// Dùng $goc để hỗ trợ trang nằm trong thư mục con.
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$goc = isset($goc) && is_string($goc) ? $goc : '';

$tieuDeTrang = isset($tieuDeTrang) && is_string($tieuDeTrang)
    ? $tieuDeTrang
    : (isset($tieuDe) && is_string($tieuDe)
        ? $tieuDe . ' | MiniPos'
        : 'MiniPos - Quản lý bán hàng thông minh');

$moTaTrang = isset($moTaTrang) && is_string($moTaTrang)
    ? $moTaTrang
    : 'MiniPos - Hệ thống quản lý bán hàng, tồn kho và đơn hàng chuyên nghiệp.';

$trangHienTai = basename(
    is_string($_SERVER['SCRIPT_NAME'] ?? null)
        ? $_SERVER['SCRIPT_NAME']
        : 'index.php',
    '.php'
);

// Đếm tổng số lượng sản phẩm trong giỏ từ session.
$soLuongGioHang = 0;

try {
    $khoSanPhamHeader = new \App\Data\KhoSanPham();
    $gioHangHeader = new \App\Services\GioHang($khoSanPhamHeader);
    $soLuongGioHang = $gioHangHeader->demSoLuong();
} catch (\Throwable $loi) {
    error_log('Không thể lấy số lượng giỏ hàng trên header: ' . $loi->getMessage());
}

// Trạng thái đăng nhập.
$nguoiDungHeader = $_SESSION['nguoi_dung'] ?? null;
$dangNhapHeader = is_array($nguoiDungHeader)
    && !empty($nguoiDungHeader['ten_dang_nhap']);

if ($dangNhapHeader && empty($_SESSION['csrf_dang_xuat'])) {
    $_SESSION['csrf_dang_xuat'] = bin2hex(random_bytes(32));
}



$menuHeader = [
    'index' => 'Trang chủ',
    'danh-sach' => 'Sản phẩm',
    'gio-hang' => 'Giỏ hàng',
    'lien-he' => 'Báo cáo & Liên hệ',
    'gioi-thieu' => 'Giới thiệu Nhóm',
];

// Kiểm tra tài khoản có quyền quản trị hay không.
$laQuanTriHeader = $dangNhapHeader
    && is_array($nguoiDungHeader)
    && ($nguoiDungHeader['vai_tro'] ?? '') === 'admin';

// Chỉ thêm mục Quản trị cho tài khoản admin.
if ($laQuanTriHeader) {
    $menuHeader['quan-tri'] = 'Quản trị';
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($moTaTrang) ?>">
    <title><?= e($tieuDeTrang) ?></title>
    <link rel="stylesheet" href="<?= e($goc) ?>css/style.css">
    <!-- CSS-TRANG-RIENG-OPTIONAL -->
    <?php if (isset($cssTrang) && is_string($cssTrang) && preg_match('/\A[a-zA-Z0-9_-]+\.css\z/', $cssTrang) === 1): ?>
        <link rel="stylesheet" href="<?= e($cssTrang) ?>">
    <?php endif; ?>
</head>
<body>

<header class="dau-trang">
    <strong>
        <a class="lien-ket-logo-minipos" href="<?= e($goc) ?>index.php">
            MiniPos
        </a>
    </strong>
    <span>| Quản lý bán hàng thông minh</span>

    <span class="thong-tin-yeu-thich-header">
        Yêu thích:
        <span data-so-luong-yeu-thich aria-live="polite">0</span>
    </span>

    <span class="thong-tin-nguoi-dung-header">
        <?php if ($dangNhapHeader): ?>
            Xin chào,
            <strong><?= e((string) ($nguoiDungHeader['ten_hien_thi'] ?? $nguoiDungHeader['ten_dang_nhap'])) ?></strong>
        <?php else: ?>
            Bạn đang xem với tư cách khách.
        <?php endif; ?>
    </span>
</header>

<nav aria-label="Menu chính">
    <button
        class="nut-menu"
        type="button"
        aria-expanded="false"
        aria-controls="menu-chinh">
        ☰ Menu
    </button>

    <ul id="menu-chinh">
        <?php foreach ($menuHeader as $tep => $nhan): ?>
            <?php
            $dangXemMuc = $trangHienTai === $tep
                || (
                    $tep === 'danh-sach'
                    && $trangHienTai === 'chi-tiet'
                );
            ?>
            <li>
                <a
                    href="<?= e($goc . $tep . '.php') ?>"
                    <?= $dangXemMuc ? 'aria-current="page"' : '' ?>>
                    <?= e($nhan) ?><?php if ($tep === 'gio-hang'): ?>
                        (<span data-so-luong-gio-hang aria-live="polite"><?= e($soLuongGioHang) ?></span>)
                    <?php endif; ?>
                </a>
            </li>
        <?php endforeach; ?>

        <li class="muc-tai-khoan-header">
            <?php if ($dangNhapHeader): ?>
                <form method="post" action="<?= e($goc) ?>dang-xuat.php" class="form-dang-xuat-header">
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= e((string) $_SESSION['csrf_dang_xuat']) ?>">
                    <button type="submit">Đăng xuất</button>
                </form>
            <?php else: ?>
                <a
                    href="<?= e($goc) ?>dang-nhap.php"
                    <?= $trangHienTai === 'dang-nhap' ? 'aria-current="page"' : '' ?>>
                    Đăng nhập
                </a>
            <?php endif; ?>
        </li>
    </ul>
</nav>