<?php

session_start();

require_once "koneksi.php";

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

$role = $_SESSION["role"] ?? "user";

$dashboard_link = match ($role) {
    "admin"      => "dashboard_admin.php",
    "supervisor" => "dashboard_supervisor.php",
    default      => "dashboard_user.php",
};

$role_label = match ($role) {
    "admin"      => "Administrator",
    "supervisor" => "Supervisor",
    default      => "User",
};

/* ==========================================================
   RINGKASAN STATISTIK
========================================================== */

$total_task     = 0;
$total_selesai  = 0;
$total_proses   = 0;
$total_belum    = 0;

$result_stat = $conn->query(
    "SELECT status, COUNT(*) AS jumlah FROM tasks GROUP BY status"
);

if ($result_stat) {
    while ($row = $result_stat->fetch_assoc()) {

        $total_task += $row["jumlah"];

        if ($row["status"] === "completed") {
            $total_selesai = $row["jumlah"];
        } elseif ($row["status"] === "in_progress") {
            $total_proses = $row["jumlah"];
        } elseif ($row["status"] === "pending") {
            $total_belum = $row["jumlah"];
        }
    }
}

/* ==========================================================
   DAFTAR TASK (MONITORING PEKERJAAN)
========================================================== */

$daftar_task = [];

$result_task = $conn->query(
    "SELECT tasks.*, users.nama AS nama_user
     FROM tasks
     LEFT JOIN users ON tasks.assigned_to = users.id
     ORDER BY tasks.id DESC"
);

if ($result_task) {
    $daftar_task = $result_task->fetch_all(MYSQLI_ASSOC);
}

$label_status = [
    "completed"   => "Selesai",
    "in_progress" => "Sedang Dikerjakan",
    "pending"     => "Belum Dikerjakan",
];

$kelas_status = [
    "completed"   => "green",
    "in_progress" => "cyan",
    "pending"     => "orange",
];

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pekerjaan</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="app">

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <div class="brand">
            <div class="brand-logo">M</div>
            <div class="brand-text">MONITORING<br>KERJA</div>
        </div>

        <a href="<?= $dashboard_link ?>" class="menu">
            <span>&#9638;</span>
            <span>Dashboard</span>
        </a>

        <?php if ($role === "supervisor"): ?>

            <a href="laporan.php" class="menu active">
                <span>&#128203;</span>
                <span>Laporan</span>
            </a>

            <a href="logout.php" class="menu">
                <span>&#8677;</span>
                <span>Logout</span>
            </a>

        <?php else: ?>

            <button type="button" class="menu task-toggle" id="btnTaskToggle">
                <span>&check;</span>
                <span>TASK</span>
                <span id="taskArrow">&#9662;</span>
            </button>

            <div class="task-submenu show" id="taskSubmenu">
                <a href="task.php">DATA PEKERJAAN</a>
                <a href="laporan.php" style="color:white;background:rgba(255,255,255,.13);">LAPORAN</a>
            </div>

            <a href="logout.php" class="menu" style="margin-top:auto;">
                <span>&#8677;</span>
                <span>Logout</span>
            </a>

        <?php endif; ?>

    </aside>


    <!-- CONTENT -->
    <main class="content">

        <div class="topbar">

            <div>
                <h1>Laporan Pekerjaan</h1>
                <p>Ringkasan dan rekap progres pekerjaan tim.</p>
            </div>

            <div class="profile-dropdown">

                <button type="button" class="profile-button" id="btnProfile">
                    <div class="profile-avatar">
                        <?= strtoupper(substr($_SESSION["nama"], 0, 1)) ?>
                    </div>
                    <span><?= htmlspecialchars($_SESSION["nama"]) ?></span>
                    <span class="profile-arrow">&#9662;</span>
                </button>

                <div class="profile-dropdown-menu" id="profileMenu">
                    <button type="button" id="btnOpenProfileModal">
                        &#128100;&nbsp; Profil Saya
                    </button>
                    <hr>
                    <a href="logout.php">
                        &#128682;&nbsp; Logout
                    </a>
                </div>

            </div>

        </div>


        <div class="welcome-card">
            <h2>Laporan Pekerjaan</h2>
            <p>Pantau performa dan progres seluruh pekerjaan yang sedang dimonitor.</p>
        </div>


        <!-- RINGKASAN STATISTIK -->
        <div class="dashboard-cards">

            <div class="stat-card">
                <div>
                    <span>Total Task</span>
                    <strong><?= $total_task ?></strong>
                    <small>Semua pekerjaan</small>
                </div>
                <div class="stat-icon blue">&#128203;</div>
            </div>

            <div class="stat-card">
                <div>
                    <span>Selesai</span>
                    <strong><?= $total_selesai ?></strong>
                    <small>Sudah rampung</small>
                </div>
                <div class="stat-icon green">&#10003;</div>
            </div>

            <div class="stat-card">
                <div>
                    <span>Sedang Dikerjakan</span>
                    <strong><?= $total_proses ?></strong>
                    <small>Dalam proses</small>
                </div>
                <div class="stat-icon cyan">&#9203;</div>
            </div>

            <div class="stat-card">
                <div>
                    <span>Belum Dikerjakan</span>
                    <strong><?= $total_belum ?></strong>
                    <small>Menunggu dikerjakan</small>
                </div>
                <div class="stat-icon orange">&#9888;</div>
            </div>

        </div>


        <!-- MONITORING PEKERJAAN -->
        <div class="dashboard-panel">

            <div class="panel-header">
                <h2>Monitoring Pekerjaan</h2>
            </div>

            <?php if (empty($daftar_task)): ?>

                <div class="empty-task">
                    Belum ada pekerjaan yang perlu dipantau.
                </div>

            <?php else: ?>

                <table class="report-table">

                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>User</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($daftar_task as $t): ?>

                            <?php
                                $judul = $t["judul"]
                                    ?? $t["nama_task"]
                                    ?? $t["title"]
                                    ?? ("Task #" . $t["id"]);

                                $namaUser = $t["nama_user"] ?? "-";

                                $status = $t["status"] ?? "pending";

                                $labelStatus = $label_status[$status] ?? $status;
                                $kelasStatus = $kelas_status[$status] ?? "orange";
                            ?>

                            <tr>
                                <td><?= htmlspecialchars($judul) ?></td>
                                <td><?= htmlspecialchars($namaUser) ?></td>
                                <td>
                                    <span class="stat-icon <?= $kelasStatus ?>" style="width:auto;height:auto;padding:4px 10px;border-radius:20px;font-size:12px;display:inline-block;">
                                        <?= htmlspecialchars($labelStatus) ?>
                                    </span>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            <?php endif; ?>

        </div>

    </main>

</div>


<!-- MODAL PROFIL -->
<div class="profile-modal-overlay" id="profileModalOverlay">

    <div class="profile-modal">

        <div class="profile-modal-header">
            <h2>Profil Saya</h2>
            <button type="button" id="btnCloseProfileModal">&times;</button>
        </div>

        <div class="profile-modal-user">
            <div class="profile-big-avatar">
                <?= strtoupper(substr($_SESSION["nama"], 0, 1)) ?>
            </div>
            <div>
                <h3><?= htmlspecialchars($_SESSION["nama"]) ?></h3>
                <p><?= $role_label ?></p>
            </div>
        </div>

        <div class="profile-info">
            <div>
                <span>Username</span>
                <strong><?= htmlspecialchars($_SESSION["username"] ?? "-") ?></strong>
            </div>
            <div>
                <span>Email</span>
                <strong><?= htmlspecialchars($_SESSION["email"] ?? "-") ?></strong>
            </div>
        </div>

        <button type="button" class="profile-close-button" id="btnCloseProfileModal2">
            Tutup
        </button>

    </div>

</div>


<script src="script.js"></script>

</body>

</html>