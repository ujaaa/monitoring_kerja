<?php

session_start();

require_once "koneksi.php";

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

$id = $_SESSION["id"];

$stmt = $conn->prepare(
    "SELECT nama, username, email, foto
     FROM users
     WHERE id = ?"
);

$stmt->bind_param("i", $id);

$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama = trim($_POST["nama"]);
    $email = trim($_POST["email"]);

    $fotoBaru = $user["foto"] ?? "";

    /* =====================================================
       PROSES UPLOAD FOTO (JIKA ADA FILE DIPILIH)
    ===================================================== */

    if (
        isset($_FILES["foto"]) &&
        $_FILES["foto"]["error"] === UPLOAD_ERR_OK
    ) {

        $file = $_FILES["foto"];

        $allowedTypes = [
            "image/jpeg",
            "image/png",
            "image/webp",
        ];

        $maxSize = 2 * 1024 * 1024; // 2MB

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file["tmp_name"]);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {

            $error = "Format foto harus JPG, PNG, atau WEBP.";

        } elseif ($file["size"] > $maxSize) {

            $error = "Ukuran foto maksimal 2MB.";

        } else {

            $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
            $namaFile = "foto_" . $id . "_" . time() . "." . $ext;

            $folderUpload = __DIR__ . "/uploads/";

            if (!is_dir($folderUpload)) {
                mkdir($folderUpload, 0755, true);
            }

            if (
                move_uploaded_file(
                    $file["tmp_name"],
                    $folderUpload . $namaFile
                )
            ) {

                /* HAPUS FOTO LAMA (JIKA ADA) */

                if (
                    !empty($user["foto"]) &&
                    file_exists($folderUpload . $user["foto"])
                ) {
                    unlink($folderUpload . $user["foto"]);
                }

                $fotoBaru = $namaFile;

            } else {

                $error = "Gagal mengunggah foto.";

            }
        }
    }

    if ($error === "") {

        $update = $conn->prepare(
            "UPDATE users
             SET nama = ?, email = ?, foto = ?
             WHERE id = ?"
        );

        $update->bind_param(
            "sssi",
            $nama,
            $email,
            $fotoBaru,
            $id
        );

        $update->execute();

        $_SESSION["nama"] = $nama;
        $_SESSION["email"] = $email;
        $_SESSION["foto"] = $fotoBaru;

        header("Location: profil.php");

        exit;
    }
}

$adaFoto = (
    !empty($user["foto"]) &&
    file_exists(__DIR__ . "/uploads/" . $user["foto"])
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Edit Profil</title>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="profil.css">

    <style>

        .edit-profile-card .form-group {
            display: block !important;
            margin-bottom: 16px !important;
        }

        .edit-profile-card .form-group label {
            display: block !important;
            margin-bottom: 7px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            color: #26344d !important;
        }

        .edit-profile-card .form-group input {
            display: block !important;
            width: 100% !important;
            height: 40px !important;
            box-sizing: border-box !important;
            padding: 0 12px !important;
            border: 1px solid #dce3ed !important;
            border-radius: 8px !important;
            font-size: 13px !important;
        }

        .edit-profile-card .profile-actions {
            display: flex !important;
            gap: 10px !important;
            margin-top: 20px !important;
        }

        .edit-profile-card .profile-actions .btn-cancel,
        .edit-profile-card .profile-actions .btn-save {
            display: flex !important;
            width: 50% !important;
            height: 41px !important;
            align-items: center !important;
            justify-content: center !important;
            box-sizing: border-box !important;
            border-radius: 9px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
        }

    </style>

</head>

<body>

<div class="profile-page">

    <div class="edit-profile-card">

        <div class="profile-heading">
            <h1>Edit Profil</h1>
            <p>Perbarui informasi dan foto profil kamu.</p>
        </div>

        <?php if ($error !== ""): ?>

            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <div class="photo-section">

                <div class="profile-photo-wrapper">

                    <img
                        src="<?= $adaFoto ? 'uploads/' . htmlspecialchars($user["foto"]) : '' ?>"
                        alt="Foto Profil"
                        id="previewImg"
                        class="profile-photo"
                        style="display: <?= $adaFoto ? 'block' : 'none' ?>;"
                    >

                    <span
                        id="previewInisial"
                        class="profile-photo profile-initial"
                        style="display: <?= $adaFoto ? 'none' : 'flex' ?>;"
                    >
                        <?= strtoupper(substr($user["nama"], 0, 1)) ?>
                    </span>

                </div>

                <label
                    class="change-photo-button"
                    onclick="document.getElementById('fotoInput').click()"
                >
                    &#128247; Ganti Foto
                </label>

                <input
                    type="file"
                    name="foto"
                    id="fotoInput"
                    accept="image/jpeg,image/png,image/webp"
                    style="display: none;"
                >

                <p class="photo-info">
                    JPG, PNG, atau WEBP. Maks 2MB.
                </p>

            </div>

            <div class="profile-divider"></div>

            <div class="form-group">

                <label>Nama</label>

                <input
                    type="text"
                    name="nama"
                    value="<?= htmlspecialchars($user["nama"]) ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>Username</label>

                <input
                    type="text"
                    value="<?= htmlspecialchars($user["username"]) ?>"
                    class="input-disabled"
                    disabled
                >

            </div>

            <div class="form-group">

                <label>Email</label>

                <input
                    type="email"
                    name="email"
                    value="<?= htmlspecialchars($user["email"]) ?>"
                    required
                >

            </div>

            <div class="profile-actions">

                <a href="profil.php" class="btn-cancel">
                    Batal
                </a>

                <button type="submit" class="btn-save">
                    Simpan
                </button>

            </div>

        </form>

    </div>

</div>

<script src="edit_profil.js"></script>

</body>

</html>