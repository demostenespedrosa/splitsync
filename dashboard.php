<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

if ($_SESSION['role'] == 'admin') {
    echo "<h2>Bem-vindo, Administrador</h2>";
    // Conteúdo exclusivo para o administrador
} else {
    echo "<h2>Bem-vindo, Usuário</h2>";
    // Conteúdo exclusivo para o usuário comum
}
?>
