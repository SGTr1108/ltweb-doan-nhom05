<?php

/**
 * inc/db.php — kết nối MySQL/MariaDB bằng PDO (một kết nối cho mỗi request).
 * Cách dùng: require inc/config.php (đã nạp tệp này), rồi gọi db().
 * Cách thử: php -r "require 'inc/db.php'; var_dump(db()->query('SELECT 1')->fetchColumn());"
 * Có thể đổi thông số bằng biến môi trường DB_HOST, DB_NAME, DB_USER, DB_PASS.
 */

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $host = getenv('DB_HOST') ?: '127.0.0.1;port=3307';
        $ten  = getenv('DB_NAME') ?: 'ltweb_nhom05';
        $user = getenv('DB_USER') ?: 'sang';
        $pass = getenv('DB_PASS') !== false ? (string) getenv('DB_PASS') : '1';

        $pdo = new PDO(
            "mysql:host=$host;dbname=$ten;charset=utf8mb4",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    }

    return $pdo;
}

/**
 * Mã lỗi của MySQL/MariaDB trong một PDOException
 * (1062 trùng khóa UNIQUE, 1451 xóa dòng đang được tham chiếu,
 * 1452 khóa ngoại không tồn tại…). Không có thì trả 0.
 */
function maLoiSql(PDOException $loi): int
{
    return (int) ($loi->errorInfo[1] ?? 0);
}
