<?php
require_once '../utils/connection.php';
require_once '../utils/csrf.php';
require_once 'auth.php';
requireAdmin();
$pageCss = 'admin.css';

$pageTitle = "Manage Classes";

$classes = $db->query("
    SELECT c.*, u.name AS trainer_name
    FROM classes c
    LEFT JOIN users u ON u.id = c.trainer_id
    ORDER BY c.id DESC
")->fetchAll();

require_once '../templates/header.php';
?>

<main class="admin-container">

    <h1>Classes</h1>

    <a href="create_class.php" class="btn btn--gold u-lift">+ Create Class</a>

    <table class="admin-table">

        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Category</th>
                <th>Trainer</th>
                <th>Duration</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($classes as $class): ?>
                <tr>
                    <td><?= (int) $class['id'] ?></td>

                    <td><?= htmlspecialchars($class['title']) ?></td>

                    <td><?= htmlspecialchars($class['category']) ?></td>

                    <td>
                        <?= $class['trainer_name'] 
                            ? htmlspecialchars($class['trainer_name']) 
                            : 'Unassigned' ?>
                    </td>

                    <td><?= (int) $class['duration'] ?> min</td>

                    <td>
                        <a href="edit_class.php?id=<?= (int) $class['id'] ?>">Edit</a>
                        |
                        <form method="post" action="delete_class.php" style="display:inline;" onsubmit="return confirm('Delete this class?')">
                            <?= csrf_input() ?>
                            <input type="hidden" name="id" value="<?= (int) $class['id'] ?>">
                            <button type="submit" style="background:none;border:none;color:red;cursor:pointer;padding:0;">Delete</button>
                        </form>
                    </td>

                </tr>
            <?php endforeach; ?>
        </tbody>

    </table>

</main>

<?php require_once '../templates/footer.php'; ?>