<?php

$host = "127.0.0.1";
$user = "root";
$password = "";
$database = "monitoring_kerja";
$port = 3306; // 3306 XAMPP default, 3307 hanya jika custom

// Kredensial sebaiknya pindah ke environment variable di production:
//   $user = getenv("DB_USER") ?: "root";
//   $password = getenv("DB_PASS") ?: "";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $password, $database, $port);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    // Jangan bocorkan detail koneksi ke pengunjung.
    error_log("DB connection error: " . $e->getMessage());
    http_response_code(500);
    exit("Koneksi database gagal. Hubungi administrator.");
}
