<?php
require_once '../utils/connection.php';
require_once 'auth.php';
requireAdmin();

$pageTitle = "Add Equipment";
$pageCss = "admin.css";

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $area = trim($_POST['area'] ?? '');
    $total = (int) ($_POST['total_units'] ?? 0);
    $available = (int) ($_POST['available_units'] ?? 0);
    $status = $_POST['status'] ?? 'available';
    $notes = trim($_POST['notes'] ?? '');

    if ($name === '' || $area === '' || $total <= 0) {
        $error = "Please fill all required fields.";
    } else {

        $stmt = $db->prepare("
            INSERT INTO equipment 
            (name, area, total_units, available_units, status, notes)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([$name, $area, $total, $available, $status, $notes]);

        header("Location: equipment.php");
        exit;
    }
}

require_once '../templates/header.php';
?>

<main class="admin-container">

    <h1>Add Equipment</h1>

    <?php if ($error): ?>
        <p style="color:red; margin-top:1rem;">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <form method="POST" class="admin-form">

        <label>
            Name
            <input name="name" required>
        </label>

        <label>
            Area
            <input name="area" required>
        </label>

        <label>
            Total units
            <input name="total_units" type="number" required>
        </label>

        <label>
            Available units
            <input name="available_units" type="number" required>
        </label>

        <label>
            Status
            <select name="status">
                <option value="available">Available</option>
                <option value="limited">Limited</option>
                <option value="maintenance">Maintenance</option>
            </select>
        </label>

        <label>
            Notes
            <textarea name="notes" rows="3"></textarea>
        </label>

        <button type="submit" class="btn btn--gold u-lift">
            Create Equipment
        </button>

    </form>

</main>

<?php require_once '../templates/footer.php'; ?>