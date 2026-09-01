<?php
require_once __DIR__ . "/../shared/init.php";
require_once __DIR__ . "/../shared/auth.php";
require_once __DIR__ . "/../shared/TaskRepository.php";

require_role("supervisor", "admin");

/* Filter dinamis (semuanya lewat prepared statement). */
$filters = [
    "status"   => $_GET["status"] ?? "",
    "priority" => $_GET["priority"] ?? "",
    "q"        => trim($_GET["q"] ?? ""),
];

$tasks  = task_list($conn, null, $filters);
$counts = task_status_counts($conn, null);

page_start("Monitoring Pekerjaan", "Pantau progres pekerjaan seluruh tim.", "monitor");
?>

<section class="stat-grid">
    <div class="stat-card">
        <div><span>Total</span><strong><?= $counts["total"] ?></strong><small>Task tercatat</small></div>
        <div class="stat-icon">≡</div>
    </div>
    <div class="stat-card">
        <div><span>Selesai</span><strong><?= $counts["completed"] ?></strong><small>Selesai</small></div>
        <div class="stat-icon green">✓</div>
    </div>
    <div class="stat-card">
        <div><span>Berjalan</span><strong><?= $counts["in_progress"] ?></strong><small>Sedang dikerjakan</small></div>
        <div class="stat-icon cyan">↻</div>
    </div>
    <div class="stat-card">
        <div><span>Menunggu</span><strong><?= $counts["pending"] ?></strong><small>Belum mulai</small></div>
        <div class="stat-icon orange">○</div>
    </div>
</section>

<section class="dashboard-panel">
    <div class="panel-header">
        <h2>Daftar Pekerjaan</h2>
    </div>

    <form method="GET" class="monitor-filter">
        <input type="text" name="q" placeholder="Cari judul pekerjaan…" value="<?= htmlspecialchars($filters["q"]) ?>">
        <select name="status">
            <option value="">Semua status</option>
            <?php foreach (TASK_STATUS_LABELS as $val => $label): ?>
                <option value="<?= $val ?>" <?= $filters["status"] === $val ? "selected" : "" ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <select name="priority">
            <option value="">Semua prioritas</option>
            <?php foreach (TASK_PRIORITY_LABELS as $val => $label): ?>
                <option value="<?= $val ?>" <?= $filters["priority"] === $val ? "selected" : "" ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-simpan">Terapkan</button>
        <a href="monitoring_task.php" class="btn-batal">Reset</a>
    </form>

    <?php if (empty($tasks)): ?>
        <div class="empty-task">Tidak ada pekerjaan yang cocok dengan filter.</div>
    <?php else: ?>
        <table class="report-table supervisor-task-table">
            <thead>
                <tr>
                    <th>Pekerjaan</th>
                    <th>User</th>
                    <th>Prioritas</th>
                    <th>Status</th>
                    <th>Deadline</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $t): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($t["title"]) ?></strong>
                            <div class="task-desc"><?= htmlspecialchars(str_truncate($t["description"], 70)) ?></div>
                        </td>
                        <td><?= htmlspecialchars($t["nama_user"] ?? "-") ?></td>
                        <td><span class="badge priority-<?= htmlspecialchars($t["priority"]) ?>"><?= htmlspecialchars(TASK_PRIORITY_LABELS[$t["priority"]]) ?></span></td>
                        <td><span class="badge status-<?= htmlspecialchars($t["status"]) ?>"><?= htmlspecialchars(TASK_STATUS_LABELS[$t["status"]]) ?></span></td>
                        <td><?= htmlspecialchars($t["deadline"]) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php page_end(); ?>
