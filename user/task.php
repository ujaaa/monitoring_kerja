<?php
require_once __DIR__ . "/../shared/init.php";
require_once __DIR__ . "/../shared/auth.php";
require_once __DIR__ . "/../shared/flash.php";
require_once __DIR__ . "/../shared/TaskRepository.php";

require_role("user", "admin");

$uid = (int) $_SESSION["id"];

/* Tambah task via modal */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "create") {
    csrf_check();
    [$ok, $err, $data] = task_validate($_POST, true); // deadline baru tidak boleh lampau
    if (!$ok) {
        set_flash("error", $err);
    } else {
        [$ok, $err] = task_create($conn, $data, $uid, $uid);
        set_flash($ok ? "success" : "error", $ok ? "Pekerjaan \"{$data["title"]}\" berhasil ditambahkan." : (string) $err);
    }
    header("Location: task.php");
    exit;
}

/* Ubah status cepat dari baris tabel */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["quick_status"])) {
    csrf_check();
    $qsId   = (int) ($_POST["id"] ?? 0);
    $qsSt   = $_POST["quick_status"] ?? "";
    $qsTask = task_find($conn, $qsId, $uid);
    if ($qsTask && in_array($qsSt, TASK_STATUSES, true) && $qsSt !== $qsTask["status"]) {
        $data = [
            "title" => $qsTask["title"], "description" => $qsTask["description"],
            "deadline" => $qsTask["deadline"], "priority" => $qsTask["priority"], "status" => $qsSt,
        ];
        if (task_update($conn, $qsId, $data, $uid)) {
            set_flash("success", "Status diperbarui: " . TASK_STATUS_LABELS[$qsSt] . ".");
        } else {
            set_flash("error", "Gagal memperbarui status.");
        }
    } else {
        set_flash("error", "Perubahan status tidak valid.");
    }
    header("Location: task.php");
    exit;
}

/* Pagination + pencarian */
$q    = trim($_GET["q"] ?? "");
$stFl = $_GET["status"] ?? "";
$filters = ["q" => $q, "status" => $stFl];
$totalTasks = task_count($conn, $uid, array_filter($filters));
$perPage  = 10;
$page     = max(1, (int) ($_GET["page"] ?? 1));
$maxPage  = max(1, (int) ceil($totalTasks / $perPage));
$page     = min($page, $maxPage);
$offset   = ($page - 1) * $perPage;
$tasks    = task_list($conn, $uid, $filters, $perPage, $offset);
$counts   = task_status_counts($conn, $uid);
$late     = task_overdue_count($conn, $uid);

page_start("Data Pekerjaan", "Kelola semua pekerjaan Anda.", "task");
?>

<div class="stat-grid">
    <div class="stat-card<?= $late > 0 ? " stat-late" : " stat-zero" ?>">
        <div><span>Terlambat</span><strong><?= $late ?></strong></div>
    </div>
    <div class="stat-card<?= $counts["in_progress"] > 0 ? " stat-today" : " stat-zero" ?>">
        <div><span>Berjalan</span><strong><?= $counts["in_progress"] ?></strong></div>
    </div>
    <div class="stat-card<?= $counts["pending"] > 0 ? "" : " stat-zero" ?>">
        <div><span>Menunggu</span><strong><?= $counts["pending"] ?></strong></div>
    </div>
    <div class="stat-card<?= $counts["completed"] > 0 ? " stat-done" : " stat-zero" ?>">
        <div><span>Selesai</span><strong><?= $counts["completed"] ?></strong></div>
    </div>
</div>
<section class="dashboard-panel">
    <div class="panel-header">
        <h2>Daftar Pekerjaan</h2>
        <button type="button" class="btn-simpan" id="btnTambahKerjaan">+ Tambah Pekerjaan</button>
    </div>

    <form method="GET" class="monitor-filter">
        <input type="text" name="q" placeholder="Cari judul pekerjaan…" value="<?= htmlspecialchars($q) ?>">
        <select name="status">
            <option value="">Semua status</option>
            <?php foreach (TASK_STATUS_LABELS as $val => $label): ?>
                <option value="<?= $val ?>" <?= $stFl === $val ? "selected" : "" ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-simpan">Terapkan</button>
        <?php if ($q !== "" || $stFl !== ""): ?>
            <a href="task.php" class="btn-batal">Reset</a>
        <?php endif; ?>
    </form>

    <?php if (empty($tasks)): ?>
        <div class="empty-task">
            Belum ada pekerjaan. Klik "Tambah Pekerjaan" untuk memulai.
        </div>
    <?php else: ?>
        <table class="report-table task-main-table">
            <thead>
                <tr>
                    <th>Pekerjaan</th>
                    <th>Prioritas</th>
                    <th>Status</th>
                    <th>Deadline</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $t): ?>
                    <?php $telat = $t["status"] !== "completed" && $t["deadline"] < date("Y-m-d"); ?>
                    <?php $cd = task_countdown($t); ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($t["title"]) ?></strong>
                            <div class="task-desc"><?= htmlspecialchars(str_truncate($t["description"], 80)) ?></div>
                        </td>
                        <td><span class="badge priority-<?= htmlspecialchars($t["priority"]) ?>"><?= htmlspecialchars(TASK_PRIORITY_LABELS[$t["priority"]]) ?></span></td>
                        <td>
                            <form method="POST" action="task.php" class="status-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $t["id"] ?>">
                                <select name="quick_status" aria-label="Ubah status <?= htmlspecialchars($t["title"]) ?>" onchange="this.form.submit()">
                                    <?php foreach (TASK_STATUS_LABELS as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= $t["status"] === $val ? "selected" : "" ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td>
                            <span class="deadline-cell <?= $telat ? "deadline-overdue" : "" ?>"><?= htmlspecialchars($t["deadline"]) ?></span>
                            <div><span class="countdown <?= $cd[0] ?>"><?= htmlspecialchars($cd[1]) ?></span></div>
                        </td>
                        <td class="task-actions">
                            <a href="edit_task.php?id=<?= (int) $t["id"] ?>" class="btn-kecil">Edit</a>
                            <form action="hapus_task.php" method="POST" onsubmit="return confirm('Hapus pekerjaan ini?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $t["id"] ?>">
                                <button type="submit" class="btn-kecil btn-kecil-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($maxPage > 1): ?>
            <nav class="pagination" aria-label="Navigasi halaman">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&amp;q=<?= urlencode($q) ?>&amp;status=<?= urlencode($stFl) ?>" class="btn-kecil">← Sebelumnya</a>
                <?php endif; ?>
                <span class="page-info">Halaman <?= $page ?> dari <?= $maxPage ?></span>
                <?php if ($page < $maxPage): ?>
                    <a href="?page=<?= $page + 1 ?>&amp;q=<?= urlencode($q) ?>&amp;status=<?= urlencode($stFl) ?>" class="btn-kecil">Berikutnya →</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
<!-- MODAL TAMBAH PEKERJAAN -->
<div class="modal-overlay" id="modalTambahTask" aria-hidden="true">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h2>Tambah Pekerjaan</h2>
                <p>Isi detail pekerjaan baru Anda.</p>
            </div>
            <button type="button" class="modal-close" id="btnTutupModal" aria-label="Tutup">×</button>
        </div>
        <form method="POST" action="task.php">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="create">
            <div class="form-group">
                <label for="title">Nama Pekerjaan</label>
                <input type="text" id="title" name="title" autocomplete="off" placeholder="cth. Laporan penjualan Mei…" required>
            </div>
            <div class="form-group">
                <label for="deadline">Deadline</label>
                <input type="date" id="deadline" name="deadline" autocomplete="off" min="<?= date("Y-m-d") ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description" placeholder="Jelaskan hasil yang diharapkan…" required></textarea>
            </div>
            <div class="form-group">
                <label for="priority">Prioritas</label>
                <select id="priority" name="priority" autocomplete="off">
                    <option value="low">Rendah</option>
                    <option value="medium" selected>Sedang</option>
                    <option value="high">Tinggi</option>
                </select>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="pending" selected>Belum Dikerjakan</option>
                    <option value="in_progress">Sedang Dikerjakan</option>
                    <option value="completed">Selesai</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-batal" id="btnBatalModal">Batal</button>
                <button type="submit" class="btn-simpan">Simpan Pekerjaan</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("modalTambahTask");
    const open  = () => { modal.classList.add("open"); modal.setAttribute("aria-hidden", "false"); modal.querySelector("#title").focus(); };
    const close = () => { modal.classList.remove("open"); modal.setAttribute("aria-hidden", "true"); };

    document.getElementById("btnTambahKerjaan").addEventListener("click", open);
    document.getElementById("btnTutupModal").addEventListener("click", close);
    document.getElementById("btnBatalModal").addEventListener("click", close);
    modal.addEventListener("click", (e) => { if (e.target === modal) close(); });
    document.addEventListener("keydown", (e) => { if (e.key === "Escape" && modal.classList.contains("open")) close(); });
});
</script>

<?php page_end(); ?>


