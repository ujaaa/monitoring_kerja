<?php

require_once "koneksi.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama = trim($_POST["nama"]);
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $konfirmasi = $_POST["konfirmasi"];

    if ($password !== $konfirmasi) {

        $error = "Password dan konfirmasi password tidak sama.";

    } else {

        $cek = $conn->prepare(
            "SELECT id FROM users
             WHERE username = ? OR email = ?
             LIMIT 1"
        );

        $cek->bind_param(
            "ss",
            $username,
            $email
        );

        $cek->execute();

        $hasil = $cek->get_result();

        if ($hasil->num_rows > 0) {

            $error = "Username atau email sudah digunakan.";

        } else {

            $password_hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $role = "user";

            $stmt = $conn->prepare(
                "INSERT INTO users
                (nama, username, email, password, role)
                VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssss",
                $nama,
                $username,
                $email,
                $password_hash,
                $role
            );

            if ($stmt->execute()) {

                header("Location: index.php?registered=1");
                exit;

            } else {

                $error = "Pendaftaran gagal.";
            }
        }
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

    <title>Daftar - Monitoring Kerja</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="login-page">

    <div class="login-card">

        <div class="logo">
            MONITORING KERJA
        </div>

        <h1>Buat Akun</h1>

        <p class="subtitle">
            Daftarkan akun baru
        </p>

        <?php if ($error): ?>

            <div class="alert error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="form-group">

                <label>Nama Lengkap</label>

                <input
                    type="text"
                    name="nama"
                    placeholder="Nama lengkap"
                    required
                >

            </div>

            <div class="form-group">

                <label>Username</label>

                <input
                    type="text"
                    name="username"
                    placeholder="Username"
                    required
                >

            </div>

            <div class="form-group">

                <label>Email</label>

                <input
                    type="email"
                    name="email"
                    placeholder="Email"
                    required
                >

            </div>

            <div class="form-group">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    required
                >

            </div>

            <div class="form-group">

                <label>Konfirmasi Password</label>

                <input
                    type="password"
                    name="konfirmasi"
                    placeholder="Ulangi password"
                    required
                >

            </div>

            <button type="submit" class="btn-login">
                Daftar
            </button>

        </form>

        <p class="register-link">

            Sudah punya akun?

            <a href="index.php">
                Login
            </a>

        </p>

    </div>

</div>

</body>

</html>