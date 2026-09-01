<?php
/**
 * shared/layout.php — kerangka halaman bersama (sidebar + topbar + flash).
 * Halaman memanggil:
 *   page_start("Judul", "Subjudul", "task");   // key menu aktif
 *   ... konten halaman ...
 *   page_end();
 * $base disetel otomatis dari lokasi file pemanggil ("" di root, "../" di subfolder).
 */

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/flash.php";
require_once __DIR__ . "/user_view.php";

function layout_nav_map(): array
{
    return [
        "user" => [
            ["key" => "dashboard", "label" => "Dashboard",     "href" => "../user/dashboard_user.php"],
            ["key" => "task",      "label" => "Data Pekerjaan", "href" => "../user/task.php"],
            ["key" => "laporan",   "label" => "Laporan",       "href" => "../shared/laporan.php"],
            ["key" => "profil",    "label" => "Profil",        "href" => "../shared/profil.php"],
        ],
        "supervisor" => [
            ["key" => "dashboard", "label" => "Dashboard",    "href" => "../supervisor/dashboard_supervisor.php"],
            ["key" => "monitor",   "label" => "Monitoring",   "href" => "../supervisor/monitoring_task.php"],
            ["key" => "laporan",   "label" => "Laporan",      "href" => "../shared/laporan.php"],
            ["key" => "profil",    "label" => "Profil",       "href" => "../shared/profil.php"],
        ],
        "admin" => [
            ["key" => "dashboard", "label" => "Dashboard",    "href" => "../admin/dashboard_admin.php"],
            ["key" => "users",     "label" => "Kelola User",  "href" => "../admin/users.php"],
            ["key" => "laporan",   "label" => "Laporan",      "href" => "../shared/laporan.php"],
            ["key" => "profil",    "label" => "Profil",       "href" => "../shared/profil.php"],
        ],
    ];
}

function page_start(string $title, string $subtitle = "", string $activeKey = "", string $base = ""): void
{
    // Deteksi kedalaman folder otomatis jika pemanggil tidak mengisi $base.
    if ($base === "") {
        $dir = basename(dirname($_SERVER["SCRIPT_FILENAME"] ?? "."));
        $base = in_array($dir, ["admin", "user", "supervisor", "shared", "auth", "config", "tools"], true) ? "../" : "";
    }

    $role   = $_SESSION["role"] ?? "user";
    $nama   = $_SESSION["nama"] ?? "User";
    $email  = $_SESSION["email"] ?? "-";
    $items  = layout_nav_map()[$role] ?? layout_nav_map()["user"];
    $foto   = foto_url($_SESSION["foto"] ?? null, $base);
    $current = basename($_SERVER["SCRIPT_FILENAME"] ?? "");
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#3D4451">
    <link rel="icon" type="image/svg+xml" href="<?= $base ?>assets/favicon.svg">
    <title><?= htmlspecialchars($title) ?> — Monitoring Kerja</title>
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css?v=3">
</head>
<body>
<a href="#konten-utama" class="skip-link">Lompat ke konten utama</a>
<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-logo">M</div>
            <div class="brand-text">MONITORING<br>KERJA</div>
        </div>
        <nav class="sidebar-nav" aria-label="Menu utama">
            <?php foreach ($items as $item): ?>
                <?php
                    $isActive = $item["key"] === $activeKey
                        || in_array($current, ["edit_task.php"]) && $item["key"] === "task";
                    $href = str_replace("../", $base, $item["href"]);
                    $badge = "";
                    $roleNow = $_SESSION["role"] ?? "user";
                    if (function_exists("task_overdue_count") && in_array($item["key"], ["task", "monitor"], true)) {
                        $scoped = in_array($roleNow, ["supervisor", "admin"], true) ? null : (int) ($_SESSION["id"] ?? 0);
                        $overdue = (int) task_overdue_count($GLOBALS["conn"] ?? null, $scoped);
                        if ($overdue > 0) {
                            $badge = '<em class="nav-badge">' . $overdue . '</em>';
                        }
                    }
                ?>
                <a href="<?= htmlspecialchars($href) ?>" class="menu<?= $isActive ? " active" : "" ?>">
                    <span class="icon" aria-hidden="true"><?= layout_icon($item["key"]) ?></span>
                    <?= htmlspecialchars($item["label"]) ?><?= $badge ?>
                </a>
            <?php endforeach; ?>
            <a href="<?= $base ?>auth/logout.php" class="menu menu-logout">
                <span class="icon">↪</span>
                Keluar
            </a>
        </nav>
    </aside>

    <main class="content" id="konten-utama">
        <header class="topbar">
            <div>
                <h1><?= htmlspecialchars($title) ?></h1>
                <?php if ($subtitle !== ""): ?><p><?= htmlspecialchars($subtitle) ?></p><?php endif; ?>
            </div>
            <div class="profile-dropdown">
                <button class="profile-button" id="btnProfile" type="button"
                        aria-haspopup="true" aria-expanded="false" aria-controls="profileMenu">
                    <img class="profile-avatar" src="<?= htmlspecialchars($foto) ?>" alt="" width="30" height="30">
                    <span><?= htmlspecialchars($nama) ?></span>
                    <span class="caret" aria-hidden="true">▾</span>
                </button>
                <div class="profile-dropdown-menu" id="profileMenu">
                    <a href="<?= $base ?>shared/profil.php">Profil</a>
                    <a href="<?= $base ?>shared/laporan.php">Laporan</a>
                    <hr>
                    <a href="<?= $base ?>auth/logout.php">Keluar</a>
                </div>
            </div>
        </header>

        <div aria-live="polite"><?= render_flash() ?></div>
<?php
}

function page_end(): void
{
    $base = "";
    $dir = basename(dirname($_SERVER["SCRIPT_FILENAME"] ?? "."));
    $base = in_array($dir, ["admin", "user", "supervisor", "shared", "auth"], true) ? "../" : "";
    ?>
    </main>
</div>
<script src="<?= $base ?>assets/js/script.js"></script>
</body>
</html>
<?php
}

function layout_icon(string $key): string
{
    return match ($key) {
        "dashboard" => "▣",
        "task"      => "＋",
        "monitor"   => "◔",
        "users"     => "☰",
        "laporan"   => "▦",
        "profil"    => "◍",
        default     => "•",
    };
}
