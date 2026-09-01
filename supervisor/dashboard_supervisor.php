<?php
require_once __DIR__ . "/../shared/init.php";
require_once __DIR__ . "/../shared/auth.php";
require_once __DIR__ . "/../shared/TaskRepository.php";

require_role("supervisor", "admin");

// Supervisor memantau seluruh task; admin melihat ringkasan yang sama.
$counts = task_status_counts($conn, null);
$tasks  = array_slice(task_list($conn, null), 0, 5);

// Jumlah user aktif untuk ringkasan tim.
$res = $conn->query("SELECT COUNT(*) AS c FROM users");
$totalUsers = (int) ($res->fetch_assoc()["c"] ?? 0);

page_start("Dashboard Supervisor", "Ringkasan pekerjaan seluruh tim.", "dashboard");
?>

<section class="stat-grid">
    <div class="stat-card">
        <div><span>Total Task</span><strong><?= $counts["total"] ?></strong></div>
    </div>
    <div class="stat-card accent-primary">
        <div><span>Belum Dikerjakan</span><strong><?= $counts["pending"] ?></strong></div>
    </div>
    <div class="stat-card accent-warning">
        <div><span>Sedang Dikerjakan</span><strong><?= $counts["in_progress"] ?></strong></div>
    </div>
    <div class="stat-card accent-success">
        <div><span>Selesai</span><strong><?= $counts["completed"] ?></strong></div>
    </div>
</section>

<section class="dashboard-panel">
    <div class="panel-header">
        <h2>Pekerjaan Terbaru</h2>
        <a href="monitoring_task.php">Lihat Semua</a>
    </div>

    <?php if (empty($tasks)): ?>
        <div class="empty-task">Belum ada pekerjaan tercatat.</div>
    <?php else: ?>
        <table class="report-table supervisor-task-table">
            <thead>
                <tr><th>Pekerjaan</th><th>User</th><th>Status</th><th>Deadline</th></tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $t): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($t["title"]) ?></strong></td>
                        <td><?= htmlspecialchars($t["nama_user"] ?? "-") ?></td>
                        <td><span class="badge status-<?= htmlspecialchars($t["status"]) ?>"><?= htmlspecialchars(TASK_STATUS_LABELS[$t["status"]]) ?></span></td>
                        <td><?= htmlspecialchars($t["deadline"]) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php page_end(); ?>
