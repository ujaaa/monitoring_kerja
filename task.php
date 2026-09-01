<?php
session_start();
require_once "koneksi.php";

/* =========================================================
   CEK LOGIN
========================================================= */
if (
    !isset($_SESSION["role"]) ||
    !in_array($_SESSION["role"], ["user", "admin"], true)
) {
    header("Location: index.php");
    exit;
}

$user_id = (int) ($_SESSION["id"] ?? 0);
$nama = $_SESSION["nama"] ?? "User";
$error = "";
$success = "";

/* =========================================================
   FOTO PROFIL
========================================================= */
$fotoProfil = "";

$stmtFoto = $conn->prepare("SELECT foto FROM users WHERE id = ?");
if ($stmtFoto) {
    $stmtFoto->bind_param("i", $user_id);
    $stmtFoto->execute();
    $rowFoto = $stmtFoto->get_result()->fetch_assoc();

    if (
        $rowFoto &&
        !empty($rowFoto["foto"]) &&
        file_exists("uploads/" . $rowFoto["foto"])
    ) {
        $fotoProfil = $rowFoto["foto"];
    }
}

/* =========================================================
   TAMBAH PEKERJAAN - FORM MODAL
========================================================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $deadline = trim($_POST["deadline"] ?? "");
    $priority = $_POST["priority"] ?? "medium";
    $status = $_POST["status"] ?? "pending";

    $allowedPriority = ["low", "medium", "high"];
    $allowedStatus = ["pending", "in_progress", "completed"];

    if ($title === "" || $description === "" || $deadline === "") {
        $error = "Nama pekerjaan, deskripsi, dan deadline wajib diisi.";
    } elseif (!in_array($priority, $allowedPriority, true)) {
        $error = "Prioritas tidak valid.";
    } elseif (!in_array($status, $allowedStatus, true)) {
        $error = "Status tidak valid.";
    } else {
        /*
         * Gunakan assigned_to karena struktur task di aplikasi
         * menggunakan assigned_to, bukan user_id.
         */
        $stmt = $conn->prepare("
            INSERT INTO tasks
                (title, description, assigned_to, assigned_by, priority, status, deadline)
            VALUES
                (?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            $error = "Gagal menyiapkan penyimpanan: " . $conn->error;
        } else {
            $stmt->bind_param(
                "ssiisss",
                $title,
                $description,
                $user_id,
                $user_id,
                $priority,
                $status,
                $deadline
            );

            if ($stmt->execute()) {
                header("Location: task.php?success=1");
                exit;
            }

            $error = "Gagal menyimpan pekerjaan: " . $stmt->error;
        }
    }
}

if (isset($_GET["success"])) {
    $success = "Pekerjaan berhasil ditambahkan.";
}

/* =========================================================
   AMBIL DATA PEKERJAAN USER
========================================================= */
$stmt = $conn->prepare("
    SELECT
        id,
        title,
        description,
        priority,
        status,
        deadline
    FROM tasks
    WHERE assigned_to = ?
    ORDER BY deadline ASC, id DESC
");

if (!$stmt) {
    die("Query tasks gagal. Pastikan tabel tasks sudah dibuat. Detail: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pekerjaan - Monitoring Kerja</title>
    <link rel="stylesheet" href="task.css?v=11">
</head>

<body>

<div class="app">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-logo">M</div>
            <div class="brand-text">MONITORING<br>KERJA</div>
        </div>

        <nav class="menu">
            <a href="dashboard_user.php" class="menu-item">
                <span class="icon">▣</span>
                <span>Dashboard</span>
            </a>

            <button type="button" class="menu-item menu-button active" id="btnTambahSidebar">
                <span class="icon">+</span>
                <span>Tambah Pekerjaan</span>
            </button>

            <a href="logout.php" class="menu-item">
                <span class="icon">↪</span>
                <span>Logout</span>
            </a>
        </nav>
    </aside>

    <!-- CONTENT -->
    <main class="content">

        <header class="topbar">
            <div>
                <h1>Data Pekerjaan</h1>
                <p>Kelola dan pantau pekerjaan kamu.</p>
            </div>

            <div class="profile-dropdown">
                <button type="button" class="profile-button" id="btnProfile">
                    <span class="profile-avatar">
                        <?php if ($fotoProfil): ?>
                            <img src="uploads/<?= htmlspecialchars($fotoProfil) ?>" alt="Foto Profil">
                        <?php else: ?>
                            <?= strtoupper(substr($nama, 0, 1)) ?>
                        <?php endif; ?>
                    </span>
                    <span><?= htmlspecialchars($nama) ?></span>
                    <span>⌄</span>
                </button>

                <div class="profile-dropdown-menu" id="profileDropdown">
                    <a href="profil.php">👤 Profile</a>
                    <hr>
                    <a href="logout.php">↪ Logout</a>
                </div>
            </div>
        </header>

        <?php if ($success): ?>
            <div class="task-alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="task-alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <section class="task-card">
            <div class="task-card-header">
                <div>
                    <h2>Data Pekerjaan</h2>
                    <p>Daftar pekerjaan yang sedang dipantau.</p>
                </div>

                <button type="button" class="btn-tambah" id="btnTambahKerjaan">
                    <span>+</span> Tambah Kerjaan
                </button>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>NAMA PEKERJAAN</th>
                            <th>DESKRIPSI</th>
                            <th>DEADLINE</th>
                            <th>PRIORITAS</th>
                            <th>STATUS</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php $no = 1; ?>
                        <?php while ($task = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="number"><?= $no++ ?></td>

                                <td>
                                    <strong><?= htmlspecialchars($task["title"]) ?></strong>
                                </td>

                                <td class="description">
                                    <?= htmlspecialchars($task["description"]) ?>
                                </td>

                                <td>
                                    <?= !empty($task["deadline"])
                                        ? htmlspecialchars(date("d M Y", strtotime($task["deadline"])))
                                        : "-" ?>
                                </td>

                                <td>
                                    <?php
                                    $priority = $task["priority"] ?? "medium";
                                    $priorityLabel = [
                                        "low" => "Rendah",
                                        "medium" => "Sedang",
                                        "high" => "Tinggi"
                                    ][$priority] ?? "Sedang";
                                    ?>
                                    <span class="badge priority-<?= htmlspecialchars($priority) ?>">
                                        <?= $priorityLabel ?>
                                    </span>
                                </td>

                                <td>
                                    <?php
                                    $status = $task["status"] ?? "pending";
                                    $statusMap = [
                                        "pending" => ["Belum Dikerjakan", "status-pending"],
                                        "in_progress" => ["Sedang Dikerjakan", "status-progress"],
                                        "completed" => ["Selesai", "status-completed"]
                                    ];
                                    [$statusLabel, $statusClass] =
                                        $statusMap[$status] ?? $statusMap["pending"];
                                    ?>
                                    <span class="badge <?= $statusClass ?>">
                                        <?= $statusLabel ?>
                                    </span>
                                </td>

                                <td>
                                    <a
                                        href="hapus_task.php?id=<?= (int) $task["id"] ?>"
                                        class="btn-hapus"
                                        onclick="return confirm('Yakin ingin menghapus pekerjaan ini?')"
                                    >Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty">Belum ada pekerjaan.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<!-- =========================================================
     MODAL TAMBAH PEKERJAAN
========================================================= -->
<div class="modal-overlay" id="modalTambahTask" aria-hidden="true">
    <div class="modal-box task-modal-box" role="dialog" aria-modal="true" aria-labelledby="judulModalTask">

        <div class="modal-header">
            <div>
                <h2 id="judulModalTask">Tambah Pekerjaan</h2>
                <p>Isi informasi pekerjaan yang ingin kamu pantau.</p>
            </div>

            <button type="button" class="modal-close" id="btnTutupModal" aria-label="Tutup">×</button>
        </div>

        <form method="POST" action="task.php">
            <div class="form-grid-modal">

                <div class="form-group">
                    <label for="title">Nama Pekerjaan</label>
                    <input
                        id="title"
                        type="text"
                        name="title"
                        placeholder="Contoh: Membuat laporan bulanan"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="deadline">Deadline</label>
                    <input id="deadline" type="date" name="deadline" required>
                </div>

                <div class="form-group full">
                    <label for="description">Deskripsi Pekerjaan</label>
                    <textarea
                        id="description"
                        name="description"
                        placeholder="Jelaskan pekerjaan yang harus dilakukan..."
                        required
                    ></textarea>
                </div>

                <div class="form-group">
                    <label for="priority">Prioritas</label>
                    <select id="priority" name="priority">
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
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-task-cancel" id="btnBatalModal">Batal</button>
                <button type="submit" class="btn-task-save">Simpan Pekerjaan</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("modalTambahTask");
    const btnTambah = document.getElementById("btnTambahKerjaan");
    const btnSidebar = document.getElementById("btnTambahSidebar");
    const btnTutup = document.getElementById("btnTutupModal");
    const btnBatal = document.getElementById("btnBatalModal");
    const btnProfile = document.getElementById("btnProfile");
    const profileDropdown = document.getElementById("profileDropdown");

    function openModal() {
        modal.classList.add("open");
        modal.setAttribute("aria-hidden", "false");
        document.body.classList.add("modal-open");

        const title = document.getElementById("title");
        if (title) title.focus();
    }

    function closeModal() {
        modal.classList.remove("open");
        modal.setAttribute("aria-hidden", "true");
        document.body.classList.remove("modal-open");
    }

    if (btnTambah) btnTambah.addEventListener("click", openModal);
    if (btnSidebar) btnSidebar.addEventListener("click", openModal);
    if (btnTutup) btnTutup.addEventListener("click", closeModal);
    if (btnBatal) btnBatal.addEventListener("click", closeModal);

    if (modal) {
        modal.addEventListener("click", function (event) {
            if (event.target === modal) closeModal();
        });
    }

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && modal.classList.contains("open")) {
            closeModal();
        }
    });

    if (btnProfile && profileDropdown) {
        btnProfile.addEventListener("click", function (event) {
            event.stopPropagation();
            profileDropdown.classList.toggle("show");
        });

        document.addEventListener("click", function (event) {
            if (
                !profileDropdown.contains(event.target) &&
                !btnProfile.contains(event.target)
            ) {
                profileDropdown.classList.remove("show");
            }
        });
    }

    // Jika server mengembalikan error validasi, buka modal lagi.
    <?php if ($error): ?>
    openModal();
    <?php endif; ?>
});
</script>

</body>
</html>
