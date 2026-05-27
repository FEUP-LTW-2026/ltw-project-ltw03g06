<?php
require_once '../utils/connection.php';
require_once 'auth.php';
requireAdmin();

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: classes.php");
    exit;
}

/* 1. Verificar se a class existe */
$stmt = $db->prepare("SELECT id FROM classes WHERE id = ?");
$stmt->execute([$id]);
$class = $stmt->fetch();

if (!$class) {
    header("Location: classes.php");
    exit;
}

/* 2. (Opcional mas recomendado) apagar dependências */
$db->prepare("DELETE FROM class_reviews WHERE session_id IN (
    SELECT id FROM class_sessions WHERE class_id = ?
)")->execute([$id]);

$db->prepare("DELETE FROM session_enrollments WHERE session_id IN (
    SELECT id FROM class_sessions WHERE class_id = ?
)")->execute([$id]);

$db->prepare("DELETE FROM class_sessions WHERE class_id = ?")->execute([$id]);

/* 3. apagar a class */
$delete = $db->prepare("DELETE FROM classes WHERE id = ?");
$delete->execute([$id]);

header("Location: classes.php");
exit;