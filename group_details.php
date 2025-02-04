<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$group_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Obter informações do grupo
$sql_group = "SELECT * FROM groups WHERE id = ?";
$stmt_group = $conn->prepare($sql_group);
$stmt_group->bind_param("i", $group_id);
$stmt_group->execute();
$result_group = $stmt_group->get_result();
$group = $result_group->fetch_assoc();

// Obter os valores a serem divididos entre os membros do grupo
$sql_bills = "SELECT gb.id, gb.description, gb.amount, up.value_per_user, up.payment_status 
              FROM group_bills gb
              JOIN user_payments up ON gb.id = up.bill_id
              WHERE gb.group_id = ? AND up.user_id = ?";
$stmt_bills = $conn->prepare($sql_bills);
$stmt_bills->bind_param("ii", $group_id, $user_id);
$stmt_bills->execute();
$result_bills = $stmt_bills->get_result();

// Atualizar o status de pagamento quando o usuário marcar como pago
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_paid'])) {
    $bill_id = $_POST['bill_id'];
    
    // Atualizar o status de pagamento para 'pago'
    $sql_update_payment = "UPDATE user_payments SET payment_status = 'pago' WHERE bill_id = ? AND user_id = ?";
    $stmt_update_payment = $conn->prepare($sql_update_payment);
    $stmt_update_payment->bind_param("ii", $bill_id, $user_id);
    $stmt_update_payment->execute();

    $payment_success = "Pagamento realizado com sucesso!";
}

// Exibir os membros do grupo (somente o administrador pode ver essa parte)
$sql_members = "SELECT u.name, u.email, up.payment_status FROM users u
                JOIN group_members gm ON u.id = gm.user_id
                JOIN user_payments up ON u.id = up.user_id
                WHERE gm.group_id = ? AND up.bill_id IN (SELECT id FROM group_bills WHERE group_id = ?)";
$stmt_members = $conn->prepare($sql_members);
$stmt_members->bind_param("ii", $group_id, $group_id);
$stmt_members->execute();
$result_members = $stmt_members->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Grupo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="text-center">Detalhes do Grupo: <?= htmlspecialchars($group['name']) ?></h2>

    <!-- Mensagem de Sucesso -->
    <?php if (isset($payment_success)): ?>
        <div class="alert alert-success"><?= $payment_success ?></div>
    <?php endif; ?>

    <!-- Lista de valores divididos entre os membros -->
    <h3>Valores Devidos</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Descrição</th>
                <th>Valor Devido</th>
                <th>Status de Pagamento</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($bill = $result_bills->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($bill['description']) ?></td>
                    <td>R$ <?= number_format($bill['value_per_user'], 2, ',', '.') ?></td>
                    <td><?= $bill['payment_status'] == 'pago' ? 'Pago' : 'Pendente' ?></td>
                    <td>
                        <?php if ($bill['payment_status'] == 'pendente'): ?>
                            <form method="post">
                                <input type="hidden" name="bill_id" value="<?= $bill['id'] ?>">
                                <button type="submit" name="mark_paid" class="btn btn-success">Marcar como Pago</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <hr>

    <!-- Exibindo membros do grupo e seus status de pagamento -->
    <?php if ($_SESSION['role'] == 'admin'): ?>
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
    <?php endif; ?>

    <a href="user_dashboard.php" class="btn btn-secondary">Voltar</a>
</div>
</body>
</html>
