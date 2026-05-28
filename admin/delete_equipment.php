<?php
require_once '../utils/connection.php';
require_once '../utils/csrf.php';
require_once 'auth.php';
requireAdmin();
$pageCss = 'admin.css';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: equipment.php");
    exit;
}

csrf_validate_or_die();

$id = (int) ($_POST['id'] ?? 0);

if ($id > 0) {
    $stmt = $db->prepare("DELETE FROM equipment WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: equipment.php");
exit;