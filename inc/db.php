<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
<<<<<<< HEAD
        $host = getenv('DB_HOST') ?: '127.0.0.1;port=3306';
        $ten  = getenv('DB_NAME') ?: 'ltweb_nhom05';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') !== false ? (string) getenv('DB_PASS') : '';
=======
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $ten  = getenv('DB_NAME') ?: 'ltweb_nhom05';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') !== false
            ? (string) getenv('DB_PASS')
            : '';
>>>>>>> 9429bc0 (Hoàn thành phần trang ca nhan cua Luy)

        $pdo = new PDO(
            "mysql:host=$host;port=$port;dbname=$ten;charset=utf8mb4",
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
 * Mã lỗi MySQL/MariaDB:
 * 1062: trùng khóa UNIQUE
 * 1451: xóa dòng đang được tham chiếu
 * 1452: khóa ngoại không tồn tại
 */
function maLoiSql(PDOException $loi): int
{
    return (int) ($loi->errorInfo[1] ?? 0);
}
