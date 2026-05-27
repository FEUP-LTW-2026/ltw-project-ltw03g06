<?php
declare(strict_types=1);
require_once '../utils/connection.php';
require_once 'auth.php';
requireAdmin();
$pageCss = 'admin.css';
$pageTitle = "Admin Dashboard";

// Stats (simples e seguros)
$totalUsers = (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalTrainers = (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'trainer'")->fetchColumn();
$totalClasses = (int) $db->query("SELECT COUNT(*) FROM classes")->fetchColumn();
$totalEquipment = (int) $db->query("SELECT COUNT(*) FROM equipment")->fetchColumn();

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

</main>

<?php require_once '../templates/footer.php'; ?>