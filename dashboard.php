<?php
session_start();
include 'config/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
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

// Transações
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY date DESC");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Dados para gráficos
$stmt = $pdo->prepare("SELECT 
    category, 
    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense,
    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income
    FROM transactions WHERE user_id = ? GROUP BY category");
$stmt->execute([$user_id]);
$category_totals = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = [];
$expenses = [];
$incomes = [];
foreach ($category_totals as $row) {
    $categories[] = $row['category'];
    $expenses[] = $row['total_expense'];
    $incomes[] = $row['total_income'];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gerenciador Financeiro</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="container mt-5">
    <!-- Cards de Resumo -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Ganho Total</h5>
                    <p class="card-text">R$<?= number_format($totals['total_income'], 2) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Despesa Total</h5>
                    <p class="card-text">R$<?= number_format($totals['total_expense'], 2) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Botão para Adicionar Transação -->
    <button onclick="window.location.href='transaction.php'" class="btn btn-primary mb-3">Adicionar Transação</button>

    
    <!-- Tabela de Transações -->
    <h3 class="mt-5">Transações</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Valor</th>
                <th>Tipo</th>
                <th>Categoria</th>
                <th>Data</th>
                <th>Descrição</th>
                <th>Ações</th> <!-- Coluna para botões de ação -->
            </tr>
        </thead>
        <tbody>
    <?php foreach ($transactions as $transaction): ?>
        <tr>
            <td>R$ <?= number_format($transaction['amount'], 2) ?></td>
            <td><?= ucfirst($transaction['type']) ?></td>
            <td><?= $transaction['category'] ?></td>
            <td><?= $transaction['date'] ?></td>
            <td><?= $transaction['description'] ?></td>
            <td>
                <form action="api/delete_transaction.php" method="post" style="display: inline;">
                    <input type="hidden" name="transaction_id" value="<?= $transaction['id'] ?>">
                    <button type="submit" name="delete_transaction" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta transação?')">Excluir</button>
                </form>
                <form action="edit_transaction.php" method="get" style="display: inline;">
                    <input type="hidden" name="id" value="<?= $transaction['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-primary">Editar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>

    </table>

    <!-- Gráficos -->
    <h3 class="mt-5">Gráficos</h3>
    <div class="row">
        <!-- Gráfico de Pizza -->
        <div class="col-md-6">
            <div class="chart-container">
                <canvas id="pieChart"></canvas>
            </div>
        </div>
        <!-- Gráfico de Barras -->
        <div class="col-md-6">
            <div class="chart-container">
                <canvas id="barChart"></canvas>
            </div>
        </div>
    </div>
    <div class="row mt-5">
        <!-- Gráfico de Linha -->
        <div class="col-md-12">
            <div class="chart-container">
                <canvas id="lineChart"></canvas>
            </div>
        </div>
    </div>
</div>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script>
    // Dados para os gráficos
    var categories = <?= json_encode($categories) ?>;
    var expenses = <?= json_encode($expenses) ?>;
    var incomes = <?= json_encode($incomes) ?>;
    var totalExpense = <?= json_encode($totals['total_expense']) ?>;
    var totalIncome = <?= json_encode($totals['total_income']) ?>;

    // Configuração do gráfico de pizza
    var ctxPie = document.getElementById('pieChart').getContext('2d');
    var pieChart = new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: ['Despesas', 'Ganho'],
            datasets: [{
                label: 'Despesas vs Ganho',
                data: [totalExpense, totalIncome],
                backgroundColor: ['#dc3545', '#28a745']
            }]
        }
    });

    // Configuração do gráfico de barras
    var ctxBar = document.getElementById('barChart').getContext('2d');
    var barChart = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: categories,
            datasets: [{
                label: 'Despesas',
                data: expenses,
                backgroundColor: 'rgba(220, 53, 69, 0.8)',
                borderColor: 'rgba(220, 53, 69, 1)',
                borderWidth: 1
            }, {
                label: 'Ganho',
                data: incomes,
                backgroundColor: 'rgba(40, 167, 69, 0.8)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });

    // Configuração do gráfico de linha
    var ctxLine = document.getElementById('lineChart').getContext('2d');
    var lineChart = new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: categories,
            datasets: [{
                label: 'Despesas',
                data: expenses,
                fill: false,
                borderColor: 'rgba(220, 53, 69, 1)',
                borderWidth: 1
            }, {
                label: 'Ganho',
                data: incomes,
                fill: false,
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });
</script>
</body>
</html>
