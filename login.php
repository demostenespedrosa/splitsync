<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Evitar SQL Injection com Prepared Statements
    $sql = "SELECT id, name, email, password, role FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Verificando a senha
        if (password_verify($password, $row['password'])) {
            // Armazena informações na sessão
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['role'] = $row['role']; // Pode ser 'user' ou 'admin'

            // Redireciona conforme o papel do usuário
            if ($row['role'] == 'admin') {
                header("Location: admin_dashboard.php"); // Tela de admin
            } else {
                header("Location: user_dashboard.php"); // Tela de usuário
            }
            exit;
        } else {
            echo "<p><h1>E-mail ou senha incorretos.</h1></p>";
        }
    } else {
        echo "<p><h1>E-mail não encontrado.</h1></p>";
    }
}
?>




