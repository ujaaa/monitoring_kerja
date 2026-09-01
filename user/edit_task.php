<?php
require_once __DIR__ . "/../shared/init.php";
require_once __DIR__ . "/../shared/auth.php";
require_once __DIR__ . "/../shared/flash.php";
require_once __DIR__ . "/../shared/TaskRepository.php";

require_role("user", "admin");

$uid = (int) $_SESSION["id"];
$task = task_find($conn, (int) ($_GET["id"] ?? $_POST["id"] ?? 0), $uid);

if (!$task) {
    set_flash("error", "Pekerjaan tidak ditemukan.");
    header("Location: task.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check();
    [$ok, $err, $data] = task_validate($_POST);
    if (!$ok) {
        set_flash("error", $err);
    } else {
        $patched = task_update($conn, (int) $task["id"], $data, $uid);
        if ($patched || $task["status"] === $data["status"]) {
            // Update no-op (nilai sama) tetap dihitung sukses.
            set_flash("success", "Perubahan pekerjaan tersimpan.");
        } else {
            set_flash("error", "Gagal menyimpan perubahan (pekerjaan hilang atau bukan milik Anda).");
        }
    }
    header("Location: task.php");
    exit;
}

page_start("Edit Pekerjaan", "Perbarui informasi pekerjaan.", "task");
?>

<section class="task-card">
    <form method="POST" class="form-grid-modal">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $task["id"] ?>">
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
                <?php foreach (TASK_PRIORITY_LABELS as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $task["priority"] === $val ? "selected" : "" ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <?php foreach (TASK_STATUS_LABELS as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $task["status"] === $val ? "selected" : "" ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="task-form-buttons" style="grid-column:1/-1">
            <a href="task.php" class="btn-task-cancel">Batal</a>
            <button type="submit" class="btn-task-save">Simpan Perubahan</button>
        </div>
    </form>
</section>

<?php page_end(); ?>
