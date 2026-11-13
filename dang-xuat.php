<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

// Chỉ nhận POST (mã CSRF đã được kiểm tra tập trung trong inc/config.php).
yeuCauPost();

// Xóa thông tin đăng nhập và hủy phiên làm việc
unset($_SESSION['uid'], $_SESSION['nguoi_dung'], $_SESSION['csrf']);
session_destroy();

header('Location: index.php', true, 303);
exit;
