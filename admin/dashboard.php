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

$activeMembersLast30 = (int)$db->query("
    SELECT COUNT(DISTINCT se.user_id)
    FROM session_enrollments se
    JOIN users u ON se.user_id = u.id
    JOIN class_sessions cs ON se.session_id = cs.id
    WHERE u.role = 'member' AND cs.start_at >= date('now', '-30 days')
")->fetchColumn();

$memberAttendanceCount = (int)$db->query("
    SELECT COUNT(DISTINCT se.user_id)
    FROM session_enrollments se
    JOIN users u ON se.user_id = u.id
    WHERE u.role = 'member'
")->fetchColumn();

$repeatMembers = (int)$db->query("
    SELECT COUNT(*)
    FROM (
        SELECT se.user_id
        FROM session_enrollments se
        JOIN users u ON se.user_id = u.id
        WHERE u.role = 'member'
        GROUP BY se.user_id
        HAVING COUNT(*) > 1
    )")->fetchColumn();

$memberRetention = $memberAttendanceCount > 0 ? round($repeatMembers * 100.0 / $memberAttendanceCount, 1) : 0;

$equipmentUsageData = $db->query("
    SELECT name, total_units, available_units,
           ROUND((total_units - available_units) * 100.0 / total_units, 1) AS usage_pct
    FROM equipment
    WHERE total_units > 0
    ORDER BY usage_pct DESC
    LIMIT 5
")->fetchAll();

$totalEquipmentUnits = (int)$db->query("SELECT COALESCE(SUM(total_units), 0) FROM equipment")->fetchColumn();
$totalEquipmentInUse = (int)$db->query("SELECT COALESCE(SUM(total_units - available_units), 0) FROM equipment")->fetchColumn();
$averageEquipmentUsage = $totalEquipmentUnits > 0 ? round($totalEquipmentInUse * 100.0 / $totalEquipmentUnits, 1) : 0;

$popularClasses = $db->query("
    SELECT c.title, COUNT(se.user_id) AS enrollments,
           ROUND(AVG(cr.rating), 1) AS average_rating
    FROM classes c
    JOIN class_sessions cs ON cs.class_id = c.id
    LEFT JOIN session_enrollments se ON se.session_id = cs.id
    LEFT JOIN class_reviews cr ON cr.session_id = cs.id
    GROUP BY c.id
    ORDER BY enrollments DESC
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

    <section class="admin-grid">
        <div class="admin-card">
            <h2><?= $activeMembersLast30 ?></h2>
            <p>Active Members (30d)</p>
        </div>

        <div class="admin-card">
            <h2><?= $memberRetention ?>%</h2>
            <p>Retention Rate</p>
        </div>

        <div class="admin-card">
            <h2><?= $averageEquipmentUsage ?>%</h2>
            <p>Equipment Usage</p>
        </div>

        <div class="admin-card">
            <h2><?= $repeatMembers ?></h2>
            <p>Repeat Members</p>
        </div>
    </section>

    <section class="admin-actions">
        <h2>Quick Actions</h2>
        <div class="admin-actions-grid">
            <a class="btn btn--gold" href="user.php">Manage Users</a>
            <a class="btn btn--gold" href="create_user.php">Create User</a>

            <a class="btn btn--gold" href="classes.php">Manage Classes</a>
            <a class="btn btn--gold" href="create_class.php">Create Class</a>

            <a class="btn btn--gold" href="equipment.php">Manage Equipment</a>
            <a class="btn btn--gold" href="add_equipment.php">Add Equipment</a>
        </div>
    </section>

    <section>
        <h2>Most Popular Classes</h2>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Enrollments</th>
                        <th>Avg. Rating</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($popularClasses as $class): ?>
                        <tr>
                            <td><?= htmlspecialchars($class['title']) ?></td>
                            <td><?= (int)$class['enrollments'] ?></td>
                            <td><?= $class['average_rating'] === null ? '—' : htmlspecialchars($class['average_rating']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section>
        <h2>Equipment Usage</h2>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Equipment</th>
                        <th>Total Units</th>
                        <th>Available</th>
                        <th>Usage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($equipmentUsageData as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= (int)$item['total_units'] ?></td>
                            <td><?= (int)$item['available_units'] ?></td>
                            <td><?= htmlspecialchars($item['usage_pct']) ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section>
        <h2>Recent Users</h2>
        <div class="admin-table-wrapper">
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
        </div>
    </section>

    <section>
        <h2>Recent Classes</h2>
        <div class="admin-table-wrapper">
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
        </div>
    </section>

</main>

<?php require_once '../templates/footer.php'; ?>