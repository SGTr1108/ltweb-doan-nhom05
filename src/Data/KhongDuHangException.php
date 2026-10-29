<?php

declare(strict_types=1);

namespace App\Data;

use RuntimeException;

/** Ném khi đặt hàng mà một món không còn đủ tồn kho (hoặc không tồn tại). */
final class KhongDuHangException extends RuntimeException {}
