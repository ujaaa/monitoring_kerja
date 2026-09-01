<?php
require_once __DIR__ . "/../shared/init.php";
require_once __DIR__ . "/../shared/auth.php";
require_once __DIR__ . "/../shared/flash.php";

require_role("admin");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: users.php");
    exit;
}

csrf_check();

$id   = (int) ($_POST["id"] ?? 0);
$role = $_POST["role"] ?? "";

if (!in_array($role, ["user", "supervisor", "admin"], true)) {
    set_flash("error", "Role tidak valid.");
} elseif ($id === (int) $_SESSION["id"] && $role !== $_SESSION["role"]) {
    set_flash("error", "Anda tidak bisa mengubah role akun sendiri.");
} else {
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $id);
    $ok = $stmt->execute() && $stmt->affected_rows > 0;
    set_flash($ok ? "success" : "error", $ok ? "Role user diperbarui." : "User tidak ditemukan.");
}

header("Location: users.php");
exit;
