<?php
require_once __DIR__ . "/../shared/init.php";
require_once __DIR__ . "/../shared/auth.php";
require_once __DIR__ . "/../shared/TaskRepository.php";

require_login();

$role = $_SESSION["role"] ?? "user";

// User hanya melihat task miliknya; supervisor & admin melihat semuanya.
$scoped = in_array($role, ["supervisor", "admin"], true) ? null : (int) $_SESSION["id"];

$filters = [
    "status"   => $_GET["status"] ?? "",
    "priority" => $_GET["priority"] ?? "",
];

$counts = task_status_counts($conn, $scoped);
$tasks  = task_list($conn, $scoped, $filters);

page_start("Laporan Pekerjaan", "Rekap status pekerjaan" . ($scoped ? " Anda" : " seluruh tim") . ".", "laporan");
?>

<section class="stat-grid">
    <div class="stat-card">
        <div><span>Total</span><strong><?= $counts["total"] ?></strong><small>Semua task</small></div>
        <div class="stat-icon">≡</div>
    </div>
    <div class="stat-card">
        <div><span>Belum Dikerjakan</span><strong><?= $counts["pending"] ?></strong><small>Menunggu</small></div>
        <div class="stat-icon orange">○</div>
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

<section class="dashboard-panel">
    <div class="panel-header">
        <h2>Monitoring Pekerjaan</h2>
    </div>

    <form method="GET" class="monitor-filter">
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
        <a href="laporan.php" class="btn-batal">Reset</a>
    </form>

    <?php if (empty($tasks)): ?>
        <div class="empty-task">Belum ada pekerjaan yang tercatat.</div>
    <?php else: ?>
        <table class="report-table laporan-table">
            <thead>
                <tr>
                    <th>Task</th>
                    <?php if (!$scoped): ?><th>User</th><?php endif; ?>
                    <th>Prioritas</th>
                    <th>Status</th>
                    <th>Deadline</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $t): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($t["title"]) ?></strong></td>
                        <?php if (!$scoped): ?><td><?= htmlspecialchars($t["nama_user"] ?? "-") ?></td><?php endif; ?>
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
