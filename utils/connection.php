<?php

$dbPath = __DIR__ . '/../database/database.db';

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->exec('PRAGMA foreign_keys = ON;');
} catch (PDOException $e) {
    die('Erro ao conectar com a base de dados: ' . $e->getMessage());
}

function currentMemberId(PDO $db): int
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
}