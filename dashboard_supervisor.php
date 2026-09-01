<?php
session_start();

require_once "koneksi.php";

if (
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "supervisor"
) {
    header("Location: index.php");
    exit;
}

$nama = $_SESSION["nama"] ?? "Supervisor";

/* ==========================================================
   RINGKASAN STATISTIK
========================================================== */

$total_task    = 0;
$total_selesai = 0;
$total_proses  = 0;
$total_belum   = 0;

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
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Supervisor</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="app">

    <aside class="sidebar">

        <div class="brand">
            <div class="brand-logo">M</div>

            <div class="brand-text">
                MONITORING<br>
                KERJA
            </div>
        </div>


        <a
            href="dashboard_supervisor.php"
            class="menu active"
        >
            <span>▣</span>
            Dashboard
        </a>


        <a href="laporan.php" class="menu">
            <span>📄</span>
            Laporan
        </a>


        <a href="logout.php" class="menu">
            <span>↪</span>
            Logout
        </a>

    </aside>


    <main class="content">

        <header class="topbar">

            <div>

                <h1>Dashboard</h1>

                <p>
                    Monitoring pekerjaan tim
                </p>

            </div>


            <div class="profile-dropdown">

                <button
                    class="profile-button"
                    onclick="toggleProfile()"
                >

                    <span class="profile-avatar">
                        <?= strtoupper(substr($nama, 0, 1)) ?>
                    </span>

                    <span>
                        <?= htmlspecialchars($nama) ?>
                    </span>

                    <span>⌄</span>

                </button>


                <div
                    class="profile-dropdown-menu"
                    id="profileDropdown"
                >

                    <button onclick="openProfile()">
                        👤 Profile
                    </button>

                    <button>
                        ⚙ Settings
                    </button>

                    <button>
                        ☷ Activity Log
                    </button>

                    <hr>

                    <a href="logout.php">
                        ↪ Logout
                    </a>

                </div>

            </div>

        </header>


        <section class="dashboard-content">


            <div class="welcome-card">

                <h2>
                    Monitoring Pekerjaan
                </h2>

                <p>
                    Pantau perkembangan pekerjaan
                    seluruh user dalam tim.
                </p>

            </div>


            <div class="dashboard-cards">

                <div class="stat-card">

                    <div>
                        <span>Total Task</span>
                        <strong><?= $total_task ?></strong>
                        <small>Semua pekerjaan</small>
                    </div>

                    <div class="stat-icon blue">
                        ✓
                    </div>

                </div>


                <div class="stat-card">

                    <div>
                        <span>Belum Selesai</span>
                        <strong><?= $total_belum ?></strong>
                        <small>Perlu dipantau</small>
                    </div>

                    <div class="stat-icon orange">
                        ○
                    </div>

                </div>


                <div class="stat-card">

                    <div>
                        <span>Sedang Dikerjakan</span>
                        <strong><?= $total_proses ?></strong>
                        <small>Task berjalan</small>
                    </div>

                    <div class="stat-icon cyan">
                        ↻
                    </div>

                </div>


                <div class="stat-card">

                    <div>
                        <span>Selesai</span>
                        <strong><?= $total_selesai ?></strong>
                        <small>Task selesai</small>
                    </div>

                    <div class="stat-icon green">
                        ✓
                    </div>

                </div>

            </div>

        </section>

    </main>

</div>


<!-- MODAL PROFILE -->

<div
    class="profile-modal-overlay"
    id="profileModal"
>

    <div class="profile-modal">

        <div class="profile-modal-header">

            <h2>Profil Saya</h2>

            <button onclick="closeProfile()">
                ×
            </button>

        </div>


        <div class="profile-modal-user">

            <div class="profile-big-avatar">
                <?= strtoupper(substr($nama, 0, 1)) ?>
            </div>

            <div>

                <h3>
                    <?= htmlspecialchars($nama) ?>
                </h3>

                <p>
                    Supervisor
                </p>

            </div>

        </div>


        <div class="profile-info">

            <div>
                <span>Nama</span>

                <strong>
                    <?= htmlspecialchars($nama) ?>
                </strong>
            </div>


            <div>
                <span>Username</span>

                <strong>
                    <?= htmlspecialchars($_SESSION["username"] ?? "-") ?>
                </strong>
            </div>


            <div>
                <span>Email</span>

                <strong>
                    <?= htmlspecialchars($_SESSION["email"] ?? "-") ?>
                </strong>
            </div>


            <div>
                <span>Role</span>

                <strong>
                    Supervisor
                </strong>
            </div>

        </div>


        <button
            class="profile-close-button"
            onclick="closeProfile()"
        >
            Tutup
        </button>

    </div>

</div>


<script>

function toggleProfile() {

    document
        .getElementById("profileDropdown")
        .classList.toggle("show");

}


function openProfile() {

    document
        .getElementById("profileDropdown")
        .classList.remove("show");

    document
        .getElementById("profileModal")
        .classList.add("show");

}


function closeProfile() {

    document
        .getElementById("profileModal")
        .classList.remove("show");

}

</script>

</body>
</html>