<?php
require_once __DIR__ . "/../shared/init.php";
require_once __DIR__ . "/../shared/auth.php";
require_once __DIR__ . "/../shared/flash.php";
require_once __DIR__ . "/../shared/TaskRepository.php";

require_role("user", "admin");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: task.php");
    exit;
}

csrf_check();

$uid = (int) $_SESSION["id"];
$id  = (int) ($_POST["id"] ?? 0);

// Admin boleh menghapus task siapa pun; user hanya task miliknya.
$owner = $_SESSION["role"] === "admin" ? null : $uid;

if (task_delete($conn, $id, $owner)) {
    set_flash("success", "Pekerjaan dihapus.");
} else {
    set_flash("error", "Pekerjaan tidak ditemukan.");
}
header("Location: task.php");
exit;
