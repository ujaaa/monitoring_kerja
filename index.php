<?php
session_start();
require_once __DIR__ . "/shared/auth.php";

if (isset($_SESSION["role"])) {
    // Routing role terpusat di dashboard_path().
    header("Location: " . dashboard_path($_SESSION["role"], "/"));
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#FAFAF7">
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <title>Login — Monitoring Kerja</title>
    <link rel="stylesheet" href="assets/css/style.css?v=3">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <div class="logo">MONITORING KERJA</div>
        <h1>Selamat Datang</h1>
        <p class="subtitle">Masuk untuk melanjutkan pekerjaan Anda</p>

        <?php if (isset($_GET["error"])): ?>
            <div class="alert alert-error">Username atau password salah.</div>
        <?php endif; ?>

        <?php if (isset($_GET["registered"])): ?>
            <div class="alert alert-success">Akun berhasil dibuat. Silakan login.</div>
        <?php endif; ?>

        <form action="auth/login.php" method="POST">
            <div class="form-group">
                <label for="login-username">Username</label>
                <input type="text" id="login-username" name="username" autocomplete="username" spellcheck="false" placeholder="cth. udin…" required>
            </div>
            <div class="form-group">
                <label for="login-password">Password</label>
                <input type="password" id="login-password" name="password" autocomplete="current-password" placeholder="Password Anda…" required>
            </div>
            <button type="submit" class="btn-login">Login</button>
        </form>

        <p class="register-link">Belum punya akun? <a href="auth/register.php">Daftar</a></p>
    </div>
</div>
</body>
</html>
