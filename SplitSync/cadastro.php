<?php
session_start();

// Cria a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Coletando os dados do formulário
    $tipo_usuario = $_POST["tipo_usuario"];
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT); // Hash da senha
    
    // Validação básica
    if (empty($nome) || empty($email) || empty($_POST["senha"])) {
        die("Todos os campos são obrigatórios!");
    }

    // Verifica se o e-mail já existe no banco de dados
    $stmt = $conn->prepare("SELECT id FROM clientes WHERE email = ? UNION SELECT id FROM admins WHERE email = ?");
    $stmt->bind_param("ss", $email, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>alert('E-mail já cadastrado!'); window.location.href='cadastro.html';</script>";
        exit();
    }

    $stmt->close();

    // Inserção no banco de dados
    if ($tipo_usuario == "cliente") {
        $idade = $_POST["idade"] ?? null; // Idade só para clientes
        $stmt = $conn->prepare("INSERT INTO clientes (nome, idade, email, senha) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siss", $nome, $idade, $email, $senha);
    } else {
        $stmt = $conn->prepare("INSERT INTO admins (nome, email, senha) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nome, $email, $senha);
    }

    // Executa e verifica se deu certo
    if ($stmt->execute()) {
        echo "<script>alert('Cadastro realizado com sucesso!'); window.location.href='index.html';</script>";
    } else {
        echo "<script>alert('Erro ao cadastrar: " . $stmt->error . "'); window.history.back();</script>";
    }

    $stmt->close();
}

$conn->close();
?>