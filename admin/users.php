<?php
require_once __DIR__ . "/../shared/init.php";
require_once __DIR__ . "/../shared/auth.php";
require_once __DIR__ . "/../shared/flash.php";

require_role("admin");

function user_validate(array $input, mysqli $conn, ?int $exceptId = null): array
{
    $data = [
        "nama"     => trim($input["nama"] ?? ""),
        "username" => trim($input["username"] ?? ""),
        "email"    => trim($input["email"] ?? ""),
        "role"     => $input["role"] ?? "user",
    ];
    if ($data["nama"] === "" || $data["username"] === "") {
        return [false, "Nama dan username wajib diisi.", $data];
    }
    if ($data["email"] !== "" && !filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
        return [false, "Format email tidak valid.", $data];
    }
    if (!in_array($data["role"], ["user", "supervisor", "admin"], true)) {
        return [false, "Role tidak valid.", $data];
    }
    $sql = "SELECT id FROM users WHERE username = ?" . ($exceptId ? " AND id != ?" : "") . " LIMIT 1";
    $stmt = $conn->prepare($sql);
    if ($exceptId) {
        $stmt->bind_param("si", $data["username"], $exceptId);
    } else {
        $stmt->bind_param("s", $data["username"]);
    }
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()) {
        return [false, "Username sudah digunakan.", $data];
    }
    return [true, null, $data];
}

/* ================== AKSI POST ================== */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check();
    $action = $_POST["action"] ?? "";

    if ($action === "add") {
        [$ok, $err, $data] = user_validate($_POST, $conn);
        if ($ok) {
            $password = $_POST["password"] ?? "";
            if (strlen($password) < 4) {
                $err = "Password minimal 4 karakter.";
            }
        }
        if ($ok) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (nama, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $data["nama"], $data["username"], $data["email"], $hash, $data["role"]);
            $ok = $stmt->execute();
            $err = $ok ? null : "Gagal menambahkan user.";
            if ($ok) {
                set_flash("success", "User \"{$data["username"]}\" ditambahkan.");
            }
        }
        if ($err) {
            set_flash("error", $err);
        }
    }

    if ($action === "delete") {
        $id = (int) ($_POST["id"] ?? 0);
        if ($id === (int) $_SESSION["id"]) {
            set_flash("error", "Anda tidak bisa menghapus akun sendiri.");
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $ok = $stmt->execute() && $stmt->affected_rows > 0;
            set_flash($ok ? "success" : "error", $ok ? "Akun user berhasil dihapus." : "User tidak ditemukan.");
        }
    }

    if ($action === "reset_password") {
        $rid = (int) ($_POST["id"] ?? 0);
        if ($rid === (int) $_SESSION["id"]) {
            set_flash("error", "Anda tidak bisa mereset password akun sendiri.");
        } elseif ($rid === 0) {
            set_flash("error", "User tidak ditemukan.");
        } else {
            $temp = bin2hex(random_bytes(4)); // 8 karakter heksadesimal
            $hash = password_hash($temp, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hash, $rid);
            $ok = $stmt->execute() && $stmt->affected_rows > 0;
            if ($ok) {
                $info = $conn->query("SELECT username FROM users WHERE id = $rid")->fetch_assoc();
                set_flash("success", "Password \"" . ($info["username"] ?? "user") . "\" direset menjadi: " . $temp . " (sampaikan ke user).");
            } else {
                set_flash("error", "User tidak ditemukan.");
            }
        }
    }

    header("Location: users.php");
    exit;
}

/* ================== DAFTAR + PENCARIAN ================== */
$q = trim($_GET["q"] ?? "");
if ($q !== "") {
    $like = "%" . $q . "%";
    $stmt = $conn->prepare("SELECT id, nama, username, email, role, created_at FROM users
                            WHERE nama LIKE ? OR username LIKE ? OR email LIKE ?
                            ORDER BY id DESC");
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $users = $conn->query("SELECT id, nama, username, email, role, created_at FROM users ORDER BY id DESC")
                  ->fetch_all(MYSQLI_ASSOC);
}

page_start("Kelola User", $q !== "" ? "Hasil pencarian \"" . $q . "\"" : "Tambah, ubah role, dan hapus akun.", "users");
?>
<section class="dashboard-panel">
    <div class="panel-header">
        <h2>Daftar User</h2>
        <button type="button" class="btn-simpan" id="btnTambahUser">+ Tambah User</button>
    </div>

    <form method="GET" class="monitor-filter">
        <input type="text" name="q" placeholder="Cari nama, username, atau email…" value="<?= htmlspecialchars($q) ?>">
        <button type="submit" class="btn-simpan">Cari</button>
        <?php if ($q !== ""): ?><a href="users.php" class="btn-batal">Reset</a><?php endif; ?>
    </form>

    <?php if (empty($users)): ?>
        <div class="empty-task">Tidak ada user yang cocok.</div>
    <?php else: ?>
        <table class="report-table admin-user-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($u["nama"]) ?></strong></td>
                        <td><?= htmlspecialchars($u["username"]) ?></td>
                        <td><?= htmlspecialchars($u["email"] ?? "-") ?></td>
                        <td>
                            <form method="POST" action="edit_role.php" class="role-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $u["id"] ?>">
                                <select name="role" onchange="this.form.submit()" aria-label="Ubah role <?= htmlspecialchars($u["username"]) ?>">
                                    <?php foreach (["user", "supervisor", "admin"] as $r): ?>
                                        <option value="<?= $r ?>" <?= $u["role"] === $r ? "selected" : "" ?>><?= role_label($r) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td class="task-actions">
                            <form method="POST" onsubmit="return confirm('Reset password user ini?')" class="role-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="reset_password">
                                <input type="hidden" name="id" value="<?= (int) $u["id"] ?>">
                                <button type="submit" class="btn-kecil" title="Reset sandi">Reset Sandi</button>
                            </form>
                            <form method="POST" onsubmit="return confirm('Hapus user ini? Semua task-nya ikut terhapus.')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $u["id"] ?>">
                                <button type="submit" class="btn-kecil btn-kecil-danger" <?= (int) $u["id"] === (int) $_SESSION["id"] ? "disabled title='Tidak bisa menghapus akun sendiri'" : "" ?>>Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<!-- MODAL TAMBAH USER -->
<div class="modal-overlay" id="modalTambahUser" aria-hidden="true">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h2>Tambah User</h2>
                <p>Buat akun baru untuk aplikasi.</p>
            </div>
            <button type="button" class="modal-close" id="btnTutupModal" aria-label="Tutup">×</button>
        </div>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" required>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required minlength="4">
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role">
                    <option value="user">User</option>
                    <option value="supervisor">Supervisor</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-batal" id="btnBatalModal">Batal</button>
                <button type="submit" class="btn-simpan">Tambah User</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("modalTambahUser");
    const open  = () => { modal.classList.add("open"); modal.setAttribute("aria-hidden", "false"); };
    const close = () => { modal.classList.remove("open"); modal.setAttribute("aria-hidden", "true"); };
    document.getElementById("btnTambahUser").addEventListener("click", open);
    document.getElementById("btnTutupModal").addEventListener("click", close);
    document.getElementById("btnBatalModal").addEventListener("click", close);
    modal.addEventListener("click", (e) => { if (e.target === modal) close(); });
    document.addEventListener("keydown", (e) => { if (e.key === "Escape" && modal.classList.contains("open")) close(); });
});
</script>

<?php page_end(); ?>

