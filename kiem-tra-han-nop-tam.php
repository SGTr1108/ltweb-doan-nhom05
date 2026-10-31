<?php
require __DIR__ . '/inc/config.php';

$st = db()->prepare(
    'SELECT id, mssv, ten_bai, ngay_han, hoan_thanh
     FROM tv_han_nop
     WHERE mssv = ?
     ORDER BY id'
);

$st->execute(['3120224092']);

print_r($st->fetchAll(PDO::FETCH_ASSOC));
