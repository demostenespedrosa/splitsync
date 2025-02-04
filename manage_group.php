<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$group_id = $_GET['id'];

// Obter informações do grupo
$sql_group = "SELECT * FROM groups WHERE id = ?";
$stmt_group = $conn->prepare($sql_group);
$stmt_group->bind_param("i", $group_id);
$stmt_group->execute();
$result_group = $stmt_group->get_result();
$group = $result_group->fetch_assoc();

// Obter os membros do grupo
$sql_members = "SELECT u.id, u.name, u.email, up.payment_status 
                FROM users u
                JOIN group_members gm ON u.id = gm.user_id
                JOIN user_payments up ON u.id = up.user_id
                WHERE gm.group_id = ?";
$stmt_members = $conn->prepare($sql_members);
$stmt_members->bind_param("i", $group_id);
$stmt_members->execute();
$result_members = $stmt_members->get_result();

// Apagar grupo
if (isset($_POST['delete_group'])) {
    // Remover membros do grupo
    $sql_delete_members = "DELETE FROM group_members WHERE group_id = ?";
    $stmt_delete_members = $conn->prepare($sql_delete_members);
    $stmt_delete_members->bind_param("i", $group_id);
    $stmt_delete_members->execute();

    // Remover pagamentos dos usuários
    $sql_delete_payments = "DELETE FROM user_payments WHERE bill_id IN (SELECT id FROM group_bills WHERE group_id = ?)";
    $stmt_delete_payments = $conn->prepare($sql_delete_payments);
    $stmt_delete_payments->bind_param("i", $group_id);
    $stmt_delete_payments->execute();

    // Remover contas
    $sql_delete_bills = "DELETE FROM group_bills WHERE group_id = ?";
    $stmt_delete_bills = $conn->prepare($sql_delete_bills);
    $stmt_delete_bills->bind_param("i", $group_id);
    $stmt_delete_bills->execute();

    // Remover o grupo
    $sql_delete_group = "DELETE FROM groups WHERE id = ?";
    $stmt_delete_group = $conn->prepare($sql_delete_group);
    $stmt_delete_group->bind_param("i", $group_id);
    $stmt_delete_group->execute();

    header("Location: admin_dashboard.php");
    exit;
}

// Apagar uma conta (valor)
if (isset($_POST['delete_bill'])) {
    $bill_id = $_POST['bill_id'];

    // Remover os pagamentos associados à conta
    $sql_delete_payments = "DELETE FROM user_payments WHERE bill_id = ?";
    $stmt_delete_payments = $conn->prepare($sql_delete_payments);
    $stmt_delete_payments->bind_param("i", $bill_id);
    $stmt_delete_payments->execute();

    // Remover a conta
    $sql_delete_bill = "DELETE FROM group_bills WHERE id = ?";
    $stmt_delete_bill = $conn->prepare($sql_delete_bill);
    $stmt_delete_bill->bind_param("i", $bill_id);
    $stmt_delete_bill->execute();

    header("Location: manage_group.php?id=$group_id");
    exit;
}

// Adicionar novo valor e dividir entre os usuários
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_value'])) {
    $description = $_POST['description'];
    $amount = $_POST['amount'];

    // Inserir o novo valor na tabela group_bills
    $sql_add_bill = "INSERT INTO group_bills (group_id, description, amount) VALUES (?, ?, ?)";
    $stmt_add_bill = $conn->prepare($sql_add_bill);
    $stmt_add_bill->bind_param("isd", $group_id, $description, $amount);
    $stmt_add_bill->execute();

    // Obter o ID do novo valor inserido
    $bill_id = $stmt_add_bill->insert_id;

    // Calcular a quantidade de membros no grupo
    $sql_count_members = "SELECT COUNT(*) AS total_members FROM group_members WHERE group_id = ?";
    $stmt_count_members = $conn->prepare($sql_count_members);
    $stmt_count_members->bind_param("i", $group_id);
    $stmt_count_members->execute();
    $result_count = $stmt_count_members->get_result();
    $count_members = $result_count->fetch_assoc()['total_members'];

    // Calcular o valor que cada usuário deve pagar
    $value_per_user = $amount / $count_members;

    // Inserir o valor dividido para cada usuário na tabela user_payments
    $sql_insert_payments = "INSERT INTO user_payments (user_id, bill_id, value_per_user, payment_status) 
                            SELECT u.id, ?, ?, 'pendente' 
                            FROM users u
                            JOIN group_members gm ON u.id = gm.user_id
                            WHERE gm.group_id = ?";
    $stmt_insert_payments = $conn->prepare($sql_insert_payments);
    $stmt_insert_payments->bind_param("idi", $bill_id, $value_per_user, $group_id);
    $stmt_insert_payments->execute();

    $success_message = "Novo valor adicionado e dividido entre os membros!";
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Grupo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="text-center">Gerenciar Grupo: <?= htmlspecialchars($group['name']) ?></h2>

    <!-- Mensagem de Sucesso ao Adicionar Valor -->
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?= $success_message ?></div>
    <?php endif; ?>

    <!-- Formulário para Adicionar Novo Valor -->
    <h3>Adicionar Novo Valor</h3>
    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label for="description" class="form-label">Descrição</label>
            <input type="text" name="description" id="description" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">Valor</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
        </div>
        <button type="submit" name="add_value" class="btn btn-primary">Adicionar Valor</button>
    </form>

    <!-- Exibir os valores a serem pagos pelos membros -->
    <h3>Valores Devidos</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Descrição</th>
                <th>Valor Total</th>
                <th>Status de Pagamento</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Obter os valores devidos e seus status de pagamento
            $sql_bills = "SELECT gb.id, gb.description, gb.amount, up.payment_status 
                          FROM group_bills gb
                          JOIN user_payments up ON gb.id = up.bill_id
                          WHERE gb.group_id = ?";
            $stmt_bills = $conn->prepare($sql_bills);
            $stmt_bills->bind_param("i", $group_id);
            $stmt_bills->execute();
            $result_bills = $stmt_bills->get_result();

            // Exibir os valores
            while ($bill = $result_bills->fetch_assoc()):
            ?>
                <tr>
                    <td><?= htmlspecialchars($bill['description']) ?></td>
                    <td>R$ <?= number_format($bill['amount'], 2, ',', '.') ?></td>
                    <td>
                        <?= $bill['payment_status'] == 'pago' ? 'Pago' : 'Pendente' ?>
                    </td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="bill_id" value="<?= $bill['id'] ?>">
                            <button type="submit" name="delete_bill" class="btn btn-danger btn-sm">Apagar</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <hr>

    <!-- Exibindo os membros do grupo com status de pagamento -->
    <h3 class="mt-4">Membros do Grupo</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Status de Pagamento</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($member = $result_members->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($member['name']) ?></td>
                    <td><?= htmlspecialchars($member['email']) ?></td>
                    <td><?= $member['payment_status'] == 'pago' ? 'Pago' : 'Pendente' ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Botão para Apagar Grupo -->
    <form method="POST">
        <button type="submit" name="delete_group" class="btn btn-danger">Apagar Grupo</button>
    </form>

    <hr>
    
    <a href="admin_dashboard.php" class="btn btn-secondary">Voltar para o Criação de grupos </a>
</div>
</body>
</html>
