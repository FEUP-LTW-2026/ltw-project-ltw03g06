<?php
// Inicia a sessão corrente
session_start();

// Limpa todas as variáveis de sessão
$_SESSION = array();

// Destrói a sessão no servidor
session_destroy();

// Redireciona o utilizador de volta para a Home Page
header('Location: index.php');
exit;