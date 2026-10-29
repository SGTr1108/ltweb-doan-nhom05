<?php

declare(strict_types=1);

http_response_code(403);

require_once __DIR__ . '/inc/config.php';

$tieuDeTrang  = '403 - Quyền truy cập bị từ chối | MiniPos';
$moTaTrang    = 'Bạn không có quyền truy cập vào tài nguyên này.';
$scriptsTrang = [];

require __DIR__ . '/inc/header.php';
?>

<main class="bao">
    <h1>403 - Quyền truy cập bị từ chối</h1>
    <p>Tài khoản của bạn không có đủ thẩm quyền để xem trang này.</p>
    <p>
        <a href="index.php">Quay lại trang chủ</a>
        |
        <a href="danh-sach.php">Xem danh sách sản phẩm</a>
    </p>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>