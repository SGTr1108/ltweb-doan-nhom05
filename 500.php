<?php
declare(strict_types=1);

http_response_code(500);

require_once __DIR__ . '/inc/config.php';

$tieuDeTrang = '500 - Lỗi máy chủ | MiniPos';
$moTaTrang = 'Hệ thống đang gặp sự cố.';
$scriptsTrang = [];

require __DIR__ . '/inc/header.php';
?>

<main class="bao">
    <h1>500 - Lỗi máy chủ</h1>
    <p>Hệ thống đang gặp sự cố khi xử lý yêu cầu.</p>
    <p>Vui lòng thử lại sau.</p>
    <p><a href="index.php">Quay lại trang chủ</a></p>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>