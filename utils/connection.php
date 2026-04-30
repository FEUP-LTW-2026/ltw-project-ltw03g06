<?php
// utils/connection.php

// Define o caminho absoluto para o banco de dados
$dbPath = __DIR__ . '/../database/database.db';

try {
    $db = new PDO('sqlite:' . $dbPath);
    // Configura o PDO para lançar exceções em caso de erro
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Configura para retornar arrays associativos por padrão
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Habilita as chaves estrangeiras no SQLite
    $db->exec('PRAGMA foreign_keys = ON;');
} catch (PDOException $e) {
    die("Erro ao conectar com a base de dados: " . $e->getMessage());
}
?>