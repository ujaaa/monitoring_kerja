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
   AMBIL PROFIL ADMIN YANG SEDANG LOGIN
===================================================== */

$idLogin = $_SESSION["id"] ?? 0;

$stmtProfil = $conn->prepare("
    SELECT nama, username, email, role, foto
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmtProfil->bind_param("i", $idLogin);
$stmtProfil->execute();

$dataProfil = $stmtProfil->get_result()->fetch_assoc();


if ($dataProfil) {

    $nama     = $dataProfil["nama"];
    $username = $dataProfil["username"];
    $email    = $dataProfil["email"];
    $foto     = $dataProfil["foto"] ?? "";

    /* UPDATE SESSION DENGAN DATA TERBARU */
    $_SESSION["nama"]     = $nama;
    $_SESSION["username"] = $username;
    $_SESSION["email"]    = $email;
    $_SESSION["foto"]     = $foto;

} else {

    $nama     = "Admin";
    $username = "-";
    $email    = "-";
    $foto     = "";
}


/* =====================================================
   PROSES TAMBAH USER
===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    /* =================================================
       TAMBAH USER
    ================================================= */

    if (isset($_POST["tambah_user"])) {

        $nama_user = trim($_POST["nama"]);
        $username  = trim($_POST["username"]);
        $email     = trim($_POST["email"]);
        $password  = $_POST["password"];
        $role      = $_POST["role"];


        if (
            empty($nama_user) ||
            empty($username) ||
            empty($email) ||
            empty($password) ||
            empty($role)
        ) {

            echo "<script>
                    alert('Semua data wajib diisi!');
                    window.location.href='users.php';
                  </script>";

            exit;
        }


        $allowedRoles = [
            "user",
            "supervisor",
            "admin"
        ];


        if (!in_array($role, $allowedRoles)) {

            echo "<script>
                    alert('Role tidak valid!');
                    window.location.href='users.php';
                  </script>";

            exit;
        }


        /* CEK USERNAME DAN EMAIL */

        $cek = $conn->prepare("
            SELECT id
            FROM users
            WHERE username = ?
            OR email = ?
            LIMIT 1
        ");

        $cek->bind_param(
            "ss",
            $username,
            $email
        );

        $cek->execute();

        $hasil = $cek->get_result();


        if ($hasil->num_rows > 0) {

            echo "<script>
                    alert('Username atau email sudah digunakan!');
                    window.location.href='users.php';
                  </script>";

            exit;
        }


        /* HASH PASSWORD */

        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );


        /* SIMPAN USER */

        $stmt = $conn->prepare("
            INSERT INTO users
            (
                nama,
                username,
                email,
                password,
                role
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "sssss",
            $nama_user,
            $username,
            $email,
            $passwordHash,
            $role
        );


        if ($stmt->execute()) {

            echo "<script>
                    alert('User berhasil ditambahkan!');
                    window.location.href='users.php';
                  </script>";

            exit;

        } else {

            echo "<script>
                    alert('User gagal ditambahkan!');
                    window.location.href='users.php';
                  </script>";

            exit;
        }

    }


    /* =================================================
       HAPUS USER
    ================================================= */

    if (isset($_POST["hapus_user"])) {

        $id_hapus = intval($_POST["id"]);

        $id_admin = $_SESSION["id"] ?? 0;


        /* JANGAN BOLEH HAPUS DIRI SENDIRI */

        if ($id_hapus == $id_admin) {

            echo "<script>
                    alert('Kamu tidak bisa menghapus akun sendiri!');
                    window.location.href='users.php';
                  </script>";

            exit;
        }


        $stmtHapus = $conn->prepare("
            DELETE FROM users
            WHERE id = ?
        ");

        $stmtHapus->bind_param(
            "i",
            $id_hapus
        );


        if ($stmtHapus->execute()) {

            echo "<script>
                    alert('User berhasil dihapus!');
                    window.location.href='users.php';
                  </script>";

            exit;

        } else {

            echo "<script>
                    alert('User gagal dihapus!');
                    window.location.href='users.php';
                  </script>";

            exit;
        }

    }

}


/* =====================================================
   AMBIL DATA USER
===================================================== */

$users = $conn->query("
    SELECT
        id,
        nama,
        username,
        email,
        role,
        foto,
        created_at
    FROM users
    ORDER BY id DESC
");

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Kelola User</title>


    <link
        rel="stylesheet"
        href="style.css"
    >


    <style>

        /* =================================================
           FOTO PROFIL TOPBAR
        ================================================= */

        .profile-button .profile-avatar {

            width: 42px;
            height: 42px;
            min-width: 42px;

            border-radius: 50%;

            object-fit: cover;

            display: flex;

            align-items: center;

            justify-content: center;

            overflow: hidden;

        }


        .profile-button img.profile-avatar {

            width: 42px;
            height: 42px;

            object-fit: cover;

            border-radius: 50%;

            border: 2px solid #1263d6;

            background: #fff;

        }


        .profile-button .profile-initial-small {

            background: #eaf2ff;

            color: #1263d6;

            border: 2px solid #1263d6;

            font-size: 17px;

            font-weight: 600;

        }


        /* =================================================
           HEADER USER
        ================================================= */

        .user-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 20px;

        }


        .user-header h2 {

            margin: 0 0 6px;

        }


        .user-header p {

            margin: 0;

            color: #8a96a8;

            font-size: 13px;

        }


        /* =================================================
           BUTTON TAMBAH USER
        ================================================= */

        .btn-tambah-user {

            background: #1260d6;

            color: white;

            border: none;

            padding: 12px 18px;

            border-radius: 9px;

            font-size: 14px;

            font-weight: bold;

            cursor: pointer;

            transition: .2s;

        }


        .btn-tambah-user:hover {

            background: #0d4fb6;

        }


        /* =================================================
           AKSI USER
        ================================================= */

        .user-actions {

            display: flex;

            align-items: center;

            gap: 8px;

        }


        /* =================================================
           BUTTON EDIT
        ================================================= */

        .edit-role {

            display: inline-block;

            text-decoration: none;

            background: #e7efff;

            color: #1260d6;

            padding: 8px 13px;

            border-radius: 7px;

            font-size: 13px;

            font-weight: 600;

            transition: .2s;

        }


        .edit-role:hover {

            background: #1260d6;

            color: white;

        }


        /* =================================================
           BUTTON HAPUS
        ================================================= */

        .delete-user {

            border: none;

            background: #ffe5e5;

            color: #dc2626;

            padding: 8px 13px;

            border-radius: 7px;

            font-size: 13px;

            font-weight: 600;

            cursor: pointer;

            transition: .2s;

        }


        .delete-user:hover {

            background: #dc2626;

            color: white;

        }


        .form-hapus-user {

            margin: 0;

        }


        /* =================================================
           MODAL
        ================================================= */

        .modal-overlay {

            display: none;

            position: fixed;

            z-index: 9999;

            left: 0;

            top: 0;

            width: 100%;

            height: 100%;

            background: rgba(0, 0, 0, .4);

            align-items: center;

            justify-content: center;

        }


        .modal-overlay.show {

            display: flex;

        }


        .modal-box {

            width: 460px;

            max-width: 90%;

            background: white;

            border-radius: 16px;

            padding: 25px;

            box-shadow:
                0 20px 50px rgba(0,0,0,.2);

            animation: muncul .2s ease;

        }


        @keyframes muncul {

            from {

                transform: translateY(-15px);

                opacity: 0;

            }

            to {

                transform: translateY(0);

                opacity: 1;

            }

        }


        /* =================================================
           MODAL HEADER
        ================================================= */

        .modal-header {

            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            margin-bottom: 22px;

        }


        .modal-header h2 {

            margin: 0 0 5px;

            font-size: 21px;

        }


        .modal-header p {

            margin: 0;

            color: #8a96a8;

            font-size: 13px;

        }


        .modal-close {

            border: none;

            background: transparent;

            font-size: 27px;

            color: #777;

            cursor: pointer;

        }


        /* =================================================
           FORM
        ================================================= */

        .form-group {

            margin-bottom: 16px;

        }


        .form-group label {

            display: block;

            margin-bottom: 7px;

            font-size: 13px;

            font-weight: bold;

            color: #263247;

        }


        .form-group input,

        .form-group select {

            width: 100%;

            padding: 12px 13px;

            border: 1px solid #dce2eb;

            border-radius: 8px;

            outline: none;

            font-size: 14px;

            box-sizing: border-box;

        }


        .form-group input:focus,

        .form-group select:focus {

            border-color: #1260d6;

        }


        /* =================================================
           MODAL FOOTER
        ================================================= */

        .modal-footer {

            display: flex;

            justify-content: flex-end;

            gap: 10px;

            margin-top: 22px;

        }


        .btn-batal {

            padding: 11px 18px;

            background: white;

            color: #263247;

            border: 1px solid #dce2eb;

            border-radius: 8px;

            cursor: pointer;

        }


        .btn-simpan {

            padding: 11px 18px;

            background: #1260d6;

            color: white;

            border: none;

            border-radius: 8px;

            cursor: pointer;

            font-weight: bold;

        }


        .btn-simpan:hover {

            background: #0d4fb6;

        }

    </style>

</head>


<body>


<div class="app">


    <!-- SIDEBAR -->

    <aside class="sidebar">


        <div class="brand">

            <div class="brand-logo">
                M
            </div>

            <div class="brand-text">

                MONITORING<br>
                KERJA

            </div>

        </div>


        <a
            href="dashboard_admin.php"
            class="menu"
        >

            <span>▣</span>

            Dashboard

        </a>


        <a
            href="users.php"
            class="menu active"
        >

            <span>👥</span>

            Tambah User

        </a>


        <a
            href="logout.php"
            class="menu"
        >

            <span>↪</span>

            Logout

        </a>


    </aside>


    <!-- CONTENT -->

    <main class="content">


        <!-- TOPBAR -->

        <header class="topbar">


            <div>

                <h1>
                    Kelola User
                </h1>

                <p>
                    Kelola dan atur pengguna sistem
                </p>

            </div>


            <!-- PROFILE -->

            <div class="profile-dropdown">


                <button
                    class="profile-button"
                    id="btnProfile"
                    type="button"
                >


                    <?php if (
                        !empty($foto) &&
                        file_exists(
                            __DIR__ . "/uploads/" . $foto
                        )
                    ): ?>

                        <img
                            src="uploads/<?= htmlspecialchars($foto) ?>?v=<?= time() ?>"
                            alt="Foto Profil"
                            class="profile-avatar"
                        >

                    <?php else: ?>

                        <span
                            class="profile-avatar profile-initial-small"
                        >

                            <?= strtoupper(
                                substr($nama, 0, 1)
                            ) ?>

                        </span>

                    <?php endif; ?>


                    <span>

                        <?= htmlspecialchars($nama) ?>

                    </span>


                    <span>
                        ⌄
                    </span>


                </button>


                <div
                    class="profile-dropdown-menu"
                    id="profileMenu"
                >


                    <a href="edit_profil.php">

                        👤 Profile

                    </a>


                    <button type="button">

                        ⚙ Settings

                    </button>


                    <button type="button">

                        ☷ Activity Log

                    </button>


                    <hr>


                    <a href="logout.php">

                        ↪ Logout

                    </a>


                </div>


            </div>


        </header>


        <!-- USER CONTENT -->

        <section class="user-page">


            <div class="user-header">


                <div>

                    <h2>
                        Daftar Pengguna
                    </h2>

                    <p>
                        Daftar pengguna yang terdaftar dalam sistem
                    </p>

                </div>


                <button
                    type="button"
                    class="btn-tambah-user"
                    id="btnTambahUser"
                >

                    + Tambah User

                </button>


            </div>


            <!-- TABLE USER -->

            <div class="user-container">


                <table class="user-table">


                    <thead>

                        <tr>

                            <th>NO</th>

                            <th>NAMA</th>

                            <th>USERNAME</th>

                            <th>EMAIL</th>

                            <th>ROLE</th>

                            <th>AKSI</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    $no = 1;

                    while (
                        $user =
                        $users->fetch_assoc()
                    ):

                    ?>

                        <tr>

                            <td>

                                <?= $no++ ?>

                            </td>


                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $user["nama"]
                                    ) ?>

                                </strong>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $user["username"]
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $user["email"]
                                ) ?>

                            </td>


                            <td>

                                <span
                                    class="role-badge role-<?= strtolower(
                                        $user["role"]
                                    ) ?>"
                                >

                                    <?= htmlspecialchars(
                                        $user["role"]
                                    ) ?>

                                </span>

                            </td>


                            <td>

                                <div class="user-actions">


                                    <a
                                        href="edit_role.php?id=<?= $user["id"] ?>"
                                        class="edit-role"
                                    >

                                        Edit

                                    </a>


                                    <form
                                        method="POST"
                                        class="form-hapus-user"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= $user["id"] ?>"
                                        >


                                        <button
                                            type="submit"
                                            name="hapus_user"
                                            class="delete-user"
                                        >

                                            Hapus

                                        </button>

                                    </form>


                                </div>

                            </td>

                        </tr>


                    <?php endwhile; ?>


                    </tbody>

                </table>


            </div>


        </section>


    </main>


</div>


<!-- =====================================================
     MODAL TAMBAH USER
===================================================== -->

<div
    class="modal-overlay"
    id="modalTambahUser"
>


    <div class="modal-box">


        <div class="modal-header">

            <div>

                <h2>
                    Tambah User
                </h2>

                <p>
                    Tambahkan pengguna baru ke dalam sistem
                </p>

            </div>


            <button
                type="button"
                class="modal-close"
                id="btnTutupUser"
            >

                ×

            </button>

        </div>


        <form
            method="POST"
            autocomplete="off"
        >


            <div class="form-group">

                <label>
                    Nama Lengkap
                </label>

                <input
                    type="text"
                    name="nama"
                    placeholder="Contoh: Budi Santoso"
                    autocomplete="off"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Username
                </label>

                <input
                    type="text"
                    name="username"
                    placeholder="Contoh: budi"
                    autocomplete="off"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    placeholder="Contoh: budi@gmail.com"
                    autocomplete="off"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="Masukkan password"
                    autocomplete="new-password"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Role
                </label>

                <select
                    name="role"
                    required
                >

                    <option value="">
                        Pilih Role
                    </option>

                    <option value="user">
                        User
                    </option>

                    <option value="supervisor">
                        Supervisor
                    </option>

                    <option value="admin">
                        Admin
                    </option>

                </select>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn-batal"
                    id="btnBatalUser"
                >

                    Batal

                </button>


                <button
                    type="submit"
                    name="tambah_user"
                    class="btn-simpan"
                >

                    Simpan User

                </button>

            </div>


        </form>


    </div>

</div>


<script src="script.js"></script>


</body>

</html>