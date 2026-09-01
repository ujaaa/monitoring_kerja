<?php
/**
 * shared/auth.php — guard akses & CSRF terpusat.
 * Satu sumber kebenaran untuk "siapa boleh masuk halaman ini".
 */

/**
 * Wajib login. Redirect ke halaman login jika belum.
 */
function require_login(): void
{
    if (empty($_SESSION["id"])) {
        header("Location: ../index.php");
        exit;
    }
}

/**
 * Wajib login + role termasuk daftar yang diizinkan.
 * require_role("user", "admin") — user ATAU admin boleh masuk.
 */
function require_role(string ...$roles): void
{
    require_login();

    if (!in_array($_SESSION["role"] ?? "", $roles, true)) {
        // Role tidak sesuai: kembalikan ke dashboard role-nya sendiri.
        header("Location: " . dashboard_path($_SESSION["role"] ?? "user"));
        exit;
    }
}

/**
 * Path dashboard sesuai role (dipakai juga di luar guard).
 * $prefix: "../" dari halaman subfolder, "" dari root.
 */
function dashboard_path(string $role, string $prefix = "../"): string
{
    return match ($role) {
        "admin"      => $prefix . "admin/dashboard_admin.php",
        "supervisor" => $prefix . "supervisor/dashboard_supervisor.php",
        default      => $prefix . "user/dashboard_user.php",
    };
}

function role_label(string $role): string
{
    return match ($role) {
        "admin"      => "Administrator",
        "supervisor" => "Supervisor",
        default      => "User",
    };
}

/* =========================================================
   CSRF — token per session, diverifikasi di setiap POST.
   ========================================================= */

function csrf_token(): string
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/**
 * Panggil di awal penanganan POST. Menghentikan request jika token salah.
 */
function csrf_check(): void
{
    $token = $_POST["csrf_token"] ?? "";
    if (!is_string($token) || !hash_equals($_SESSION["csrf_token"] ?? "", $token)) {
        http_response_code(419);
        exit("Sesi kedaluwarsa. Muat ulang halaman dan coba lagi.");
    }
}
