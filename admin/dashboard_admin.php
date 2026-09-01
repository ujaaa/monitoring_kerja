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
    <div class="stat-card accent-primary">
        <div><span>Total User</span><strong><?= $totalUsers ?></strong></div>
    </div>
    <div class="stat-card">
        <div><span>Total Task</span><strong><?= $counts["total"] ?></strong></div>
    </div>
    <div class="stat-card accent-warning">
        <div><span>Sedang Dikerjakan</span><strong><?= $counts["in_progress"] ?></strong></div>
    </div>
    <div class="stat-card accent-success">
        <div><span>Selesai</span><strong><?= $counts["completed"] ?></strong></div>
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
            <table class="report-table admin-user-table">
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
            <table class="report-table admin-task-table">
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

<section class="dashboard-panel">
    <div class="panel-header">
        <h2>Laporan Pekerjaan</h2>
        <a href="../shared/laporan.php">Lihat Semua</a>
    </div>
    <?php if ($counts["total"] === 0): ?>
        <div class="empty-task">Belum ada pekerjaan tercatat.</div>
    <?php else: ?>
        <table class="report-table admin-laporan-table">
            <thead>
                <tr><th>Status</th><th>Jumlah</th><th>Persentase</th></tr>
            </thead>
            <tbody>
                <?php foreach (["pending" => "Belum Dikerjakan", "in_progress" => "Sedang Dikerjakan", "completed" => "Selesai"] as $key => $label): ?>
                    <?php
                        $c = (int) ($counts[$key] ?? 0);
                        $pct = $counts["total"] > 0 ? round($c / $counts["total"] * 100) : 0;
                    ?>
                    <tr>
                        <td><span class="badge status-<?= $key ?>"><?= htmlspecialchars($label) ?></span></td>
                        <td><strong><?= $c ?></strong></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="flex:1;height:6px;background:var(--border);border-radius:999px;overflow:hidden;min-width:80px;">
                                    <div style="height:100%;width:<?= $pct ?>%;background:var(--ink);border-radius:999px;transition:width 300ms ease;"></div>
                                </div>
                                <span style="font-family:var(--font-mono);font-variant-numeric:tabular-nums;font-size:12px;color:var(--muted);min-width:36px;text-align:right;"><?= $pct ?>%</span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php page_end(); ?>
