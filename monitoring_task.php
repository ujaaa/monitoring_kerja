<?php

session_start();

if (
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "supervisor"
) {
    header("Location: index.php");
    exit;
}

$nama = $_SESSION["nama"] ?? "Supervisor";

require_once "koneksi.php";

// Ambil foto profil
$fotoProfil = "";
$stmtFoto = $conn->prepare("SELECT foto FROM users WHERE id = ?");
$stmtFoto->bind_param("i", $_SESSION["id"]);
$stmtFoto->execute();
$rowFoto = $stmtFoto->get_result()->fetch_assoc();
if ($rowFoto && !empty($rowFoto["foto"]) && file_exists("uploads/" . $rowFoto["foto"])) {
    $fotoProfil = $rowFoto["foto"];
}

// Filter status & user (opsional lewat query string)
$filterStatus = $_GET["status"] ?? "";
$filterUser   = $_GET["user"] ?? "";

// Ambil daftar user yang punya task (buat isi dropdown filter)
$daftarUser = $conn->query("
    SELECT DISTINCT users.id, users.nama
    FROM users
    INNER JOIN tasks ON tasks.assigned_to = users.id
    ORDER BY users.nama ASC
");

$sql = "
    SELECT
        tasks.id,
        tasks.title,
        tasks.description,
        tasks.priority,
        tasks.status,
        tasks.deadline,
        assignee.nama AS nama_assignee,
        assigner.nama AS nama_assigner
    FROM tasks
    LEFT JOIN users AS assignee ON tasks.assigned_to = assignee.id
    LEFT JOIN users AS assigner ON tasks.assigned_by = assigner.id
    WHERE 1 = 1
";

$params = [];
$types  = "";

if ($filterStatus !== "") {
    $sql .= " AND tasks.status = ?";
    $params[] = $filterStatus;
    $types .= "s";
}

if ($filterUser !== "") {
    $sql .= " AND tasks.assigned_to = ?";
    $params[] = $filterUser;
    $types .= "i";
}

$sql .= " ORDER BY tasks.deadline ASC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$tasks = $stmt->get_result();

// Ringkasan jumlah per status (untuk kartu statistik)
$countAll = $conn->query("SELECT COUNT(*) AS total FROM tasks")->fetch_assoc()["total"];
$countPending = $conn->query("SELECT COUNT(*) AS total FROM tasks WHERE status = 'pending'")->fetch_assoc()["total"];
$countProgress = $conn->query("SELECT COUNT(*) AS total FROM tasks WHERE status = 'in_progress'")->fetch_assoc()["total"];
$countDone = $conn->query("SELECT COUNT(*) AS total FROM tasks WHERE status = 'completed'")->fetch_assoc()["total"];

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Monitoring Pekerjaan</title>

    <link rel="stylesheet" href="style.css?v=2">
    <link rel="stylesheet" href="monitoring_task.css">

</head>

<body>

<div class="app">

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <div class="brand">
            <div class="brand-logo">M</div>
            <div class="brand-text">MONITORING<br>KERJA</div>
        </div>

        <a href="dashboard_supervisor.php" class="menu">
            <span>▣</span>
            Dashboard
        </a>

        <a href="monitoring_task.php" class="menu active">
            <span>✓</span>
            Monitoring Pekerjaan
        </a>

        <a href="setting.php" class="menu">
            <span>⚙</span>
            Pengaturan
        </a>

        <a href="logout.php" class="menu logout">
            <span>↪</span>
            Logout
        </a>

    </aside>

    <!-- CONTENT -->
    <main class="content">

        <!-- TOPBAR -->
        <header class="topbar">

            <div>
                <h1>Monitoring Pekerjaan</h1>
                <p>Pantau progres seluruh pekerjaan tim.</p>
            </div>

            <div class="profile-dropdown">

                <button class="profile-button" onclick="toggleProfile()">

                    <span class="profile-avatar">

                        <?php if ($fotoProfil): ?>
                            <img src="uploads/<?= htmlspecialchars($fotoProfil) ?>"
                                 alt="Foto Profil"
                                 class="profile-avatar-img">
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


        <!-- RINGKASAN -->
        <div class="dashboard-cards">

            <div class="stat-card">
                <div>
                    <span>Total Pekerjaan</span>
                    <strong><?= $countAll ?></strong>
                    <small>Semua task</small>
                </div>
                <div class="stat-icon blue">☷</div>
            </div>

            <div class="stat-card">
                <div>
                    <span>Belum Dikerjakan</span>
                    <strong><?= $countPending ?></strong>
                    <small>Status pending</small>
                </div>
                <div class="stat-icon orange">⏱</div>
            </div>

            <div class="stat-card">
                <div>
                    <span>Sedang Dikerjakan</span>
                    <strong><?= $countProgress ?></strong>
                    <small>In progress</small>
                </div>
                <div class="stat-icon cyan">⟳</div>
            </div>

            <div class="stat-card">
                <div>
                    <span>Selesai</span>
                    <strong><?= $countDone ?></strong>
                    <small>Task selesai</small>
                </div>
                <div class="stat-icon green">✓</div>
            </div>

        </div>


        <!-- MONITORING PANEL -->
        <div class="task-page">

            <div class="welcome-card">

                <div class="monitoring-header-row">

                    <div>
                        <h2>Daftar Pekerjaan</h2>
                        <p>Lihat siapa mengerjakan apa, dan progresnya sampai mana.</p>
                    </div>

                    <div class="monitoring-filter">

                        <select id="filterStatus" onchange="filterTask()">
                            <option value="" <?= $filterStatus === "" ? "selected" : "" ?>>Semua Status</option>
                            <option value="pending" <?= $filterStatus === "pending" ? "selected" : "" ?>>Pending</option>
                            <option value="in_progress" <?= $filterStatus === "in_progress" ? "selected" : "" ?>>In Progress</option>
                            <option value="completed" <?= $filterStatus === "completed" ? "selected" : "" ?>>Completed</option>
                        </select>

                        <select id="filterUser" onchange="filterTask()">

                            <option value="" <?= $filterUser === "" ? "selected" : "" ?>>Semua User</option>

                            <?php while ($u = $daftarUser->fetch_assoc()): ?>

                                <option
                                    value="<?= $u["id"] ?>"
                                    <?= $filterUser == $u["id"] ? "selected" : "" ?>
                                >
                                    <?= htmlspecialchars($u["nama"]) ?>
                                </option>

                            <?php endwhile; ?>

                        </select>

                        <input
                            type="text"
                            id="searchTask"
                            placeholder="Cari pekerjaan..."
                            onkeyup="searchTask()"
                        >

                    </div>

                </div>

                <div class="table-wrap">

                    <table class="task-table" id="taskTable">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Pekerjaan</th>
                                <th>Deskripsi</th>
                                <th>Dikerjakan Oleh</th>
                                <th>Diberikan Oleh</th>
                                <th>Prioritas</th>
                                <th>Status</th>
                                <th>Deadline</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($tasks->num_rows === 0): ?>

                                <tr>
                                    <td colspan="8" class="table-empty">
                                        Belum ada pekerjaan yang tercatat.
                                    </td>
                                </tr>

                            <?php else: ?>

                                <?php $no = 1; ?>

                                <?php while ($row = $tasks->fetch_assoc()): ?>

                                    <tr>

                                        <td><?= $no++ ?></td>

                                        <td><?= htmlspecialchars($row["title"]) ?></td>

                                        <td><?= htmlspecialchars($row["description"]) ?></td>

                                        <td>
                                            <?= htmlspecialchars($row["nama_assignee"] ?? "-") ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row["nama_assigner"] ?? "-") ?>
                                        </td>

                                        <td>
                                            <span class="badge badge-<?= htmlspecialchars($row["priority"]) ?>">
                                                <?= htmlspecialchars($row["priority"]) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <span class="badge badge-<?= htmlspecialchars($row["status"]) ?>">
                                                <?= htmlspecialchars(str_replace("_", " ", $row["status"])) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?= date("d M Y, H:i", strtotime($row["deadline"])) ?>
                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </main>

</div>

<script src="monitoring_task.js"></script>

<script>

function toggleProfile() {
    document.getElementById("profileDropdown").classList.toggle("show");
}

</script>

</body>
</html>