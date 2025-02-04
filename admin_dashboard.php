<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];

// Obter todos os grupos do administrador
$sql_groups = "SELECT * FROM groups WHERE admin_id = ?";
$stmt_groups = $conn->prepare($sql_groups);
$stmt_groups->bind_param("i", $admin_id);
$stmt_groups->execute();
$result_groups = $stmt_groups->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard do Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="text-center">Dashboard do Administrador</h2>

    <!-- Botão para Criar Novo Grupo -->
    <div class="text-end mb-3">
        <a href="create_group.php" class="btn btn-primary">Criar Novo Grupo</a>
    </div>

    <!-- Exibir Grupos -->
    <h3 class="mt-4">Grupos Criados</h3>

    <?php while ($group = $result_groups->fetch_assoc()): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($group['name']) ?></h5>
                <p class="card-text"><?= htmlspecialchars($group['description']) ?></p>

                <!-- Verificar se o grupo foi criado e exibir o botão de adicionar usuário -->
                <form method="get" action="manage_group.php">
                    <input type="hidden" name="id" value="<?= $group['id'] ?>">
                    <button type="submit" class="btn btn-primary">Gerenciar Grupo</button>
                </form>

                    <form method="get" action="add_user_to_group.php" class="mt-2">
                        <input type="hidden" name="group_id" value="<?= $group['id'] ?>">
                        <button type="submit" class="btn btn-success">Adicionar Usuários ao Grupo</button>
                    </form>
                
            </div>
        </div>
    <?php endwhile; ?>
    <a href="login.html" class="btn btn-danger">Sair da Conta</a>
</div>
</body>
</html>
