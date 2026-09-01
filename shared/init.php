<?php
/**
 * shared/init.php — bootstrap bersama: session + koneksi.
 * Setiap halaman aplikasi memulai dirinya dengan:
 *   require_once __DIR__ . "/../shared/init.php";
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . "/config/koneksi.php";

$conn->set_charset("utf8mb4");

/**
 * Truncate string tanpa bergantung ekstensi mbstring.
 */
function str_truncate(string $s, int $max): string
{
    if (function_exists("mb_strlen") && mb_strlen($s) <= $max) {
        return $s;
    }
    if (!function_exists("mb_strlen") && strlen($s) <= $max) {
        return $s;
    }
    return function_exists("mb_substr")
        ? mb_substr($s, 0, $max) . "…"
        : substr($s, 0, $max) . "…";
}


// Modul bersama yang dipakai semua halaman.
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/flash.php";
require_once __DIR__ . "/user_view.php";
require_once __DIR__ . "/layout.php";
