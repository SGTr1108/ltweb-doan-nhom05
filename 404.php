<?php
declare(strict_types=1);

http_response_code(404);

require_once __DIR__ . '/inc/config.php';

$tieuDeTrang = '404 - Không tìm thấy trang | MiniPos';
$moTaTrang = 'Trang bạn tìm kiếm không tồn tại.';
$scriptsTrang = [];

require __DIR__ . '/inc/header.php';
?>

<main class="bao">
    <h1>404 - Không tìm thấy trang</h1>
    <p>Đường dẫn bạn truy cập không tồn tại hoặc đã được thay đổi.</p>
    <p>
        <a href="index.php">Quay lại trang chủ</a>
        |
        <a href="danh-sach.php">Xem danh sách sản phẩm</a>
    </p>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>