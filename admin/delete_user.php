<?php
require_once '../utils/connection.php';
require_once '../utils/csrf.php';
require_once 'auth.php';
requireAdmin();
$pageCss = 'admin.css';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: user.php");
    exit;
}

csrf_validate_or_die();

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    header("Location: user.php");
    exit;
}

/* 1. Buscar user */
$stmt = $db->prepare("SELECT id, role FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: user.php");
    exit;
}

/* 2. Proteção: não deixar apagar admin (opcional mas recomendado) */
if ($user['role'] === 'admin') {
    // conta admins
    $countAdmins = $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();

    if ($countAdmins <= 1) {
        header("Location: user.php?error=last_admin");
        exit;
    }
}

/* 3. Delete */
$delete = $db->prepare("DELETE FROM users WHERE id = ?");
$delete->execute([$id]);

header("Location: user.php?success=deleted");
exit;