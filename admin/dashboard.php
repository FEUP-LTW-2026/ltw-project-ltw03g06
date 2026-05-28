<?php
declare(strict_types=1);

require_once '../utils/connection.php';
require_once 'auth.php';
requireAdmin();

$pageCss = 'admin.css';
$pageTitle = "Admin Dashboard";


$totalUsers = (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalTrainers = (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'trainer'")->fetchColumn();
$totalClasses = (int) $db->query("SELECT COUNT(*) FROM classes")->fetchColumn();
$totalEquipment = (int) $db->query("SELECT COUNT(*) FROM equipment")->fetchColumn();

$recentUsers = $db->query("
    SELECT id, name, email, role
    FROM users
    ORDER BY id DESC
    LIMIT 5
")->fetchAll();

$recentClasses = $db->query("
    SELECT id, title, category
    FROM classes
    ORDER BY id DESC
    LIMIT 5
")->fetchAll();

require_once '../templates/header.php';
?>

<main class="admin-container">

    <h1>Admin Dashboard</h1>

    <div class="admin-grid">

        <div class="admin-card">
            <h2><?= $totalUsers ?></h2>
            <p>Users</p>
        </div>

        <div class="admin-card">
            <h2><?= $totalTrainers ?></h2>
            <p>Trainers</p>
        </div>

        <div class="admin-card">
            <h2><?= $totalClasses ?></h2>
            <p>Classes</p>
        </div>

        <div class="admin-card">
            <h2><?= $totalEquipment ?></h2>
            <p>Equipment</p>
        </div>

    </div>

    <section class="admin-actions" style="margin-top: 3rem;">

        <h2>Quick Actions</h2>

        <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-top:1rem;">

            <a class="btn btn--gold" href="user.php">Manage Users</a>
            <a class="btn btn--gold" href="create_user.php">Create User</a>

            <a class="btn btn--gold" href="classes.php">Manage Classes</a>
            <a class="btn btn--gold" href="create_class.php">Create Class</a>

            <a class="btn btn--gold" href="equipment.php">Manage Equipment</a>
            <a class="btn btn--gold" href="add_equipment.php">Add Equipment</a>

        </div>

    </section>

    <section style="margin-top: 3rem;">

        <h2>Recent Users</h2>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($recentUsers as $user): ?>
                    <tr>
                        <td><?= (int)$user['id'] ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </section>

    <section style="margin-top: 3rem;">

        <h2>Recent Classes</h2>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($recentClasses as $class): ?>
                    <tr>
                        <td><?= (int)$class['id'] ?></td>
                        <td><?= htmlspecialchars($class['title']) ?></td>
                        <td><?= htmlspecialchars($class['category']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </section>

</main>

<?php require_once '../templates/footer.php'; ?>