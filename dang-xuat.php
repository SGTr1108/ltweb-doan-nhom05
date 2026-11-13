<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

// Chỉ nhận POST (mã CSRF đã được kiểm tra tập trung trong inc/config.php).
yeuCauPost();

// Xóa dữ liệu phiên, cookie phiên và hủy phiên làm việc
dangXuat();

header('Location: index.php', true, 303);
exit;
