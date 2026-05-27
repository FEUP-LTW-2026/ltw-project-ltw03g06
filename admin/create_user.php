<?php
require_once '../utils/connection.php';
require_once 'auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = $db->prepare("
        INSERT INTO users (name, email, password, role)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([$name, $email, $password, $role]);

    header("Location: users.php");
    exit;
}

require_once '../templates/header.php';
?>

<main class="admin-container">

<h1>Create User</h1>

<form method="POST">

    <input name="name" placeholder="Name" required>
    <input name="email" placeholder="Email" required>
    <input name="password" type="password" placeholder="Password" required>

    <select name="role">
        <option value="member">Member</option>
        <option value="trainer">Trainer</option>
        <option value="admin">Admin</option>
    </select>

    <button type="submit" class="btn btn--gold">Create</button>

</form>

</main>

<?php require_once '../templates/footer.php'; ?>