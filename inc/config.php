<?php

declare(strict_types=1);

date_default_timezone_set('Asia/Ho_Chi_Minh');

// Nạp tự động các lớp App\... bằng Composer.
require_once __DIR__ . '/../vendor/autoload.php';

// Cấu hình và bí mật nằm trong .env (không commit). Thiếu biến bắt buộc thì dừng ngay.
// safeLoad(): không có tệp .env vẫn dùng được biến môi trường thật (hosting), nhưng
// required() vẫn bắt buộc đủ biến. Chi tiết lỗi chỉ ghi log, không in ra trình duyệt.
$env = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
try {
    $env->safeLoad();
    $env->required(['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD']);
} catch (Dotenv\Exception\ExceptionInterface $loi) {
    error_log('[cau-hinh] ' . $loi->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Lỗi cấu hình máy chủ: thiếu biến trong .env (xem .env.example).');
}

// Gỡ lỗi chỉ bật khi APP_DEBUG=true; thiếu hoặc giá trị khác thì tắt.
define('DEBUG', ($_ENV['APP_DEBUG'] ?? '') === 'true');

// Nạp các hàm dùng chung như e() và vnd().
require_once __DIR__ . '/ham.php';

// Kết nối CSDL: hàm db() và maLoiSql() (PDO, MySQL).
require_once __DIR__ . '/db.php';

// Nạp hệ thống xác thực & phân quyền (chứa hàm nguoiDung, yeuCauDangNhap...)
require_once __DIR__ . '/xac-thuc.php';

// Mã CSRF: csrfToken(), csrfField(), csrfHopLe().
require_once __DIR__ . '/csrf.php';

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

// Chỉ hiển thị lỗi kỹ thuật ra trình duyệt khi APP_DEBUG=true (máy cá nhân).
ini_set('display_errors', DEBUG ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__) . '/logs/php-error.log');

$dangHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

// Header bảo mật gửi kèm mọi trang — đặt trước mọi output.
// CSP: chỉ cho tải tài nguyên từ chính website; không script, style nội tuyến.
// Nguồn ngoài duy nhất (connect-src) là hai API mà js/trang-chu.js (thời tiết
// Open-Meteo) và js/trang-lien-he.js (JSONPlaceholder) gọi bằng fetch.
header(
    "Content-Security-Policy: default-src 'self'; "
    . "script-src 'self'; "
    . "style-src 'self'; "
    . "font-src 'self'; "
    . "img-src 'self' data:; "
    . "connect-src 'self' https://api.open-meteo.com https://jsonplaceholder.typicode.com; "
    . "object-src 'none'; "
    . "base-uri 'self'; "
    . "form-action 'self'; "
    . "frame-ancestors 'none'"
);
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header_remove('X-Powered-By');
if ($dangHttps) {   // HSTS chỉ khi đang chạy HTTPS
    header('Strict-Transport-Security: max-age=31536000');
}

// Cookie phiên an toàn — đặt TRƯỚC session_start().

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