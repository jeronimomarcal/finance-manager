<?php
session_start();
include 'config/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // Redireciona para a página de login se não houver sessão ativa
    exit;
}

// Obter o ID da transação a ser editada
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php'); // Redireciona de volta para o dashboard se o ID não for válido
    exit;
}

$user_id = $_SESSION['user_id'];
$transaction_id = $_GET['id'];

// Obter os detalhes da transação do banco de dados
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
$stmt->execute([$transaction_id, $user_id]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar se a transação existe e pertence ao usuário
if (!$transaction) {
    header('Location: dashboard.php'); // Redireciona de volta para o dashboard se a transação não pertencer ao usuário
    exit;
}

// Atualizar transação editada
if (isset($_POST['update_transaction'])) {
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    $category = $_POST['category'];
    $date = $_POST['date'];
    $description = $_POST['description'];

    // Preparar e executar a query de atualização
    $stmt = $pdo->prepare("UPDATE transactions SET amount = ?, type = ?, category = ?, date = ?, description = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$amount, $type, $category, $date, $description, $transaction_id, $user_id]);

    // Redirecionar após a atualização
    header('Location: dashboard.php'); // Redireciona de volta para o dashboard após a atualização
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Transação - Gerenciador Financeiro</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?> <!-- Inclui o cabeçalho comum -->
<div class="container mt-5">
    <h3>Editar Transação</h3>
    <form method="post" action="edit_transaction.php?id=<?= $transaction_id ?>">
        <div class="form-group">
            <label for="amount">Valor:</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control" value="<?= $transaction['amount'] ?>" required>
        </div>
        <div class="form-group">
            <label for="type">Tipo:</label>
            <select name="type" id="type" class="form-control" required>
                <option value="income" <?= ($transaction['type'] === 'income') ? 'selected' : '' ?>>Receita</option>
                <option value="expense" <?= ($transaction['type'] === 'expense') ? 'selected' : '' ?>>Despesa</option>
            </select>
        </div>
        <div class="form-group">
            <label for="category">Categoria:</label>
            <input type="text" name="category" id="category" class="form-control" value="<?= $transaction['category'] ?>" required>
        </div>
        <div class="form-group">
            <label for="date">Data:</label>
            <input type="date" name="date" id="date" class="form-control" value="<?= $transaction['date'] ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Descrição:</label>
            <input type="text" name="description" id="description" class="form-control" value="<?= $transaction['description'] ?>" required>
        </div>
        <button type="submit" name="update_transaction" class="btn btn-primary">Salvar Alterações</button> <!-- Botão para salvar alterações -->
        <button type="button" class="btn btn-secondary" onclick="window.location.href='dashboard.php';">Cancelar</button> <!-- Botão para cancelar e voltar ao dashboard -->
    </form>
</div>
<?php include 'includes/footer.php'; ?> <!-- Inclui o rodapé comum -->
</body>
</html>
