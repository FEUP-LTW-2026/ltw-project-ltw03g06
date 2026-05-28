<?php
require_once '../utils/connection.php';
require_once '../utils/csrf.php';
require_once 'auth.php';
requireAdmin();
$pageCss = 'admin.css';

$id = (int) ($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: user.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_die();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'member';
    $allowedRoles = ['member', 'trainer', 'admin'];

    if (!in_array($role, $allowedRoles, true)) {
        $role = 'member';
    }

    if ($name === '' || $email === '') {
        $error = "Name and email are required.";
    } else {
        $update = $db->prepare("
            UPDATE users 
            SET name = ?, email = ?, role = ?
            WHERE id = ?
        ");

        $update->execute([$name, $email, $role, $id]);

        $success = "User updated successfully.";

        // refresh data
        $stmt->execute([$id]);
        $user = $stmt->fetch();
    }
}

require_once '../templates/header.php';
?>

<main class="admin-container">

    <h1>Edit User</h1>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p style="color:green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post" class="admin-form">
        <?= csrf_input() ?>

        <label>
            Name
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>">
        </label>

        <label>
            Email
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
        </label>

        <label>
            Role
            <select name="role">
                <option value="member" <?= $user['role'] === 'member' ? 'selected' : '' ?>>Member</option>
                <option value="trainer" <?= $user['role'] === 'trainer' ? 'selected' : '' ?>>Trainer</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </label>

        <button type="submit" class="btn btn--gold u-lift">Save changes</button>

    </form>

</main>

<?php require_once '../templates/footer.php'; ?>