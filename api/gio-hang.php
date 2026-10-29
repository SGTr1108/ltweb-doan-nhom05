<?php

declare(strict_types=1);

require_once __DIR__ . '/../inc/config.php';

set_exception_handler(static function (Throwable $loi): void {
    error_log("API GioHang Error: " . $loi->getMessage());
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode(['loi' => 'Lỗi máy chủ'], JSON_UNESCAPED_UNICODE);
});

header('Content-Type: application/json; charset=utf-8');

$phuongThuc = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$khoSanPham = new \App\Data\KhoSanPham(db());
$gioHang    = new \App\Services\GioHang($khoSanPham);

if ($phuongThuc === 'GET') {
    echo json_encode([
        'so_luong'  => $gioHang->demSoLuong(),
        'tong_tien' => $gioHang->tinhTongTien(),
        'danh_sach' => $gioHang->layDanhSach(),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($phuongThuc === 'POST') {
    $rawInput = file_get_contents('php://input');
    $data     = json_decode($rawInput, true);

    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(['loi' => 'Dữ liệu JSON gửi lên không hợp lệ.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $id      = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $soLuong = filter_var($data['so_luong'] ?? 1, FILTER_VALIDATE_INT);

    if ($id === false) {
        http_response_code(404);
        echo json_encode(['loi' => 'Mã sản phẩm không tồn tại hoặc không hợp lệ.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($soLuong === false || $soLuong < 1) {
        http_response_code(422);
        echo json_encode(['loi' => 'Số lượng phải lớn hơn hoặc bằng 1.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $gioHang->them((string) $id, $soLuong);
        echo json_encode([
            'thong_bao'     => 'Đã thêm sản phẩm vào giỏ hàng.',
            'tong_so_luong' => $gioHang->demSoLuong(),
            'tong_tien'     => $gioHang->tinhTongTien(),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (\InvalidArgumentException $e) {
        http_response_code(404);
        echo json_encode(['loi' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (\RuntimeException $e) {
        http_response_code(422);
        echo json_encode(['loi' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

http_response_code(405);
header('Allow: GET, POST');
echo json_encode(['loi' => 'Phương thức không được hỗ trợ.'], JSON_UNESCAPED_UNICODE);
