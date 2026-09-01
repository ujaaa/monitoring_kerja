<?php
session_start();

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "user") {
    header("Location: index.php");
    exit;
}

$nama = $_SESSION["nama"] ?? "User";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="app">

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <div class="brand">
            <div class="brand-logo">M</div>
            <div class="brand-text">
                MONITORING<br>
                KERJA
            </div>
        </div>

        <a href="dashboard_user.php" class="menu active">
            <span>▣</span>
            Dashboard
        </a>

        <a href="task.php" class="menu">
            <span>+</span>
            Tambah Kerjaan
        </a>

        <a href="logout.php" class="menu">
            <span>↪</span>
            Logout
        </a>

    </aside>


    <!-- CONTENT -->
    <main class="content">

        <!-- TOPBAR -->
        <header class="topbar">

            <div>
                <h1>Dashboard</h1>
                <p>
                    Selamat datang,
                    <?= htmlspecialchars($nama) ?>
                </p>
            </div>

            <!-- PROFILE -->
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


        <!-- DASHBOARD USER -->

        <section class="dashboard-content">

            <div class="welcome-card">
                <h2>Monitoring Kerja</h2>

                <p>
                    Pantau dan kelola pekerjaan kamu
                    melalui sistem Monitoring Kerja.
                </p>
            </div>


            <!-- STATISTIK -->

            <div class="dashboard-cards">

                <div class="stat-card">
                    <div>
                        <span>Total Task</span>
                        <strong>0</strong>
                        <small>Semua pekerjaan</small>
                    </div>
                    <div class="stat-icon blue">✓</div>
                </div>


                <div class="stat-card">
                    <div>
                        <span>Belum Selesai</span>
                        <strong>0</strong>
                        <small>Belum dikerjakan</small>
                    </div>
                    <div class="stat-icon orange">○</div>
                </div>


                <div class="stat-card">
                    <div>
                        <span>Sedang Dikerjakan</span>
                        <strong>0</strong>
                        <small>Task berjalan</small>
                    </div>
                    <div class="stat-icon cyan">↻</div>
                </div>


                <div class="stat-card">
                    <div>
                        <span>Selesai</span>
                        <strong>0</strong>
                        <small>Task selesai</small>
                    </div>
                    <div class="stat-icon green">✓</div>
                </div>

            </div>


            <!-- TASK TERBARU -->

            <div class="dashboard-panel">

                <div class="panel-header">
                    <h2>Pekerjaan Terbaru</h2>

                    <a href="task.php">
                        Lihat Semua
                    </a>
                </div>

                <div class="empty-task">
                    Belum ada pekerjaan.
                </div>

            </div>

        </section>

    </main>

</div>


<!-- PROFILE MODAL -->

<div class="profile-modal-overlay" id="profileModal">

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
                <h3><?= htmlspecialchars($nama) ?></h3>
                <p>User</p>
            </div>

        </div>

        <div class="profile-info">

            <div>
                <span>Nama</span>
                <strong><?= htmlspecialchars($nama) ?></strong>
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
                <strong>User</strong>
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