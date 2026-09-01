<?php
/**
 * Test suite TaskRepository — jalankan: php tests/run_tests.php
 * Memakai database terpisah monitoring_kerja_test (aman untuk data asli).
 */

error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli("127.0.0.1", "root", "", "", 3306);
$conn->set_charset("utf8mb4");
$conn->query("DROP DATABASE IF EXISTS monitoring_kerja_test");
$conn->query("CREATE DATABASE monitoring_kerja_test");
$conn->select_db("monitoring_kerja_test");

$conn->query("CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','supervisor','admin') NOT NULL DEFAULT 'user',
    foto VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB");
$conn->query("CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    assigned_to INT NOT NULL,
    assigned_by INT NULL,
    priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    status ENUM('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
    deadline DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE CASCADE) ENGINE=InnoDB");

require_once dirname(__DIR__) . "/shared/TaskRepository.php";

$pass = 0;
$fail = 0;
function check(string $name, bool $cond): void
{
    global $pass, $fail;
    if ($cond) { $pass++; echo "  ok  $name\n"; }
    else       { $fail++; echo "FAIL  $name\n"; }
}

/* Seed 2 user */
$conn->query("INSERT INTO users (nama, username, password, role) VALUES ('Udin','udin','x','user')");
$udin = (int) $conn->insert_id;
$conn->query("INSERT INTO users (nama, username, password, role) VALUES ('Susi','susi','x','supervisor')");
$susi = (int) $conn->insert_id;

/* --- task_validate --- */
[$ok] = task_validate(["title" => "", "description" => "", "deadline" => ""]);
check("validate: tolak field kosong", !$ok);

[$ok] = task_validate(["title" => "A", "description" => "B", "deadline" => "2099-01-01", "priority" => "ekstrem"]);
check("validate: tolak priority di luar whitelist", !$ok);

[$ok, $err] = task_validate(["title" => "A", "description" => "B", "deadline" => "2001-01-01"], true);
check("validate: tolak deadline lampau saat create ($err)", !$ok && $err === "Deadline tidak boleh di masa lalu.");

[$ok] = task_validate(["title" => "A", "description" => "B", "deadline" => "2001-01-01"]);
check("validate: deadline lampau lolos saat edit (flag off)", $ok);

[$ok, , $data] = task_validate(["title" => "  A  ", "description" => "B", "deadline" => "2099-01-01", "priority" => "HIGH"]);
check("validate: priority di luar whitelist tetap ditolak (case-sensitive)", !$ok && $data["title"] === "A");

/* --- create / find --- */
[$ok] = task_create($conn, ["title" => "T1", "description" => "d", "deadline" => "2099-01-01", "priority" => "high", "status" => "pending"], $udin, $udin);
check("create: task baru sukses", $ok);

$t1 = task_find($conn, 1);
check("find: task ditemukan", $t1 !== null && $t1["title"] === "T1");

check("find: pemilik salah -> null", task_find($conn, 1, $susi) === null);

/* --- overdue & countdown --- */
$conn->query("INSERT INTO tasks (title, description, assigned_to, priority, status, deadline)
    VALUES ('Lewat', 'd', $udin, 'low', 'pending', '2001-01-01')");
$overdue = task_overdue_count($conn, $udin);
check("overdue: hitung task lewat deadline (1)", $overdue === 1);

[$cls] = task_countdown(["status" => "pending", "deadline" => date("Y-m-d")]);
check("countdown: hari ini -> today", $cls === "today");
[$cls] = task_countdown(["status" => "completed", "deadline" => "2001-01-01"]);
check("countdown: selesai -> done", $cls === "done");

/* --- list / count / pagination / filter --- */
for ($i = 1; $i <= 5; $i++) {
    task_create($conn, ["title" => "Bulk $i", "description" => "d", "deadline" => "2099-03-01", "priority" => "low", "status" => "in_progress"], $udin, $udin);
}
check("count: semua task udin (7)", task_count($conn, $udin) === 7);

$page = task_list($conn, $udin, [], 3, 3);
check("list: pagination limit 3 offset 3", count($page) === 3 && $page[0]["title"] === "Bulk 4");

check("count: filter pencarian 'Bulk 3'", task_count($conn, $udin, ["q" => "Bulk 3"]) === 1);

check("list: scoping pemilik (susi 0)", task_count($conn, $susi) === 0);

check("count: filter status in_progress", task_count($conn, $udin, ["status" => "in_progress"]) === 5);

/* --- update / delete scoping --- */
$ok = task_update($conn, 1, ["title" => "T1-edit", "description" => "d", "deadline" => "2099-01-02", "priority" => "low", "status" => "completed"], $susi);
check("update: pemilik salah -> tidak berubah", $ok === false || task_find($conn, 1)["title"] === "T1");

$ok = task_update($conn, 1, ["title" => "T1-edit", "description" => "d", "deadline" => "2099-01-02", "priority" => "low", "status" => "completed"], $udin);
check("update: pemilik benar -> berubah", $ok && task_find($conn, 1)["title"] === "T1-edit");

task_delete($conn, 1, $susi);
check("delete: pemilik salah -> tetap ada", task_find($conn, 1) !== null);
task_delete($conn, 1, $udin);
check("delete: pemilik benar -> terhapus", task_find($conn, 1) === null);

/* --- status counts --- */
$c = task_status_counts($conn, $udin);
check("counts: total & status konsisten", $c["total"] === 6 && $c["in_progress"] === 5);

echo "\n{$pass} lulus, {$fail} gagal\n";
exit($fail === 0 ? 0 : 1);
