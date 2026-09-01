<?php
require_once __DIR__ . "/../shared/init.php";
require_once __DIR__ . "/../shared/auth.php";
require_once __DIR__ . "/../shared/flash.php";
require_once __DIR__ . "/../shared/user_view.php";

require_login();

$userId = (int) $_SESSION["id"];

$user = $conn->prepare("SELECT id, nama, username, email, role, foto, created_at FROM users WHERE id = ? LIMIT 1");
$user->bind_param("i", $userId);
$user->execute();
$userRow = $user->get_result()->fetch_assoc();

if (!$userRow) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check();
    $action = $_POST["action"] ?? "";

    if ($action === "profil") {
        $nama     = trim($_POST["nama"] ?? "");
        $username = trim($_POST["username"] ?? "");
        $email    = trim($_POST["email"] ?? "");

        if ($nama === "" || $username === "") {
            set_flash("error", "Nama dan username wajib diisi.");
        } elseif ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash("error", "Format email tidak valid.");
        } else {
            // Upload foto (jika ada file baru).
            [$okUp, $errUp, $filename] = upload_foto($_FILES["foto"] ?? []);
            if (!$okUp) {
                set_flash("error", $errUp);
            } else {
                // Cek unik username/email.
                $cek = $conn->prepare("SELECT id FROM users WHERE (username = ? OR (? <> '' AND email = ?)) AND id != ? LIMIT 1");
                $cek->bind_param("sssi", $username, $email, $email, $userId);
                $cek->execute();
                if ($cek->get_result()->fetch_assoc()) {
                    set_flash("error", "Username atau email sudah digunakan.");
                } else {
                    if ($filename !== null) {
                        $stmt = $conn->prepare("UPDATE users SET nama = ?, username = ?, email = ?, foto = ? WHERE id = ?");
                        $stmt->bind_param("ssssi", $nama, $username, $email, $filename, $userId);
                    } else {
                        $stmt = $conn->prepare("UPDATE users SET nama = ?, username = ?, email = ? WHERE id = ?");
                        $stmt->bind_param("sssi", $nama, $username, $email, $userId);
                    }
                    if ($stmt->execute()) {
                        // Sinkronkan session.
                        $_SESSION["nama"] = $nama;
                        $_SESSION["username"] = $username;
                        $_SESSION["email"] = $email;
                        $_SESSION["foto"] = $filename ?? $userRow["foto"];
                        set_flash("success", "Profil berhasil diperbarui.");
                    } else {
                        set_flash("error", "Gagal menyimpan profil.");
                    }
                }
            }
        }
        header("Location: profil.php");
        exit;
    }

    if ($action === "password") {
        $lama  = $_POST["password_lama"] ?? "";
        $baru  = $_POST["password_baru"] ?? "";
        $ulang = $_POST["konfirmasi"] ?? "";

        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $hash = $stmt->get_result()->fetch_assoc()["password"] ?? "";

        if (!password_verify($lama, $hash)) {
            set_flash("error", "Password lama salah.");
        } elseif (strlen($baru) < 6) {
            set_flash("error", "Password baru minimal 6 karakter.");
        } elseif ($baru !== $ulang) {
            set_flash("error", "Konfirmasi password tidak sama.");
        } else {
            $newHash = password_hash($baru, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $newHash, $userId);
            $ok = $stmt->execute();
            set_flash($ok ? "success" : "error", $ok ? "Password berhasil diganti." : "Gagal mengganti password.");
        }
        header("Location: profil.php");
        exit;
    }
}

$roleTampil = role_label($userRow["role"]);
$dashboard  = dashboard_path($userRow["role"]);

page_start("Profil Saya", "Kelola data akun Anda.", "profil");
?>
<section class="dashboard-columns">
    <div class="profile-card">
        <h2>Data Akun</h2>
        <form method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="profil">

            <div class="profile-photo">
                <img class="profile-big-avatar" src="<?= htmlspecialchars(foto_url($userRow["foto"])) ?>" alt="Foto profil">
                <div>
                    <label class="btn-kecil" for="inputFoto">Ganti Foto</label>
                    <input type="file" name="foto" id="inputFoto" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" hidden>
                    <p class="photo-info">JPG, PNG, WEBP. Maksimal 2MB.</p>
                </div>
            </div>

            <div class="form-group">
                <label for="nama">Nama</label>
                <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($userRow["nama"]) ?>" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($userRow["username"]) ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($userRow["email"] ?? "") ?>">
            </div>
            <div class="form-group">
                <label>Role</label>
                <input type="text" value="<?= htmlspecialchars($roleTampil) ?>" disabled class="input-disabled">
            </div>
            <div class="profile-actions">
                <a href="<?= htmlspecialchars($dashboard) ?>" class="btn-cancel">Batal</a>
                <button type="submit" class="btn-save">Simpan Profil</button>
            </div>
        </form>
    </div>

    <div class="profile-card">
        <h2>Ganti Password</h2>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="password">
            <div class="form-group">
                <label for="password_lama">Password Lama</label>
                <input type="password" id="password_lama" name="password_lama" required>
            </div>
            <div class="form-group">
                <label for="password_baru">Password Baru</label>
                <input type="password" id="password_baru" name="password_baru" required minlength="6">
            </div>
            <div class="form-group">
                <label for="konfirmasi">Konfirmasi Password Baru</label>
                <input type="password" id="konfirmasi" name="konfirmasi" required minlength="6">
            </div>
            <div class="profile-actions">
                <button type="submit" class="btn-save">Ganti Password</button>
            </div>
        </form>
    </div>
</section>

<script src="../assets/js/profil.js"></script>

<?php page_end(); ?>

