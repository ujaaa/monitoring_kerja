<?php
session_start();
require_once "koneksi.php";

if (
    !isset($_SESSION["id"]) ||
    !isset($_SESSION["role"]) ||
    !in_array($_SESSION["role"], ["user", "admin"], true)
) {
    header("Location: index.php");
    exit;
}

$id = (int) ($_GET["id"] ?? 0);
$user_id = (int) $_SESSION["id"];

if ($id > 0) {
    $stmt = $conn->prepare("
        DELETE FROM tasks
        WHERE id = ? AND assigned_to = ?
    ");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
}

header("Location: task.php");
exit;
?>