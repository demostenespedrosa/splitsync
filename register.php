<?php
include 'db_connection.php'; // Este arquivo contém a conexão com o banco de dados

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $age = $_POST['age'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Criptografando a senha antes de salvar no banco de dados
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, age, password, role) VALUES ('$name', '$email', '$age', '$hashed_password', '$role')";

    if (mysqli_query($conn, $sql)) {
        echo "<p>Cadastro realizado com sucesso. <a href='login.html'>Faça login aqui</a></p>";
    } else {
        echo "Erro: " . $sql . "<br>" . mysqli_error($conn);
    }
}
?>
