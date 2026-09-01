<?php

session_start();

require_once "koneksi.php";


/* ==========================================================
   CEK LOGIN
========================================================== */

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}


$userId = $_SESSION["id"];

$pesan = "";
$error = "";


/* ==========================================================
   AMBIL DATA USER
========================================================== */

$stmt = $conn->prepare(
    "SELECT id, nama, username, email, role, foto, created_at
     FROM users
     WHERE id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $userId);

$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();


if (!$user) {

    session_destroy();

    header("Location: index.php");

    exit;
}


/* ==========================================================
   PROSES SIMPAN PROFIL
========================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $nama = trim($_POST["nama"] ?? "");

    $username = trim($_POST["username"] ?? "");

    $email = trim($_POST["email"] ?? "");


    /* ======================================================
       VALIDASI
    ====================================================== */

    if ($nama === "") {

        $error = "Nama tidak boleh kosong.";

    }

    elseif ($username === "") {

        $error = "Username tidak boleh kosong.";

    }

    elseif ($email === "") {

        $error = "Email tidak boleh kosong.";

    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Format email tidak valid.";

    }


    /* ======================================================
       CEK USERNAME
    ====================================================== */

    if ($error === "") {

        $stmtUsername = $conn->prepare(
            "SELECT id
             FROM users
             WHERE username = ?
             AND id != ?
             LIMIT 1"
        );

        $stmtUsername->bind_param(
            "si",
            $username,
            $userId
        );

        $stmtUsername->execute();

        $cekUsername =
            $stmtUsername
            ->get_result()
            ->fetch_assoc();


        if ($cekUsername) {

            $error = "Username sudah digunakan.";

        }

    }


    /* ======================================================
       CEK EMAIL
    ====================================================== */

    if ($error === "") {

        $stmtEmail = $conn->prepare(
            "SELECT id
             FROM users
             WHERE email = ?
             AND id != ?
             LIMIT 1"
        );

        $stmtEmail->bind_param(
            "si",
            $email,
            $userId
        );

        $stmtEmail->execute();

        $cekEmail =
            $stmtEmail
            ->get_result()
            ->fetch_assoc();


        if ($cekEmail) {

            $error = "Email sudah digunakan.";

        }

    }


    /* ======================================================
       FOTO
    ====================================================== */

    $namaFotoBaru = $user["foto"];


    if (
        $error === "" &&
        isset($_FILES["foto"]) &&
        $_FILES["foto"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {


        $file = $_FILES["foto"];


        /* MAKSIMAL 2 MB */

        $maxSize = 2 * 1024 * 1024;


        if ($file["error"] !== UPLOAD_ERR_OK) {

            $error = "Foto gagal diupload.";

        }

        elseif ($file["size"] > $maxSize) {

            $error = "Ukuran foto maksimal 2 MB.";

        }

        else {


            /* CEK MIME */

            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            $mime =
                finfo_file(
                    $finfo,
                    $file["tmp_name"]
                );

            finfo_close($finfo);


            $mimeAllowed = [

                "image/jpeg" => "jpg",

                "image/png" => "png",

                "image/webp" => "webp"

            ];


            if (!isset($mimeAllowed[$mime])) {

                $error =
                    "Format foto harus JPG, PNG, atau WEBP.";

            }

            else {


                $extension =
                    $mimeAllowed[$mime];


                /* NAMA FOTO UNIK */

                $namaFotoBaru =
                    "user_" .
                    $userId .
                    "_" .
                    time() .
                    "." .
                    $extension;


                /* FOLDER */

                $folderUpload =
                    __DIR__ . "/uploads/";


                if (!is_dir($folderUpload)) {

                    mkdir(
                        $folderUpload,
                        0755,
                        true
                    );

                }


                $tujuan =
                    $folderUpload .
                    $namaFotoBaru;


                /* PINDAHKAN FOTO */

                if (
                    !move_uploaded_file(
                        $file["tmp_name"],
                        $tujuan
                    )
                ) {

                    $error =
                        "Foto gagal disimpan.";

                    $namaFotoBaru =
                        $user["foto"];

                }

            }

        }

    }


    /* ======================================================
       SIMPAN DATABASE
    ====================================================== */

    if ($error === "") {


        $stmtUpdate = $conn->prepare(
            "UPDATE users
             SET
                nama = ?,
                username = ?,
                email = ?,
                foto = ?
             WHERE id = ?"
        );


        $stmtUpdate->bind_param(
            "ssssi",
            $nama,
            $username,
            $email,
            $namaFotoBaru,
            $userId
        );


        if ($stmtUpdate->execute()) {


            /* =================================================
               HAPUS FOTO LAMA
            ================================================= */

            if (
                !empty($user["foto"]) &&
                $namaFotoBaru !== $user["foto"]
            ) {


                $fotoLama =
                    __DIR__ .
                    "/uploads/" .
                    $user["foto"];


                if (
                    file_exists($fotoLama) &&
                    is_file($fotoLama)
                ) {

                    @unlink($fotoLama);

                }

            }


            /* =================================================
               UPDATE SESSION
            ================================================= */

            $_SESSION["nama"] =
                $nama;

            $_SESSION["username"] =
                $username;

            $_SESSION["email"] =
                $email;

            $_SESSION["foto"] =
                $namaFotoBaru;


            $pesan =
                "Profil berhasil diperbarui.";


            /* =================================================
               AMBIL DATA TERBARU
            ================================================= */

            $stmtRefresh = $conn->prepare(
                "SELECT
                    id,
                    nama,
                    username,
                    email,
                    role,
                    foto,
                    created_at
                 FROM users
                 WHERE id = ?"
            );


            $stmtRefresh->bind_param(
                "i",
                $userId
            );


            $stmtRefresh->execute();


            $user =
                $stmtRefresh
                ->get_result()
                ->fetch_assoc();


        } else {

            $error =
                "Profil gagal diperbarui.";

        }

    }

}


/* ==========================================================
   FOTO
========================================================== */

$fotoAda = false;


if (
    !empty($user["foto"]) &&
    file_exists(
        __DIR__ .
        "/uploads/" .
        $user["foto"]
    )
) {

    $fotoAda = true;

}


/* ==========================================================
   HURUF AWAL
========================================================== */

$hurufAwal =
    strtoupper(
        substr(
            $user["nama"],
            0,
            1
        )
    );


/* ==========================================================
   ROLE
========================================================== */

$roleTampil =
    ucfirst(
        strtolower(
            $user["role"]
        )
    );


/* ==========================================================
   DASHBOARD
========================================================== */

if ($user["role"] === "admin") {

    $dashboard =
        "dashboard_admin.php";

}

elseif ($user["role"] === "supervisor") {

    $dashboard =
        "dashboard_supervisor.php";

}

else {

    $dashboard =
        "dashboard_user.php";

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
        Edit Profil - Monitoring Kerja
    </title>


    <link
        rel="stylesheet"
        href="style.css"
    >

    <link
        rel="stylesheet"
        href="profil.css"
    >

</head>


<body>


<div class="profile-page">


    <div class="edit-profile-card">


        <!-- JUDUL -->

        <div class="profile-heading">

            <h1>
                Edit Profil
            </h1>

            <p>
                Kelola informasi profil akun kamu.
            </p>

        </div>


        <!-- FORM -->

        <form
            action="profil.php"
            method="POST"
            enctype="multipart/form-data"
            id="profileForm"
        >


            <!-- FOTO -->

            <div class="photo-section">


                <div class="profile-photo-wrapper">


                    <?php if ($fotoAda): ?>

                        <img
                            src="uploads/<?= htmlspecialchars($user["foto"]) ?>?v=<?= time() ?>"
                            alt="Foto Profil"
                            id="previewFoto"
                            class="profile-photo"
                        >

                    <?php else: ?>

                        <div
                            id="previewFoto"
                            class="profile-photo profile-initial"
                        >

                            <?= htmlspecialchars($hurufAwal) ?>

                        </div>

                    <?php endif; ?>


                </div>


                <label
                    for="inputFoto"
                    class="change-photo-button"
                >

                    📷 Ganti Foto

                </label>


                <input
                    type="file"
                    name="foto"
                    id="inputFoto"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    hidden
                >


                <p class="photo-info">

                    JPG, PNG, atau WEBP. Maksimal 2MB.

                </p>


            </div>


            <div class="profile-divider"></div>


            <!-- NAMA -->

            <div class="form-group">

                <label for="nama">
                    Nama
                </label>

                <input
                    type="text"
                    id="nama"
                    name="nama"
                    value="<?= htmlspecialchars($user["nama"]) ?>"
                    required
                >

            </div>


            <!-- USERNAME -->

            <div class="form-group">

                <label for="username">
                    Username
                </label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?= htmlspecialchars($user["username"]) ?>"
                    required
                >

            </div>


            <!-- EMAIL -->

            <div class="form-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($user["email"]) ?>"
                    required
                >

            </div>


            <!-- ROLE -->

            <div class="form-group">

                <label>
                    Role
                </label>

                <input
                    type="text"
                    value="<?= htmlspecialchars($roleTampil) ?>"
                    disabled
                    class="input-disabled"
                >

            </div>


            <!-- BUTTON -->

            <div class="profile-actions">


                <a
                    href="<?= htmlspecialchars($dashboard) ?>"
                    class="btn-cancel"
                >

                    Batal

                </a>


                <button
                    type="submit"
                    class="btn-save"
                >

                    Simpan

                </button>


            </div>


        </form>


        <!-- PESAN -->

        <?php if ($pesan): ?>

            <div class="alert alert-success">

                ✓
                <?= htmlspecialchars($pesan) ?>

            </div>

        <?php endif; ?>


        <?php if ($error): ?>

            <div class="alert alert-error">

                ⚠
                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


    </div>


</div>


<script src="profil.js"></script>

</body>

</html>