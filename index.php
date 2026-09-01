<?php

session_start();

if (isset($_SESSION['role'])) {

    if ($_SESSION['role'] === 'user') {
        header("Location: dashboard_user.php");
        exit;
    }

    if ($_SESSION['role'] === 'supervisor') {
        header("Location: dashboard_supervisor.php");
        exit;
    }

    if ($_SESSION['role'] === 'admin') {
        header("Location: dashboard_admin.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login - Monitoring Kerja</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="login-page">

    <div class="login-card">

        <div class="logo">
            MONITORING KERJA
        </div>

        <h1>Selamat Datang</h1>

        <p class="subtitle">
            Silakan masuk ke akun Anda
        </p>

        <?php if (isset($_GET['error'])): ?>

            <div class="alert error">
                Username atau password salah.
            </div>

        <?php endif; ?>


        <?php if (isset($_GET['registered'])): ?>

            <div class="alert success">
                Akun berhasil dibuat. Silakan login.
            </div>

        <?php endif; ?>


        <form action="login.php" method="POST">

            <div class="form-group">

                <label>Username</label>

                <input
                    type="text"
                    name="username"
                    placeholder="Masukkan username"
                    required
                >

            </div>


            <div class="form-group">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Masukkan password"
                    required
                >

            </div>


            <button
                type="submit"
                class="btn-login"
            >
                Login
            </button>

        </form>


        <p class="register-link">

            Belum punya akun?

            <a href="register.php">
                Daftar
            </a>

        </p>

    </div>

</div>

</body>

</html>