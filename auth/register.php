<?php
require_once __DIR__ . "/../shared/init.php";
require_once __DIR__ . "/../shared/auth.php";
require_once __DIR__ . "/../shared/flash.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check();

    $nama       = trim($_POST["nama"] ?? "");
    $username   = trim($_POST["username"] ?? "");
    $email      = trim($_POST["email"] ?? "");
    $password   = $_POST["password"] ?? "";
    $konfirmasi = $_POST["konfirmasi"] ?? "";

    if ($nama === "" || $username === "" || $email === "") {
        $error = "Semua kolom wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } elseif ($password !== $konfirmasi) {
        $error = "Password dan konfirmasi password tidak sama.";
    } else {
        $cek = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $cek->bind_param("ss", $username, $email);
        $cek->execute();
        if ($cek->get_result()->fetch_assoc()) {
            $error = "Username atau email sudah digunakan.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $role = "user";
            $stmt = $conn->prepare("INSERT INTO users (nama, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nama, $username, $email, $hash, $role);
            if ($stmt->execute()) {
                header("Location: ../index.php?registered=1");
                exit;
            }
            $error = "Pendaftaran gagal, coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — Monitoring Kerja</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=4">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <div class="logo">MONITORING KERJA</div>
        <h1>Buat Akun</h1>
        <p class="subtitle">Daftarkan akun baru</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="reg-nama">Nama Lengkap</label>
                <input type="text" id="reg-nama" name="nama" autocomplete="name" placeholder="cth. Budi Santoso…" required>
            </div>
            <div class="form-group">
                <label for="reg-username">Username</label>
                <input type="text" id="reg-username" name="username" autocomplete="username" spellcheck="false" placeholder="cth. budi…" required>
            </div>
            <div class="form-group">
                <label for="reg-email">Email</label>
                <input type="email" id="reg-email" name="email" autocomplete="email" spellcheck="false" placeholder="cth. budi@kantor.id…" required>
            </div>
            <div class="form-group">
                <label for="reg-password">Password</label>
                <input type="password" id="reg-password" name="password" autocomplete="new-password" placeholder="Minimal 6 karakter…" required minlength="6">
            </div>
            <div class="form-group">
                <label for="reg-konfirmasi">Konfirmasi Password</label>
                <input type="password" id="reg-konfirmasi" name="konfirmasi" autocomplete="new-password" placeholder="Ulangi password…" required minlength="6">
            </div>
            <button type="submit" class="btn-login">Daftar</button>
        </form>

        <p class="register-link">Sudah punya akun? <a href="../index.php">Login</a></p>
    </div>
</div>
</body>
</html>
