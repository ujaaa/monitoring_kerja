<?php
/**
 * shared/user_view.php — tampilan & file foto profil.
 * Satu sumber kebenaran untuk fallback foto dan upload yang aman.
 */

/**
 * URL foto profil. Fallback ke default.png jika file tidak ada.
 * $base = prefix path relatif dari halaman pemanggil ("../" atau "").
 */
function foto_url(?string $foto, string $base = "../"): string
{
    $default = $base . "uploads/default.svg";
    if (empty($foto)) {
        return $default;
    }
    // Blokir path traversal: hanya nama file polos.
    $safe = basename($foto);
    $path = dirname(__DIR__) . "/uploads/" . $safe;
    return is_file($path) ? $base . "uploads/" . $safe : $default;
}

/**
 * Pindahkan file upload foto profil ke uploads/ dengan aman.
 * Mengembalikan [ok(bool), error(string|null), filename(string|null)].
 */
function upload_foto(array $file): array
{
    if (($file["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [true, null, null]; // tidak ada file dikirim — bukan error
    }
    if ($file["error"] !== UPLOAD_ERR_OK) {
        return [false, "Upload gagal, coba lagi.", null];
    }
    if ($file["size"] > 2 * 1024 * 1024) {
        return [false, "Ukuran foto maksimal 2MB.", null];
    }

    $allowed = [
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp",
    ];
    $mime = mime_content_type($file["tmp_name"]);
    if (!isset($allowed[$mime])) {
        return [false, "Format harus JPG, PNG, atau WEBP.", null];
    }

    $dir = dirname(__DIR__) . "/uploads";
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    // Nama file digenerate sendiri (bukan dari input user) → tidak bisa XSS/traversal.
    $filename = "foto_" . bin2hex(random_bytes(8)) . "." . $allowed[$mime];
    if (!move_uploaded_file($file["tmp_name"], $dir . "/" . $filename)) {
        return [false, "Gagal menyimpan foto.", null];
    }
    return [true, null, $filename];
}
