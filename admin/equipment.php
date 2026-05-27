<?php
require_once '../utils/connection.php';
require_once 'auth.php';
requireAdmin();

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
                    <a href="delete_equipment.php?id=<?= $item['id'] ?>"
                       onclick="return confirm('Delete this equipment?')"
                       style="color:red;">
                        Delete
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

</main>

<?php require_once '../templates/footer.php'; ?>