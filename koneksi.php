<?php

$host = "127.0.0.1";
$user = "root";
$password = "";
$database = "monitoring_kerja";
$port = 3307;

$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

?>