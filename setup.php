<?php
$host = "127.0.0.1";
$user = "root";
$password = "";
$port = 3307;

$conn = new mysqli($host, $user, $password, "", $port);

if ($conn->connect_error) {
    die("Koneksi MySQL gagal: " . $conn->connect_error);
}

if (!$conn->query("CREATE DATABASE IF NOT EXISTS monitoring_kerja")) {
    die("Gagal membuat database: " . $conn->error);
}

$conn->select_db("monitoring_kerja");

/* USERS */
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'supervisor', 'admin') NOT NULL DEFAULT 'user',
    foto VARCHAR(255) NULL
)";
if (!$conn->query($sql)) {
    die("Gagal membuat tabel users: " . $conn->error);
}

/* Tambahkan kolom untuk database lama jika belum ada. */
$columns = [];
$res = $conn->query("SHOW COLUMNS FROM users");
while ($row = $res->fetch_assoc()) {
    $columns[] = $row["Field"];
}

if (!in_array("email", $columns, true)) {
    $conn->query("ALTER TABLE users ADD COLUMN email VARCHAR(150) NULL UNIQUE AFTER username");
}
if (!in_array("foto", $columns, true)) {
    $conn->query("ALTER TABLE users ADD COLUMN foto VARCHAR(255) NULL AFTER role");
}

/* TASKS */
$sql = "CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    assigned_to INT NOT NULL,
    assigned_by INT NULL,
    priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    status ENUM('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
    deadline DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tasks_assigned_to
        FOREIGN KEY (assigned_to) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_tasks_assigned_by
        FOREIGN KEY (assigned_by) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE
)";
if (!$conn->query($sql)) {
    die("Gagal membuat tabel tasks: " . $conn->error);
}

echo "<h2>Database Monitoring Kerja siap digunakan.</h2>";
echo "<p>Tabel <b>users</b> dan <b>tasks</b> sudah diperiksa/dibuat.</p>";
echo "<p><a href='index.php'>Kembali ke Login</a></p>";
?>