<?php
/**
 * shared/TaskRepository.php — satu-satunya tempat aplikasi menyentuh tabel tasks.
 * Interface: task_create / task_find / task_list / task_update / task_delete /
 * task_status_counts + map label & warna status/prioritas.
 */

const TASK_PRIORITIES  = ["low", "medium", "high"];
const TASK_STATUSES    = ["pending", "in_progress", "completed"];
const TASK_STATUS_LABELS = [
    "pending"     => "Belum Dikerjakan",
    "in_progress" => "Sedang Dikerjakan",
    "completed"   => "Selesai",
];
const TASK_STATUS_COLORS = [
    "pending"     => "orange",
    "in_progress" => "cyan",
    "completed"   => "green",
];
const TASK_PRIORITY_LABELS = [
    "low"    => "Rendah",
    "medium" => "Sedang",
    "high"   => "Tinggi",
];

/**
 * Validasi payload task. Mengembalikan [ok(bool), error(string|null), data(array)].
 */
function task_validate(array $input, bool $rejectPastDeadline = false): array
{
    $data = [
        "title"       => trim($input["title"] ?? ""),
        "description" => trim($input["description"] ?? ""),
        "deadline"    => trim($input["deadline"] ?? ""),
        "priority"    => $input["priority"] ?? "medium",
        "status"      => $input["status"] ?? "pending",
    ];

    if ($data["title"] === "" || $data["description"] === "" || $data["deadline"] === "") {
        return [false, "Nama pekerjaan, deskripsi, dan deadline wajib diisi.", $data];
    }
    if (!in_array($data["priority"], TASK_PRIORITIES, true)) {
        return [false, "Prioritas tidak valid.", $data];
    }
    if (!in_array($data["status"], TASK_STATUSES, true)) {
        return [false, "Status tidak valid.", $data];
    }
    $d = DateTime::createFromFormat("Y-m-d", $data["deadline"]);
    if (!$d || $d->format("Y-m-d") !== $data["deadline"]) {
        return [false, "Format deadline tidak valid.", $data];
    }
    if ($rejectPastDeadline && $d < new DateTimeImmutable("today")) {
        return [false, "Deadline tidak boleh di masa lalu.", $data];
    }
    return [true, null, $data];
}

/**
 * Buat task baru. $assignedTo = pemilik task, $assignedBy = pembuat.
 */
function task_create(mysqli $conn, array $data, int $assignedTo, int $assignedBy): array
{
    $stmt = $conn->prepare(
        "INSERT INTO tasks (title, description, assigned_to, assigned_by, priority, status, deadline)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    if (!$stmt) {
        return [false, "Gagal menyiapkan penyimpanan data."];
    }
    $stmt->bind_param(
        "ssiisss",
        $data["title"],
        $data["description"],
        $assignedTo,
        $assignedBy,
        $data["priority"],
        $data["status"],
        $data["deadline"]
    );
    if (!$stmt->execute()) {
        return [false, "Gagal menyimpan pekerjaan."];
    }
    return [true, null];
}

/**
 * Ambil satu task. Jika $assignedTo diberikan, task wajib milik user itu.
 */
function task_find(mysqli $conn, int $id, ?int $assignedTo = null): ?array
{
    $sql = "SELECT id, title, description, priority, status, deadline, assigned_to, assigned_by
            FROM tasks WHERE id = ?";
    if ($assignedTo !== null) {
        $sql .= " AND assigned_to = ?";
    }
    $sql .= " LIMIT 1";
    $stmt = $conn->prepare($sql);
    if ($assignedTo !== null) {
        $stmt->bind_param("ii", $id, $assignedTo);
    } else {
        $stmt->bind_param("i", $id);
    }
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ?: null;
}
/**
 * Daftar task dengan pagination.
 * $filters: status, priority, q (pencarian judul).
 * $limit 0 = tanpa batas; $offset mulai baris ke-n.
 */
function task_list(mysqli $conn, ?int $assignedTo = null, array $filters = [], int $limit = 0, int $offset = 0): array
{
    $sql = "SELECT tasks.*, users.nama AS nama_user
            FROM tasks LEFT JOIN users ON tasks.assigned_to = users.id
            WHERE 1=1";
    $types = "";
    $params = [];

    if ($assignedTo !== null) {
        $sql .= " AND tasks.assigned_to = ?";
        $types .= "i";
        $params[] = $assignedTo;
    }
    if (!empty($filters["status"]) && in_array($filters["status"], TASK_STATUSES, true)) {
        $sql .= " AND tasks.status = ?";
        $types .= "s";
        $params[] = $filters["status"];
    }
    if (!empty($filters["priority"]) && in_array($filters["priority"], TASK_PRIORITIES, true)) {
        $sql .= " AND tasks.priority = ?";
        $types .= "s";
        $params[] = $filters["priority"];
    }
    if (!empty($filters["q"])) {
        $sql .= " AND tasks.title LIKE ?";
        $types .= "s";
        $params[] = "%" . $filters["q"] . "%";
    }
    $sql .= " ORDER BY tasks.deadline ASC, tasks.id DESC";
    if ($limit > 0) {
        $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
    }

    $stmt = $conn->prepare($sql);
    if ($types !== "") {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Hitung jumlah task untuk filter yang sama (dipakai pagination).
 */
function task_count(mysqli $conn, ?int $assignedTo = null, array $filters = []): int
{
    $sql = "SELECT COUNT(*) AS c FROM tasks WHERE 1=1";
    $types = "";
    $params = [];

    if ($assignedTo !== null) {
        $sql .= " AND assigned_to = ?";
        $types .= "i";
        $params[] = $assignedTo;
    }
    if (!empty($filters["status"]) && in_array($filters["status"], TASK_STATUSES, true)) {
        $sql .= " AND status = ?";
        $types .= "s";
        $params[] = $filters["status"];
    }
    if (!empty($filters["priority"]) && in_array($filters["priority"], TASK_PRIORITIES, true)) {
        $sql .= " AND priority = ?";
        $types .= "s";
        $params[] = $filters["priority"];
    }
    if (!empty($filters["q"])) {
        $sql .= " AND title LIKE ?";
        $types .= "s";
        $params[] = "%" . $filters["q"] . "%";
    }

    $stmt = $conn->prepare($sql);
    if ($types !== "") {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return (int) $stmt->get_result()->fetch_assoc()["c"];
}

/**
 * Perbarui task. $assignedTo null = tanpa cek kepemilikan.
 */
function task_update(mysqli $conn, int $id, array $data, ?int $assignedTo = null): bool
{
    $sql = "UPDATE tasks SET title = ?, description = ?, priority = ?, status = ?, deadline = ?
            WHERE id = ?";
    $types = "sssssi";
    $params = [$data["title"], $data["description"], $data["priority"], $data["status"], $data["deadline"], $id];
    if ($assignedTo !== null) {
        $sql .= " AND assigned_to = ?";
        $types .= "i";
        $params[] = $assignedTo;
    }
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        return false;
    }
    // Update tidak mengubah baris (scope salah / tidak ditemukan) = gagal.
    return $stmt->affected_rows > 0;
}

/**
 * Hapus task. $assignedTo null hanya untuk admin (hapus task siapa pun).
 */
function task_delete(mysqli $conn, int $id, ?int $assignedTo = null): bool
{
    $sql = "DELETE FROM tasks WHERE id = ?";
    $types = "i";
    $params = [$id];
    if ($assignedTo !== null) {
        $sql .= " AND assigned_to = ?";
        $types .= "i";
        $params[] = $assignedTo;
    }
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        return false;
    }
    // Delete tidak menghapus baris (scope salah / tidak ditemukan) = gagal.
    return $stmt->affected_rows > 0;
}

/**
 * Selisih hari deadline vs hari ini untuk task yang belum selesai.
 * Mengembalikan [class(late|today|later|done), teks relatif].
 */
function task_countdown(array $task): array
{
    if ($task["status"] === "completed") {
        return ["done", "Selesai"];
    }
    $today    = new DateTimeImmutable("today");
    $deadline = new DateTimeImmutable($task["deadline"]);
    $diff     = (int) $today->diff($deadline)->format("%r%a");

    if ($diff < 0) {
        return ["late", abs($diff) . " hari lewat"];
    }
    if ($diff === 0) {
        return ["today", "Hari ini"];
    }
    return ["later", $diff . " hari lagi"];
}

/**
 * Hitungan task terlambat (belum selesai & deadline terlewat).
 */
function task_overdue_count(mysqli $conn, ?int $assignedTo = null): int
{
    $sql = "SELECT COUNT(*) AS c FROM tasks
            WHERE status != 'completed' AND deadline < CURDATE()";
    if ($assignedTo !== null) {
        $sql .= " AND assigned_to = ?";
    }
    $stmt = $conn->prepare($sql);
    if ($assignedTo !== null) {
        $stmt->bind_param("i", $assignedTo);
    }
    $stmt->execute();
    return (int) $stmt->get_result()->fetch_assoc()["c"];
}

/**
 * Hitungan per status. $assignedTo null = semua task.
 * Mengembalikan ["total" => n, "pending" => n, "in_progress" => n, "completed" => n].
 */
function task_status_counts(mysqli $conn, ?int $assignedTo = null): array
{
    $counts = ["total" => 0, "pending" => 0, "in_progress" => 0, "completed" => 0];
    $sql = "SELECT status, COUNT(*) AS c FROM tasks";
    if ($assignedTo !== null) {
        $sql .= " WHERE assigned_to = ?";
    }
    $sql .= " GROUP BY status";
    $stmt = $conn->prepare($sql);
    if ($assignedTo !== null) {
        $stmt->bind_param("i", $assignedTo);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $counts[$row["status"]] = (int) $row["c"];
        $counts["total"] += (int) $row["c"];
    }
    return $counts;
}

