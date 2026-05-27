<?php
require_once '../utils/connection.php';
require_once 'auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $area = trim($_POST['area']);
    $total = (int) $_POST['total_units'];
    $available = (int) $_POST['available_units'];
    $status = $_POST['status'];
    $notes = trim($_POST['notes']);

    $stmt = $db->prepare("
        INSERT INTO equipment (name, area, total_units, available_units, status, notes)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([$name, $area, $total, $available, $status, $notes]);

    header("Location: equipment.php");
    exit;
}

require_once '../templates/header.php';
?>

<main class="admin-container">

<h1>Add Equipment</h1>

<form method="POST">

    <input name="name" placeholder="Name" required>
    <input name="area" placeholder="Area" required>

    <input name="total_units" type="number" placeholder="Total units" required>
    <input name="available_units" type="number" placeholder="Available units" required>

    <select name="status">
        <option value="available">Available</option>
        <option value="limited">Limited</option>
        <option value="maintenance">Maintenance</option>
    </select>

    <textarea name="notes" placeholder="Notes"></textarea>

    <button type="submit" class="btn btn--gold">Create</button>

</form>

</main>

<?php require_once '../templates/footer.php'; ?>