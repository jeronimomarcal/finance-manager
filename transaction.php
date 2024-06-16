<?php
session_start();
include 'config/db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Processa o formulário de adição de transação
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    $category = $_POST['category'];
    $date = $_POST['date'];
    $description = $_POST['description'];
    $user_id = $_SESSION['user_id'];

    // Insere a transação no banco de dados
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, category, date, description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $amount, $type, $category, $date, $description]);

    // Redireciona de volta para o dashboard após a inserção
    header('Location: dashboard.php');
    exit;
}

// Obter dados do banco de dados
$user_id = $_SESSION['user_id'];

// Total de receitas e despesas
$stmt = $pdo->prepare("SELECT 
    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
    FROM transactions WHERE user_id = ?");
$stmt->execute([$user_id]);
$totals = $stmt->fetch(PDO::FETCH_ASSOC);

// Variáveis para os balanços
$totalIncome = $totals['total_income'];
$totalExpense = $totals['total_expense'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Transação - Gerenciador Financeiro</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="container mt-5">
    <h3>Adicionar Transação</h3>

    <!-- Exibição dos Balanços -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Ganho Total</h5>
                    <p class="card-text">R$<?= number_format($totalIncome, 2) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Despesa Total</h5>
                    <p class="card-text">R$<?= number_format($totalExpense, 2) ?></p>
                </div>
            </div>
        </div>
    </div>

    <form id="transaction-form" method="post">
        <div class="form-group">
            <label for="amount">Valor:</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="type">Tipo:</label>
            <select name="type" id="type" class="form-control" required>
                <option value="income">Ganho</option>
                <option value="expense">Despesa</option>
            </select>
        </div>
        <div class="form-group">
            <label for="category">Categoria:</label>
            <input type="text" name="category" id="category" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="date">Data:</label>
            <input type="date" name="date" id="date" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="description">Descrição:</label>
            <input type="text" name="description" id="description" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Adicionar Transação</button>
        <button type="button" class="btn btn-secondary" onclick="window.location.href='dashboard.php';">Cancelar</button>
    </form>
</div>
<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
