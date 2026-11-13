<?php

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        // Giá trị do inc/config.php nạp từ .env và đã kiểm tra bằng required().
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'] ?? '3306';
        $ten  = $_ENV['DB_DATABASE'];
        $user = $_ENV['DB_USERNAME'];
        $pass = $_ENV['DB_PASSWORD'];

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