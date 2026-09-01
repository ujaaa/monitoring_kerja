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

$stmt = $conn->prepare("
    SELECT id, title, description, priority, status, deadline
    FROM tasks
    WHERE id = ? AND assigned_to = ?
");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Task tidak ditemukan.");
}

$task = $result->fetch_assoc();
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $deadline = trim($_POST["deadline"] ?? "");
    $priority = $_POST["priority"] ?? "medium";
    $status = $_POST["status"] ?? "pending";

    if ($title === "" || $description === "" || $deadline === "") {
        $error = "Semua data harus diisi.";
    } else {
        $update = $conn->prepare("
            UPDATE tasks
            SET title = ?, description = ?, priority = ?, status = ?, deadline = ?
            WHERE id = ? AND assigned_to = ?
        ");
        $update->bind_param(
            "sssssii",
            $title,
            $description,
            $priority,
            $status,
            $deadline,
            $id,
            $user_id
        );

        if ($update->execute()) {
            header("Location: task.php");
            exit;
        }

        $error = "Gagal menyimpan perubahan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pekerjaan</title>
    <link rel="stylesheet" href="task.css">
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-logo">M</div>
            <div class="brand-text">MONITORING<br>KERJA</div>
        </div>
        <nav class="menu">
            <a href="dashboard_user.php" class="menu-item"><span class="icon">▣</span>Dashboard</a>
            <a href="task.php" class="menu-item active"><span class="icon">+</span>Data Pekerjaan</a>
            <a href="logout.php" class="menu-item"><span class="icon">↪</span>Logout</a>
        </nav>
    </aside>

    <main class="content">
        <header class="topbar">
            <div>
                <h1>Edit Pekerjaan</h1>
                <p>Perbarui informasi pekerjaan.</p>
            </div>
        </header>

        <section class="task-card">
            <?php if ($error): ?>
                <div class="task-alert error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="form-grid-modal">
                <div class="form-group">
                    <label>Nama Pekerjaan</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($task["title"]) ?>" required>
                </div>

                <div class="form-group">
                    <label>Deadline</label>
                    <input type="date" name="deadline" value="<?= htmlspecialchars($task["deadline"]) ?>" required>
                </div>

                <div class="form-group full">
                    <label>Deskripsi</label>
                    <textarea name="description" required><?= htmlspecialchars($task["description"]) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Prioritas</label>
                    <select name="priority">
                        <option value="low" <?= $task["priority"] === "low" ? "selected" : "" ?>>Rendah</option>
                        <option value="medium" <?= $task["priority"] === "medium" ? "selected" : "" ?>>Sedang</option>
                        <option value="high" <?= $task["priority"] === "high" ? "selected" : "" ?>>Tinggi</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="pending" <?= $task["status"] === "pending" ? "selected" : "" ?>>Belum Dikerjakan</option>
                        <option value="in_progress" <?= $task["status"] === "in_progress" ? "selected" : "" ?>>Sedang Dikerjakan</option>
                        <option value="completed" <?= $task["status"] === "completed" ? "selected" : "" ?>>Selesai</option>
                    </select>
                </div>

                <div class="task-form-buttons" style="grid-column:1/-1">
                    <a href="task.php" class="btn-task-cancel">Batal</a>
                    <button type="submit" class="btn-task-save">Simpan Perubahan</button>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>
