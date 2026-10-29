<?php

declare(strict_types=1);

require_once __DIR__ . '/../inc/config.php';

// Exception Handler riêng cho API trả JSON 500
set_exception_handler(static function (Throwable $loi): void {
    error_log("API SanPham Error: " . $loi->getMessage());
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode(['loi' => 'Lỗi máy chủ'], JSON_UNESCAPED_UNICODE);
});

header('Content-Type: application/json; charset=utf-8');

$phuongThuc = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($phuongThuc !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['loi' => 'Phương thức không được hỗ trợ.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$khoSanPham = new \App\Data\KhoSanPham(db());

// Trường hợp 1: Chi tiết 1 sản phẩm (?id=...)
if (isset($_GET['id'])) {
    $rawId = $_GET['id'];
    $id    = filter_var($rawId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($id === false) {
        http_response_code(400);
        echo json_encode(['loi' => 'Mã sản phẩm không hợp lệ.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $sanPham = $khoSanPham->timTheoId($id);
    if ($sanPham === null) {
        http_response_code(404);
        echo json_encode(['loi' => 'Không tìm thấy sản phẩm.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode($sanPham, JSON_UNESCAPED_UNICODE);
    exit;
}

// Trường hợp 2: Tìm kiếm danh sách (?q=...)
$q        = trim((string) ($_GET['q'] ?? ''));
$dm       = filter_var($_GET['dm'] ?? null, FILTER_VALIDATE_INT) ?: null;
$giaToiDa = filter_var($_GET['giaToiDa'] ?? null, FILTER_VALIDATE_INT) ?: null;

$loc      = ['q' => $q, 'dm' => $dm, 'giaToiDa' => $giaToiDa];
$danhSach = $khoSanPham->trang($loc, 'moi', 20, 0);

echo json_encode($danhSach, JSON_UNESCAPED_UNICODE);
