<?php
session_start();
$conn = new mysqli("localhost", "root", "", "seu_banco");

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, senha FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION["user_id"] = $id;
            header("Location: dashboard.php");
            exit();
        } else {
            echo "<script>alert('Senha incorreta!'); window.location.href='index.html';</script>";
        }
    } else {
        echo "<script>alert('Usuário não encontrado!'); window.location.href='index.html';</script>";
    }
    
    $stmt->close();
}

$conn->close();
?>
