<?php
require_once '../utils/connection.php';
require_once '../utils/csrf.php';
require_once 'auth.php';
requireAdmin();
$pageCss = 'admin.css';

$pageTitle = "Manage Equipment";

$equipment = $db->query("SELECT * FROM equipment ORDER BY id DESC")->fetchAll();

require_once '../templates/header.php';
?>

<main class="admin-container">

    <h1>Equipment</h1>

    <a href="add_equipment.php" class="btn btn--gold u-lift">+ Add Equipment</a>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Area</th>
                <th>Total</th>
                <th>Available</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($equipment as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= htmlspecialchars($item['area']) ?></td>
                <td><?= (int)$item['total_units'] ?></td>
                <td><?= (int)$item['available_units'] ?></td>
                <td><?= htmlspecialchars($item['status']) ?></td>
                <td>
                    <a href="edit_equipment.php?id=<?= $item['id'] ?>">Edit</a> |
                    <form method="post" action="delete_equipment.php" style="display:inline;" onsubmit="return confirm('Delete this equipment?')">
                        <?= csrf_input() ?>
                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                        <button type="submit" style="background:none;border:none;color:red;cursor:pointer;padding:0;">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

</main>

<?php require_once '../templates/footer.php'; ?>