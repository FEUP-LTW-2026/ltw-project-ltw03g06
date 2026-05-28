<?php
require_once '../utils/connection.php';
require_once '../utils/csrf.php';
require_once 'auth.php';
requireAdmin();
$pageCss = 'admin.css';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_die();

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $allowedRoles = ['member', 'trainer', 'admin'];

    if (!in_array($role, $allowedRoles, true)) {
        $role = 'member';
    }

    $stmt = $db->prepare("
        INSERT INTO users (name, email, password, role)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([$name, $email, $password, $role]);

    header("Location: user.php");
    exit;
}

require_once '../templates/header.php';
?>

<main class="admin-container">

<h1>Create User</h1>

<form method="POST" class="admin-form">
    <?= csrf_input() ?>

    <label>
        Name
        <input name="name" required>
    </label>

    <label>
        Email
        <input name="email" required>
    </label>

    <label>
        Password
        <input name="password" type="password" required>
    </label>

    <label>
        Role
        <select name="role">
            <option value="member">Member</option>
            <option value="trainer">Trainer</option>
            <option value="admin">Admin</option>
        </select>
    </label>

    <button type="submit" class="btn btn--gold u-lift">
        Create
    </button>

</form>

</main>

<?php require_once '../templates/footer.php'; ?>