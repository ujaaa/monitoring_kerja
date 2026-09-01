<?php
require_once __DIR__ . "/../shared/init.php";
require_once __DIR__ . "/../shared/auth.php";
require_once __DIR__ . "/../shared/TaskRepository.php";

require_role("admin");

$counts = task_status_counts($conn, null);
$totalUsers = (int) ($conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()["c"] ?? 0);

$users = $conn->query("SELECT id, nama, username, email, role FROM users ORDER BY id DESC LIMIT 5")
              ->fetch_all(MYSQLI_ASSOC);
$recentTasks = array_slice(task_list($conn, null), 0, 5);

page_start("Dashboard Admin", "Ringkasan sistem dan aktivitas.", "dashboard");
?>

<section class="stat-grid">
    <div class="stat-card">
        <div><span>Total User</span><strong><?= $totalUsers ?></strong><small>Semua role</small></div>
        <div class="stat-icon">☰</div>
    </div>
    <div class="stat-card">
        <div><span>Total Task</span><strong><?= $counts["total"] ?></strong><small>Semua pekerjaan</small></div>
        <div class="stat-icon">≡</div>
    </div>
    <div class="stat-card">
        <div><span>Sedang Dikerjakan</span><strong><?= $counts["in_progress"] ?></strong><small>Task berjalan</small></div>
        <div class="stat-icon cyan">↻</div>
    </div>
    <div class="stat-card">
        <div><span>Selesai</span><strong><?= $counts["completed"] ?></strong><small>Task selesai</small></div>
        <div class="stat-icon green">✓</div>
    </div>
</section>

<section class="dashboard-columns">
    <div class="dashboard-panel">
        <div class="panel-header">
            <h2>User Terbaru</h2>
            <a href="users.php">Kelola User</a>
        </div>
        <?php if (empty($users)): ?>
            <div class="empty-task">Belum ada user terdaftar.</div>
        <?php else: ?>
            <table class="report-table">
                <thead><tr><th>Nama</th><th>Username</th><th>Role</th></tr></thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($u["nama"]) ?></strong></td>
                            <td><?= htmlspecialchars($u["username"]) ?></td>
                            <td><span class="badge role-<?= htmlspecialchars($u["role"]) ?>"><?= htmlspecialchars(role_label($u["role"])) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="dashboard-panel">
        <div class="panel-header">
            <h2>Task Terbaru</h2>
            <a href="../shared/laporan.php">Laporan</a>
        </div>
        <?php if (empty($recentTasks)): ?>
            <div class="empty-task">Belum ada pekerjaan tercatat.</div>
        <?php else: ?>
            <table class="report-table">
                <thead><tr><th>Task</th><th>User</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($recentTasks as $t): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($t["title"]) ?></strong></td>
                            <td><?= htmlspecialchars($t["nama_user"] ?? "-") ?></td>
                            <td><span class="badge status-<?= htmlspecialchars($t["status"]) ?>"><?= htmlspecialchars(TASK_STATUS_LABELS[$t["status"]]) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>

<?php page_end(); ?>
