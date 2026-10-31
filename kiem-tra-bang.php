<?php
require_once __DIR__ . '/inc/db.php';

try {
    $ketQua = db()->query("SHOW TABLES");

    foreach ($ketQua as $dong) {
       echo reset($dong) . PHP_EOL;
    }
} catch (PDOException $loi) {
    echo "Loi ket noi: " . $loi->getMessage();
}