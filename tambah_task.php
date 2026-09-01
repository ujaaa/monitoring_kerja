<?php

session_start();
require_once "koneksi.php";


/* =========================================================
   CEK LOGIN
========================================================= */

if (
    !isset($_SESSION["role"]) ||
    !in_array($_SESSION["role"], ["user", "admin"])
) {
    header("Location: index.php");
    exit;
}


$user_id = (int) $_SESSION["id"];
$nama = $_SESSION["nama"] ?? "User";

$error = "";


/* =========================================================
   SIMPAN PEKERJAAN
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $deadline = $_POST["deadline"] ?? "";
    $priority = $_POST["priority"] ?? "medium";
    $status = $_POST["status"] ?? "pending";


    if (
        $title === "" ||
        $description === "" ||
        $deadline === ""
    ) {

        $error = "Semua data pekerjaan harus diisi.";

    } else {


        $stmt = $conn->prepare("
            INSERT INTO tasks
            (
                title,
                description,
                assigned_to,
                assigned_by,
                priority,
                status,
                deadline
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");


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

            /*
             * SETELAH BERHASIL:
             * LANGSUNG KEMBALI KE DATA PEKERJAAN
             */

            header("Location: task.php");
            exit;

        } else {

            $error = "Gagal menyimpan pekerjaan.";

        }

    }

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

    <title>Tambah Kerjaan</title>

    <!-- CSS UTAMA (WAJIB, buat layout sidebar & topbar) -->
    <link
        rel="stylesheet"
        href="style.css"
    >

    <!-- CSS KHUSUS TASK -->
    <!-- <link
        rel="stylesheet"
        href="task.css?v=10"
    > -->

    <style>

        .form-card {
            background: white;
            border: 1px solid #e1e8f0;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(31,45,61,0.05);
        }

        .form-header {
            padding: 25px 28px;
            border-bottom: 1px solid #e8edf3;
        }

        .form-header h2 {
            font-size: 22px;
            margin-bottom: 5px;
            color: #102a43;
        }

        .form-header p {
            color: #718096;
            font-size: 14px;
        }

        .form-body {
            padding: 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 700;
            color: #243b53;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {

            width: 100%;

            border: 1px solid #d7e0ea;

            border-radius: 9px;

            padding: 13px 14px;

            font-family: inherit;

            font-size: 14px;

            color: #243b53;

            outline: none;

            background: white;

        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #1165d8;
            box-shadow: 0 0 0 3px rgba(17,101,216,0.08);
        }

        .form-group textarea {
            height: 140px;
            resize: vertical;
        }

        .form-footer {

            margin-top: 25px;

            padding-top: 20px;

            border-top: 1px solid #e8edf3;

            display: flex;

            justify-content: flex-end;

            gap: 12px;

        }

        .btn-batal {

            display: flex;

            align-items: center;

            justify-content: center;

            height: 44px;

            padding: 0 20px;

            border-radius: 9px;

            background: #eef1f4;

            color: #52606d;

            text-decoration: none;

            font-weight: 700;

            font-size: 14px;

        }

        .btn-simpan {

            border: none;

            height: 44px;

            padding: 0 22px;

            border-radius: 9px;

            background: #1165d8;

            color: white;

            font-weight: 700;

            font-size: 14px;

            cursor: pointer;

        }

        .error {

            background: #ffe5e7;

            color: #dc2626;

            padding: 12px 15px;

            border-radius: 8px;

            margin-bottom: 20px;

            font-size: 14px;

        }

        @media (max-width: 700px) {

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full {
                grid-column: auto;
            }

        }

    </style>

</head>


<body>


<div class="app">


    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

    <aside class="sidebar">

        <div class="brand">
            <div class="brand-logo">M</div>
            <div class="brand-text">
                MONITORING<br>
                KERJA
            </div>
        </div>

        <a href="dashboard_user.php" class="menu">
            <span>▣</span>
            Dashboard
        </a>

        <a href="tambah_task.php" class="menu active">
            <span>+</span>
            Tambah Kerjaan
        </a>

        <a href="logout.php" class="menu">
            <span>↪</span>
            Logout
        </a>

    </aside>



    <!-- =====================================================
         CONTENT
    ====================================================== -->

    <main class="content">


        <header class="topbar">


            <div>

                <h1>
                    Tambah Kerjaan
                </h1>

                <p>
                    Tambahkan pekerjaan baru yang ingin kamu pantau.
                </p>

            </div>


        </header>



        <!-- FORM -->

        <div class="form-card">


            <div class="form-header">

                <h2>
                    Tambah Data Pekerjaan
                </h2>

                <p>
                    Isi informasi pekerjaan yang akan ditambahkan.
                </p>

            </div>



            <div class="form-body">


                <?php if ($error): ?>

                    <div class="error">
                        <?= htmlspecialchars($error) ?>
                    </div>

                <?php endif; ?>



                <form
                    method="POST"
                    action=""
                >


                    <div class="form-grid">


                        <!-- NAMA -->

                        <div class="form-group">

                            <label>
                                Nama Pekerjaan
                            </label>

                            <input
                                type="text"
                                name="title"
                                placeholder="Contoh: Membuat laporan bulanan"
                                required
                            >

                        </div>



                        <!-- DEADLINE -->

                        <div class="form-group">

                            <label>
                                Deadline
                            </label>

                            <input
                                type="date"
                                name="deadline"
                                required
                            >

                        </div>



                        <!-- DESKRIPSI -->

                        <div class="form-group full">

                            <label>
                                Deskripsi Pekerjaan
                            </label>

                            <textarea
                                name="description"
                                placeholder="Jelaskan pekerjaan yang harus dilakukan..."
                                required
                            ></textarea>

                        </div>



                        <!-- PRIORITAS -->

                        <div class="form-group">

                            <label>
                                Prioritas
                            </label>

                            <select name="priority">

                                <option value="low">
                                    Rendah
                                </option>

                                <option
                                    value="medium"
                                    selected
                                >
                                    Sedang
                                </option>

                                <option value="high">
                                    Tinggi
                                </option>

                            </select>

                        </div>



                        <!-- STATUS -->

                        <div class="form-group">

                            <label>
                                Status
                            </label>

                            <select name="status">

                                <option
                                    value="pending"
                                    selected
                                >
                                    Belum Dikerjakan
                                </option>

                                <option value="in_progress">
                                    Sedang Dikerjakan
                                </option>

                                <option value="completed">
                                    Selesai
                                </option>

                            </select>

                        </div>


                    </div>



                    <!-- BUTTON -->

                    <div class="form-footer">


                        <a
                            href="tambah_task.php"
                            class="btn-batal"
                        >
                            Batal
                        </a>


                        <button
                            type="submit"
                            class="btn-simpan"
                        >
                            Simpan Pekerjaan
                        </button>


                    </div>


                </form>


            </div>


        </div>


    </main>


</div>


</body>

</html>