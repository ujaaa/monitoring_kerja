<?php

session_start();

require_once "koneksi.php";


/* =====================================================
   AMBIL INPUT LOGIN
===================================================== */

$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";


/* =====================================================
   CEK INPUT
===================================================== */

if ($username === "" || $password === "") {

    header("Location: index.php?error=1");
    exit;

}


/* =====================================================
   CARI USER
===================================================== */

$stmt = $conn->prepare(
    "SELECT
        id,
        nama,
        username,
        email,
        password,
        role,
        foto
     FROM users
     WHERE username = ?
     LIMIT 1"
);


$stmt->bind_param(
    "s",
    $username
);


$stmt->execute();


$result = $stmt->get_result();


/* =====================================================
   USER TIDAK DITEMUKAN
===================================================== */

if ($result->num_rows !== 1) {

    header("Location: index.php?error=1");
    exit;

}


$user = $result->fetch_assoc();


/* =====================================================
   CEK PASSWORD
===================================================== */

if (!password_verify(
    $password,
    $user["password"]
)) {

    header("Location: index.php?error=1");
    exit;

}


/* =====================================================
   BUAT SESSION
===================================================== */

session_regenerate_id(true);


$_SESSION["id"] = $user["id"];

$_SESSION["nama"] = $user["nama"];

$_SESSION["username"] = $user["username"];

$_SESSION["email"] = $user["email"];

$_SESSION["role"] = $user["role"];


/*
    SIMPAN FOTO KE SESSION

    Kalau foto kosong/null,
    otomatis menggunakan default.png
*/

$_SESSION["foto"] =
    !empty($user["foto"])
        ? $user["foto"]
        : "default.png";


/* =====================================================
   REDIRECT BERDASARKAN ROLE
===================================================== */

switch ($user["role"]) {


    /* ================= USER ================= */

    case "user":

        header(
            "Location: dashboard_user.php"
        );

        break;


    /* ================= SUPERVISOR ================= */

    case "supervisor":

        header(
            "Location: dashboard_supervisor.php"
        );

        break;


    /* ================= ADMIN ================= */

    case "admin":

        header(
            "Location: dashboard_admin.php"
        );

        break;


    /* ================= ROLE TIDAK VALID ================= */

    default:

        session_destroy();

        header(
            "Location: index.php?error=1"
        );

        break;

}


exit;

?>