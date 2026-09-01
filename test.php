<?php

require_once "koneksi.php";

$username_test = "user";
$password_input = "123";  // GANTI dengan password yang Anda ketik saat login

$stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
$stmt->bind_param("s", $username_test);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "User tidak ditemukan.";
    exit;
}

$user = $result->fetch_assoc();

echo "Hash di database: " . $user['password'] . "<br>";
echo "Panjang hash: " . strlen($user['password']) . " karakter<br><br>";

if (password_verify($password_input, $user['password'])) {
    echo "✅ COCOK - password benar";
} else {
    echo "❌ TIDAK COCOK";
}

?>