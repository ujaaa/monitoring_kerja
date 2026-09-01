<?php


session_start();


require_once "koneksi.php";


if (
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: index.php");
    exit;
}


$id = $_GET["id"] ?? 0;


if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $role = $_POST["role"];


    $allowedRoles = [
        "user",
        "supervisor",
        "admin"
    ];


    if (!in_array($role, $allowedRoles)) {
        die("Role tidak valid.");
    }


    $stmt = $conn->prepare(
        "UPDATE users
         SET role = ?
         WHERE id = ?"
    );


    $stmt->bind_param(
        "si",
        $role,
        $id
    );


    $stmt->execute();


    header("Location: users.php");


    exit;
}


$stmt = $conn->prepare(
    "SELECT nama, username, role
     FROM users
     WHERE id = ?"
);


$stmt->bind_param("i", $id);


$stmt->execute();


$result = $stmt->get_result();


if ($result->num_rows !== 1) {
    die("User tidak ditemukan.");
}


$user = $result->fetch_assoc();


?>


<!DOCTYPE html>
<html lang="id">


<head>


    <meta charset="UTF-8">


    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >


    <title>Edit Role</title>


    <link rel="stylesheet" href="style.css">

    <link rel="stylesheet" href="edit_role.css">


</head>


<body>


<div class="form-page">


    <div class="form-card">


        <h1>
            Edit Role
        </h1>


        <p>
            <?= htmlspecialchars($user["nama"]) ?>
        </p>


        <form method="POST">


            <label>
                Role
            </label>


            <select name="role">


                <option
                    value="user"
                    <?= $user["role"] === "user"
                        ? "selected"
                        : "" ?>
                >
                    User
                </option>


                <option
                    value="supervisor"
                    <?= $user["role"] === "supervisor"
                        ? "selected"
                        : "" ?>
                >
                    Supervisor
                </option>


                <option
                    value="admin"
                    <?= $user["role"] === "admin"
                        ? "selected"
                        : "" ?>
                >
                    Admin
                </option>


            </select>


            <div class="form-buttons">


                <a href="users.php">
                    Batal
                </a>


                <button type="submit">
                    Simpan
                </button>


            </div>


        </form>


    </div>


</div>


<!-- JAVASCRIPT EDIT ROLE -->

<script src="edit_role.js"></script>


</body>
</html>