<?php
/**
 * shared/flash.php — satu idiom pesan untuk seluruh aplikasi.
 * Pola: set_flash("error", "...") lalu redirect; halaman tujuan
 * memanggil render_flash() di dalam layout.
 */
function set_flash(string $type, string $message): void
{
    $_SESSION["flash"] = ["type" => $type, "message" => $message];
}

function take_flash(): ?array
{
    if (!empty($_SESSION["flash"])) {
        $f = $_SESSION["flash"];
        unset($_SESSION["flash"]);
        return $f;
    }
    return null;
}

function render_flash(): string
{
    $f = take_flash();
    if (!$f) {
        return "";
    }
    $type = $f["type"] === "error" ? "error" : "success";
    $label = $type === "error" ? "ERROR" : "SUKSES";
    $message = htmlspecialchars($f["message"]);
    return '<div class="alert alert-' . $type . '" data-label="' . $label . '"><span class="alert-message">' . $message . "</span></div>";
}
