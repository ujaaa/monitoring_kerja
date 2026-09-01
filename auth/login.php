<?php
session_start();
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../shared/auth.php";

$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

if ($username === "" || $password === "") {
    header("Location: ../index.php?error=1");
    exit;
}

$stmt = $conn->prepare("SELECT id, nama, username, email, password, role, foto FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Pesan sama untuk "user tidak ada" dan "password salah".
if (!$user || !password_verify($password, $user["password"])) {
    header("Location: ../index.php?error=1");
    exit;
}

session_regenerate_id(true);

$_SESSION["id"]       = (int) $user["id"];
$_SESSION["nama"]     = $user["nama"];
$_SESSION["username"] = $user["username"];
$_SESSION["email"]    = $user["email"];
$_SESSION["role"]     = $user["role"];
$_SESSION["foto"]     = !empty($user["foto"]) ? $user["foto"] : null;

header("Location: " . dashboard_path($user["role"], "/"));
exit;
