<?php

session_start();

require_once "koneksi.php";


/* =====================================================
   CEK LOGIN ADMIN
===================================================== */

if (
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: index.php");
    exit;
}


/* =====================================================
   DATA SESSION
===================================================== */

$nama = $_SESSION["nama"] ?? "Admin";

$userId = $_SESSION["id"] ?? 0;


/* =====================================================
   AMBIL FOTO PROFIL
===================================================== */

$fotoProfil = "";

$stmtFoto = $conn->prepare(
    "SELECT foto
     FROM users
     WHERE id = ?
     LIMIT 1"
);

$stmtFoto->bind_param(
    "i",
    $userId
);

$stmtFoto->execute();

$resultFoto = $stmtFoto->get_result();

if ($resultFoto->num_rows === 1) {

    $dataFoto = $resultFoto->fetch_assoc();

    if (
        !empty($dataFoto["foto"]) &&
        file_exists(
            "uploads/" . $dataFoto["foto"]
        )
    ) {

        $fotoProfil = $dataFoto["foto"];

    }
}


/* =====================================================
   TOTAL USER
===================================================== */

$totalUser = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users"
);

if ($result) {

    $data = $result->fetch_assoc();

    $totalUser = $data["total"];

}


/* =====================================================
   USER AKTIF
   Sementara semua user dianggap aktif
===================================================== */

$userAktif = $totalUser;


/* =====================================================
   TOTAL TASK
===================================================== */

$totalTask = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM tasks"
);

if ($result) {

    $data = $result->fetch_assoc();

    $totalTask = $data["total"];

}


/* =====================================================
   TASK SELESAI
===================================================== */

$taskSelesai = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM tasks
     WHERE status = 'completed'"
);

if ($result) {

    $data = $result->fetch_assoc();

    $taskSelesai = $data["total"];

}

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Dashboard Admin - Monitoring Kerja
    </title>


    <!-- CSS UTAMA -->

    <link
        rel="stylesheet"
        href="style.css?v=3"
    >

</head>


<body>


<div class="app">


    <!-- =================================================
         SIDEBAR
    ================================================= -->

    <aside class="sidebar">


        <!-- BRAND -->

        <div class="brand">

            <div class="brand-logo">
                M
            </div>

            <div class="brand-text">

                MONITORING<br>
                KERJA

            </div>

        </div>


        <!-- DASHBOARD -->

        <a
            href="dashboard_admin.php"
            class="menu active"
        >

            <span>
                ▣
            </span>

            Dashboard

        </a>


        <!-- TAMBAH USER -->

        <a
            href="users.php"
            class="menu"
        >

            <span>
                👥
            </span>

            Tambah User

        </a>


        <!-- LOGOUT -->

        <a
            href="logout.php"
            class="menu"
        >

            <span>
                ↪
            </span>

            Logout

        </a>


    </aside>


    <!-- =================================================
         CONTENT
    ================================================= -->

    <main class="content">


        <!-- =================================================
             TOPBAR
        ================================================= -->

        <header class="topbar">


            <!-- JUDUL -->

            <div>

                <h1>
                    Dashboard Admin
                </h1>

                <p>
                    Kelola dan pantau sistem Monitoring Kerja
                </p>

            </div>


            <!-- =================================================
                 PROFILE KANAN ATAS
            ================================================= -->

            <div class="profile-dropdown">


                <button
                    class="profile-button"
                    id="btnProfile"
                    type="button"
                >


                    <!-- FOTO PROFILE -->

                    <span class="profile-avatar">

                        <?php if ($fotoProfil): ?>

                            <img
                                src="uploads/<?= htmlspecialchars($fotoProfil) ?>"
                                alt="Foto Profil"
                                class="profile-avatar-img"
                            >

                        <?php else: ?>

                            <?= strtoupper(
                                substr($nama, 0, 1)
                            ) ?>

                        <?php endif; ?>

                    </span>


                    <!-- NAMA -->

                    <span>

                        <?= htmlspecialchars($nama) ?>

                    </span>


                    <!-- ARROW -->

                    <span>
                        ⌄
                    </span>


                </button>


                <!-- =================================================
                     DROPDOWN PROFILE
                ================================================= -->

                <div
                    class="profile-dropdown-menu"
                    id="profileMenu"
                >


                    <a href="edit_profil.php">

                        👤 Profile

                    </a>


                    <button
                        type="button"
                    >

                        ⚙ Settings

                    </button>


                    <button
                        type="button"
                    >

                        ☷ Activity Log

                    </button>


                    <hr>


                    <a href="logout.php">

                        ↪ Logout

                    </a>


                </div>


            </div>


        </header>


        <!-- =================================================
             DASHBOARD CONTENT
        ================================================= -->

        <section class="dashboard-content">


            <!-- WELCOME -->

            <div class="welcome-card">

                <h2>
                    Administrator
                </h2>

                <p>
                    Kelola pengguna, pekerjaan,
                    dan laporan sistem.
                </p>

            </div>


            <!-- =================================================
                 STATISTIK
            ================================================= -->

            <div class="dashboard-cards">


                <!-- TOTAL USER -->

                <div class="stat-card">

                    <div>

                        <span>
                            Total User
                        </span>

                        <strong>
                            <?= $totalUser ?>
                        </strong>

                        <small>
                            Pengguna sistem
                        </small>

                    </div>


                    <div class="stat-icon blue">
                        👥
                    </div>

                </div>


                <!-- USER AKTIF -->

                <div class="stat-card">

                    <div>

                        <span>
                            User Aktif
                        </span>

                        <strong>
                            <?= $userAktif ?>
                        </strong>

                        <small>
                            User terdaftar
                        </small>

                    </div>


                    <div class="stat-icon green">
                        ✓
                    </div>

                </div>


                <!-- TOTAL TASK -->

                <div class="stat-card">

                    <div>

                        <span>
                            Total Task
                        </span>

                        <strong>
                            <?= $totalTask ?>
                        </strong>

                        <small>
                            Semua pekerjaan
                        </small>

                    </div>


                    <div class="stat-icon cyan">
                        ☷
                    </div>

                </div>


                <!-- TASK SELESAI -->

                <div class="stat-card">

                    <div>

                        <span>
                            Task Selesai
                        </span>

                        <strong>
                            <?= $taskSelesai ?>
                        </strong>

                        <small>
                            Pekerjaan selesai
                        </small>

                    </div>


                    <div class="stat-icon green">
                        ✓
                    </div>

                </div>


            </div>


            <!-- =================================================
                 AKTIVITAS
            ================================================= -->

            <div class="dashboard-panel">


                <div class="panel-header">

                    <h2>
                        Aktivitas Terbaru
                    </h2>

                    <a href="users.php">
                        Lihat Semua
                    </a>

                </div>


                <div class="empty-task">

                    Belum ada aktivitas terbaru.

                </div>


            </div>


        </section>


    </main>


</div>



<!-- =====================================================
     MODAL PROFILE
===================================================== -->

<div
    class="profile-modal-overlay"
    id="profileModalOverlay"
>


    <div class="profile-modal">


        <!-- HEADER MODAL -->

        <div class="profile-modal-header">


            <h2>
                Profil Saya
            </h2>


            <button
                type="button"
                id="btnCloseProfileModal"
            >

                ×

            </button>


        </div>


        <!-- =================================================
             FOTO PROFILE BESAR
        ================================================= -->

        <div class="profile-modal-user">


            <div class="profile-big-avatar">


                <?php if ($fotoProfil): ?>

                    <img
                        src="uploads/<?= htmlspecialchars($fotoProfil) ?>"
                        alt="Foto Profil"
                        class="profile-big-avatar-img"
                    >

                <?php else: ?>

                    <?= strtoupper(
                        substr($nama, 0, 1)
                    ) ?>

                <?php endif; ?>


            </div>


            <div>

                <h3>

                    <?= htmlspecialchars($nama) ?>

                </h3>


                <p>
                    Admin
                </p>

            </div>


        </div>


        <!-- =================================================
             INFORMASI PROFILE
        ================================================= -->

        <div class="profile-info">


            <div>

                <span>
                    Nama
                </span>

                <strong>

                    <?= htmlspecialchars($nama) ?>

                </strong>

            </div>


            <div>

                <span>
                    Username
                </span>

                <strong>

                    <?= htmlspecialchars(
                        $_SESSION["username"] ?? "-"
                    ) ?>

                </strong>

            </div>


            <div>

                <span>
                    Email
                </span>

                <strong>

                    <?= htmlspecialchars(
                        $_SESSION["email"] ?? "-"
                    ) ?>

                </strong>

            </div>


            <div>

                <span>
                    Role
                </span>

                <strong>
                    Admin
                </strong>

            </div>


        </div>


        <!-- TOMBOL TUTUP -->

        <button
            type="button"
            class="profile-close-button"
            id="btnCloseProfileModal2"
        >

            Tutup

        </button>


    </div>


</div>



<!-- =====================================================
     JAVASCRIPT UTAMA
===================================================== -->

<script src="script.js"></script>


</body>

</html>