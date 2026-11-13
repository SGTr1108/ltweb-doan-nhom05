<?php

declare(strict_types=1);

// Mã CSRF: mỗi phiên một mã ngẫu nhiên; form và fetch gửi lại đúng mã đó.

function csrfToken(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

// In ra trong MỌI form method="post".
function csrfField(): string
{
    return '<input type="hidden" name="csrf" value="' . csrfToken() . '">';
}

// Mã gửi lên (ô csrf hoặc header X-CSRF-Token) có khớp mã trong phiên không?
function csrfHopLe(): bool
{
    $gui = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return is_string($gui) && hash_equals(csrfToken(), $gui);
}
