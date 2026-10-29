<?php

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

yeuCauPost();
xacNhanCsrf();

unset($_SESSION['uid']);
session_destroy();

header('Location: index.php', true, 303);
exit;
