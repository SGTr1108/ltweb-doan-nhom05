<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

yeuCauQuyen('quan_tri');
yeuCauPost();
xacNhanCsrf();

$spId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if ($spId === false || $spId < 1) {
    $_SESSION['flash_quan_tri_loi'] = 'Mã sản phẩm không hợp lệ.';
    header('Location: quan-tri.php', true, 303);
    exit;
}

$khoSanPham = new \App\Data\KhoSanPham(db());

try {
    if ($khoSanPham->xoa($spId)) {
        $_SESSION['flash_quan_tri'] = "Đã xóa sản phẩm #{$spId} thành công.";
    } else {
        $_SESSION['flash_quan_tri_loi'] = "Sản phẩm #{$spId} không tồn tại.";
    }
} catch (\PDOException $e) {
    // Bắt lỗi 1451: Khóa ngoại vi phạm khi sản phẩm đã thuộc về một đơn hàng
    if (maLoiSql($e) === 1451) {
        $_SESSION['flash_quan_tri_loi'] = "Không thể xóa sản phẩm #{$spId} vì sản phẩm này đã có trong đơn hàng của khách.";
    } else {
        throw $e;
    }
}

header('Location: quan-tri.php', true, 303);
exit;
