<?php
require __DIR__ . '/inc/config.php';

$pdo = db();

$duLieu = [
    [
        'ten_bai' => 'Lập trình mạng - BT04',
        'ngay_han' => '2026-10-15',
    ],
    [
        'ten_bai' => 'Văn nghệ TSV 2026',
        'ngay_han' => '2026-10-25',
    ],
];

$sql = 'INSERT INTO tv_han_nop (mssv, ten_bai, ngay_han, hoan_thanh)
        SELECT ?, ?, ?, 0
        WHERE NOT EXISTS (
            SELECT 1 FROM tv_han_nop
            WHERE mssv = ? AND ten_bai = ? AND ngay_han = ?
        )';

$st = $pdo->prepare($sql);
$pdo->beginTransaction();

try {
    $soBanGhi = 0;

    foreach ($duLieu as $bai) {
        $st->execute([
            '3120224092',
            $bai['ten_bai'],
            $bai['ngay_han'],
            '3120224092',
            $bai['ten_bai'],
            $bai['ngay_han'],
        ]);

        $soBanGhi += $st->rowCount();
    }

    $pdo->commit();

    echo "Chuyen du lieu thanh cong. So bai tap moi: $soBanGhi\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo "Chuyen du lieu that bai: " . $e->getMessage() . "\n";
}
