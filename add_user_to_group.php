<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $group_id = $_POST['group_id'];
    $user_id = $_POST['user_id'];

    $sql = "INSERT INTO group_members (group_id, user_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $group_id, $user_id);

    if ($stmt->execute()) {
        $success_message = "Usuário adicionado ao grupo!";
    } else {
        $error_message = "Erro ao adicionar usuário.";
    }
}

$sql_users = "SELECT * FROM users WHERE role = 'user'";
$result_users = $conn->query($sql_users);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow-lg p-4">
        <h2 class="text-center">Adicionar Usuário ao Grupo</h2>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
        <?php elseif (isset($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label">Selecione um Usuário</label>
                <select name="user_id" class="form-select" required>
                    <?php while ($user = $result_users->fetch_assoc()): ?>
                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <input type="hidden" name="group_id" value="<?= $_GET['group_id'] ?>">
            <button type="submit" class="btn btn-primary w-100">Adicionar</button>
        </form>
        <a href="admin_dashboard.php" class="btn btn-secondary mt-3">Voltar</a>
    </div>
</div>
</body>
</html>
