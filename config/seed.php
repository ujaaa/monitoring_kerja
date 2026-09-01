<?php
/**
 * Seed data dummy — jalankan: php config/seed.php
 * Hanya CLI. Membuat user + task contoh agar dashboard terisi.
 */

if (PHP_SAPI !== "cli") {
    http_response_code(403);
    exit("Akses ditolak. Jalankan dari CLI: php config/seed.php\n");
}

$host = "127.0.0.1";
$user = "root";
$password = "";
$port = 3306;

$conn = new mysqli($host, $user, $password, "monitoring_kerja", $port);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error . "\n");
}

/* ---------- USERS ---------- */
$users = [
    ["Admin", "admin", "admin@kerja.id", "admin123", "admin"],
    ["Sari", "sari", "sari@kerja.id", "sari123", "supervisor"],
    ["Budi", "budi", "budi@kerja.id", "budi123", "supervisor"],
    ["Dewi", "dewi", "dewi@kerja.id", "dewi123", "user"],
    ["Raka", "raka", "raka@kerja.id", "raka123", "user"],
    ["Maya", "maya", "maya@kerja.id", "maya123", "user"],
];

$ids = [];
foreach ($users as [$nama, $username, $email, $pass, $role]) {
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT IGNORE INTO users (nama, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nama, $username, $email, $hash, $role);
    $stmt->execute();
    $id = (int) $conn->insert_id;
    if ($id === 0) {
        $stmt2 = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $stmt2->bind_param("s", $username);
        $stmt2->execute();
        $id = (int) $stmt2->get_result()->fetch_assoc()["id"];
    }
    $ids[$username] = $id;
    echo "User {$nama} ({$role}) id={$id}\n";
}

/* ---------- TASKS ---------- */
$today = new DateTimeImmutable("today");

$tasks = [
    // On going / in progress
    ["Laporan penjualan Q3", "Laporan penjualan triwulan ketiga.", "in_progress", "high", -3],
    ["Update website landing", "Perbarui konten halaman depan.", "in_progress", "medium", 5],
    ["Persiapan rapat mingguan", "Susun agenda dan materi rapat.", "in_progress", "high", 1],
    ["Testing fitur login", "Uji login, register, reset password.", "in_progress", "medium", 7],
    ["Desain mockup mobile", "Mockup halaman task untuk mobile.", "in_progress", "low", 10],

    // Pending / belum dikerjakan
    ["Audit keamanan sistem", "Cek SQL injection, XSS, CSRF.", "pending", "high", 14],
    ["Optimasi query database", "Percepat task_list dan task_count.", "pending", "medium", 12],
    ["Dokumentasi API", "Tulis dokumentasi endpoint.", "pending", "low", 21],
    ["Backup database rutin", "Setup cron backup harian.", "pending", "high", 3],
    ["Review pull request", "Review 3 PR yang pending.", "pending", "medium", 4],

    // Completed
    ["Setup hosting production", "Deploy ke VPS.", "completed", "high", -10],
    ["Install SSL certificate", "Let's Encrypt untuk domain.", "completed", "high", -8],
    ["Konfigurasi firewall", "Buka hanya 80 dan 443.", "completed", "medium", -5],
    ["Migrasi database lama", "Pindah dari MySQL ke MariaDB.", "completed", "high", -15],
    ["Training tim", "Pelatihan penggunaan sistem.", "completed", "low", -20],

    // Mixed lagi
    ["Fix bug upload foto", "Foto tidak muncul di profil.", "in_progress", "high", 2],
    ["Tambah pagination", "List task harus paginated.", "pending", "medium", 8],
    ["Export laporan CSV", "User bisa download laporan.", "pending", "low", 15],
    ["Notifikasi email", "Kirim email saat task ditugaskan.", "pending", "medium", 18],
    ["Integrasi WhatsApp", "Notifikasi via WhatsApp API.", "pending", "high", 25],
];

$userList = array_values($ids);

foreach ($tasks as $i => [$title, $desc, $status, $priority, $dayOffset]) {
    $deadline = $today->modify("+{$dayOffset} days")->format("Y-m-d");
    $assignedTo = $userList[array_rand($userList)];
    $assignedBy = $ids["admin"];

    $stmt = $conn->prepare("INSERT INTO tasks (title, description, assigned_to, assigned_by, priority, status, deadline) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissss", $title, $desc, $assignedTo, $assignedBy, $priority, $status, $deadline);
    $stmt->execute();
    echo "Task {$title} -> {$status} ({$priority}) deadline {$deadline}\n";
}

echo "\nSelesai. " . count($tasks) . " task dibuat.\n";
