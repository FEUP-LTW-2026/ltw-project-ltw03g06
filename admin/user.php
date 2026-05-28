<?php
require_once '../utils/connection.php';
require_once '../utils/csrf.php';
require_once 'auth.php';
requireAdmin();
$pageCss = 'admin.css';

$pageTitle = "Manage Users";

$users = $db
    ->query("SELECT id, name, email, role FROM users ORDER BY id DESC")
    ->fetchAll();

require_once '../templates/header.php';
?>

<main class="admin-container">

    <h1>Users</h1>

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= (int) $user['id'] ?></td>

                    <td><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></td>

                    <td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td>

                    <td><?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?></td>

                    <td>
                        <a href="edit_user.php?id=<?= (int) $user['id'] ?>">
                            Edit
                        </a>

                        |

                        <form method="post" action="delete_user.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?')">
                            <?= csrf_input() ?>
                            <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                            <button type="submit" style="background:none;border:none;color:red;cursor:pointer;padding:0;">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</main>

<?php require_once '../templates/footer.php'; ?>