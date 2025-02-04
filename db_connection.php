<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mydatabase"; // Nome do seu banco de dados

// Criação da conexão
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Verificar a conexão
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>