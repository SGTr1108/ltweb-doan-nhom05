<?php

declare(strict_types=1);

date_default_timezone_set('Asia/Ho_Chi_Minh');

// Nạp tự động các lớp App\... bằng Composer.
require_once __DIR__ . '/../vendor/autoload.php';

// Nạp các hàm dùng chung như e() và vnd().
require_once __DIR__ . '/ham.php';

// Kết nối CSDL: hàm db() và maLoiSql() (PDO, MySQL).
require_once __DIR__ . '/db.php';

// Nạp hệ thống xác thực & phân quyền (chứa hàm nguoiDung, yeuCauDangNhap...)
require_once __DIR__ . '/xac-thuc.php';

// Mã CSRF: csrfToken(), csrfField(), csrfHopLe().
require_once __DIR__ . '/csrf.php';

// Môi trường phát triển; khi triển khai thật đổi thành 'prod'.
const MOI_TRUONG = 'dev';

// ----- ĐOẠN BỔ SUNG: TỰ ĐỘNG TẠO THƯ MỤC NẾU CHƯA CÓ -----
$log_dir = dirname(__DIR__) . '/logs';
$storage_dir = dirname(__DIR__) . '/storage';

if (!is_dir($log_dir)) {
    mkdir($log_dir, 0777, true);
}
if (!is_dir($storage_dir)) {
    mkdir($storage_dir, 0777, true);
}
// ---------------------------------------------------------

error_reporting(E_ALL);

// Không hiển thị lỗi kỹ thuật trực tiếp ra trình duyệt.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__) . '/logs/php-error.log');

// Cookie phiên an toàn — đặt TRƯỚC session_start().
$dangHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

ini_set('session.use_strict_mode', '1');   // bỏ qua mã phiên lạ (chống cố định phiên)
ini_set('session.use_only_cookies', '1');  // không nhận mã phiên trên URL
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => $dangHttps,   // chỉ gửi qua HTTPS khi đang chạy HTTPS
    'httponly' => true,         // JavaScript không đọc được PHPSESSID
    'samesite' => 'Lax',        // hạn chế gửi cookie từ site khác
]);

// Khởi tạo session nếu chưa có.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 30 phút không hoạt động: bỏ phiên cũ, cấp mã mới → phải đăng nhập lại.
if (time() - ($_SESSION['hoat_dong'] ?? time()) > 30 * 60) {
    $_SESSION = [];
    session_regenerate_id(true);
}
$_SESSION['hoat_dong'] = time();

// Mọi request POST (form hoặc fetch) phải mang mã CSRF đúng.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !csrfHopLe()) {
    ghiLogBaoMat('csrf_sai', ['trang' => $_SERVER['REQUEST_URI'] ?? '']);
    $loiCsrf = 'Trang đã hết hạn, hãy tải lại trang rồi gửi lại';
    if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'json')) {
        traJson(['loi' => $loiCsrf], 403);
    }
    http_response_code(403);
    exit($loiCsrf);
}


// Xử lý các ngoại lệ chưa được bắt.
set_exception_handler(static function (Throwable $loi): void {
    // Ghi chi tiết lỗi vào log để lập trình viên kiểm tra.
    error_log(sprintf(
        "[%s] %s in %s:%d\n%s",
        date('c'),
        $loi->getMessage(),
        $loi->getFile(),
        $loi->getLine(),
        $loi->getTraceAsString()
    ));

    // Trả về mã lỗi 500 và hiển thị trang lỗi thân thiện.
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }

    require dirname(__DIR__) . '/500.php';
});
