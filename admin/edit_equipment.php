<?php
require_once '../utils/connection.php';
require_once '../utils/csrf.php';
require_once 'auth.php';
requireAdmin();
$pageCss = 'admin.css';

$id = (int) ($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM equipment WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    header("Location: equipment.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_die();

    $name = trim($_POST['name']);
    $area = trim($_POST['area']);
    $total = (int) $_POST['total_units'];
    $available = (int) $_POST['available_units'];
    $status = $_POST['status'];
    $notes = trim($_POST['notes']);

    $update = $db->prepare("
        UPDATE equipment
        SET name=?, area=?, total_units=?, available_units=?, status=?, notes=?, updated_at=CURRENT_TIMESTAMP
        WHERE id=?
    ");

    $update->execute([$name, $area, $total, $available, $status, $notes, $id]);

    header("Location: equipment.php");
    exit;
}

require_once '../templates/header.php';
?>

<main class="admin-container">

<h1>Edit Equipment</h1>

<form method="POST">
    <?= csrf_input() ?>

    <input name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
    <input name="area" value="<?= htmlspecialchars($item['area']) ?>" required>

    <input name="total_units" type="number" value="<?= (int)$item['total_units'] ?>" required>
    <input name="available_units" type="number" value="<?= (int)$item['available_units'] ?>" required>

    <select name="status">
        <option value="available" <?= $item['status']==='available'?'selected':'' ?>>Available</option>
        <option value="limited" <?= $item['status']==='limited'?'selected':'' ?>>Limited</option>
        <option value="maintenance" <?= $item['status']==='maintenance'?'selected':'' ?>>Maintenance</option>
    </select>

    <textarea name="notes"><?= htmlspecialchars($item['notes']) ?></textarea>

    <button type="submit" class="btn btn--gold">Save</button>

</form>

</main>

<?php require_once '../templates/footer.php'; ?>