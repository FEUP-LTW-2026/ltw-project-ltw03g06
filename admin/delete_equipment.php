<?php
require_once '../utils/connection.php';
require_once 'auth.php';
requireAdmin();

$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $db->prepare("DELETE FROM equipment WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: equipment.php");
exit;