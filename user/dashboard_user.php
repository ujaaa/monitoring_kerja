<?php
require_once __DIR__ . "/../shared/init.php";
require_once __DIR__ . "/../shared/auth.php";
require_once __DIR__ . "/../shared/TaskRepository.php";

require_role("user", "admin");

$uid = (int) $_SESSION["id"];
$nama = $_SESSION["nama"] ?? "User";
$counts = task_status_counts($conn, $uid);

$recent = array_slice(task_list($conn, $uid), 0, 3);

page_start("Dashboard", "Selamat datang, " . $nama . ".", "dashboard");
?>

<section class="stat-grid">
    <?php $late = task_overdue_count($conn, $uid); ?>
    <div class="stat-card<?= $late > 0 ? " accent-danger" : " accent-primary" ?>">
        <div><span>Terlambat</span><strong><?= $late ?></strong></div>
    </div>
    <div class="stat-card accent-warning">
        <div><span>Berjalan</span><strong><?= $counts["in_progress"] ?></strong></div>
    </div>
    <div class="stat-card">
        <div><span>Menunggu</span><strong><?= $counts["pending"] ?></strong></div>
    </div>
    <div class="stat-card accent-success">
        <div><span>Selesai</span><strong><?= $counts["completed"] ?></strong></div>
    </div>
</section>

<section class="dashboard-panel">
    <div class="panel-header">
        <h2>Pekerjaan Terbaru</h2>
        <a href="task.php">Lihat Semua</a>
    </div>

    <?php if (empty($recent)): ?>
        <div class="empty-task">Belum ada pekerjaan. <a href="task.php">Tambah sekarang</a>.</div>
    <?php else: ?>
        <table class="report-table">
            <thead>
                <tr><th>Pekerjaan</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $t): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($t["title"]) ?></strong></td>
                        <td>
                            <span class="badge status-<?= htmlspecialchars($t["status"]) ?>">
                                <?= htmlspecialchars(TASK_STATUS_LABELS[$t["status"]]) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php page_end(); ?>
