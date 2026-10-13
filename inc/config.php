<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Ho_Chi_Minh');

// Nạp tự động các lớp App\... bằng Composer.
require_once __DIR__ . '/../vendor/autoload.php';

// Nạp các hàm dùng chung như e() và vnd().
require_once __DIR__ . '/ham.php';

// Môi trường phát triển; khi triển khai thật đổi thành 'prod'.
const MOI_TRUONG = 'dev';

error_reporting(E_ALL);

ini_set('display_errors', MOI_TRUONG === 'dev' ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__) . '/logs/php-error.log');

// Khởi tạo session nếu chưa có.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Xử lý các ngoại lệ chưa được bắt và ghi chi tiết vào log.
set_exception_handler(static function (Throwable $loi): void {
    error_log(sprintf(
        "[%s] %s in %s:%d\n%s",
        date('c'),
        $loi->getMessage(),
        $loi->getFile(),
        $loi->getLine(),
        $loi->getTraceAsString()
    ));

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }

    if (MOI_TRUONG === 'dev') {
        echo '<h1>Lỗi máy chủ</h1><pre>'
            . htmlspecialchars(
                (string) $loi,
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            )
            . '</pre>';
    } else {
        // Hiển thị trang lỗi thân thiện khi chạy ở môi trường prod.
        require dirname(__DIR__) . '/500.php';
    }
});