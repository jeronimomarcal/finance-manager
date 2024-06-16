<?php
session_start();
include 'config/db.php'; // Verifique se o caminho está correto

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Dados mensais para o doughnutChart
    $current_month = date('m');
    $stmt = $pdo->prepare("SELECT 
        SUM(CASE WHEN type = 'Expense' THEN amount ELSE 0 END) as total_expense,
        SUM(CASE WHEN type = 'Income' THEN amount ELSE 0 END) as total_income
        FROM transactions WHERE user_id = ? AND MONTH(date) = ?");
    $stmt->execute([$user_id, $current_month]);
    $monthly_totals = $stmt->fetch(PDO::FETCH_ASSOC);

    // Dados mensais para o areaChart (acumulados ao longo do ano)
    $stmt = $pdo->prepare("SELECT 
        MONTH(date) as month,
        SUM(CASE WHEN type = 'Expense' THEN amount ELSE 0 END) as total_expense,
        SUM(CASE WHEN type = 'Income' THEN amount ELSE 0 END) as total_income
        FROM transactions WHERE user_id = ? AND YEAR(date) = YEAR(CURRENT_DATE()) GROUP BY MONTH(date)");
    $stmt->execute([$user_id]);
    $monthly_category_totals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $months = [];
    $monthly_expenses = [];
    $monthly_incomes = [];
    foreach ($monthly_category_totals as $row) {
        $months[] = date('F', mktime(0, 0, 0, $row['month'], 1));
        $monthly_expenses[] = $row['total_expense'];
        $monthly_incomes[] = $row['total_income'];
    }

    // Dados anuais para o areaChart
    $stmt = $pdo->prepare("SELECT 
        MONTH(date) as month,
        SUM(CASE WHEN type = 'Expense' THEN amount ELSE 0 END) as total_expense,
        SUM(CASE WHEN type = 'Income' THEN amount ELSE 0 END) as total_income
        FROM transactions WHERE user_id = ? AND YEAR(date) = YEAR(CURRENT_DATE()) GROUP BY MONTH(date)");
    $stmt->execute([$user_id]);
    $annual_category_totals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $categories = [];
    $annual_expenses = [];
    $annual_incomes = [];
    foreach ($annual_category_totals as $row) {
        $categories[] = date('F', mktime(0, 0, 0, $row['month'], 1));
        $annual_expenses[] = $row['total_expense'];
        $annual_incomes[] = $row['total_income'];
    }

    // Consulta para obter o resumo financeiro total
    $stmt = $pdo->prepare("SELECT 
        SUM(CASE WHEN type = 'Income' THEN amount ELSE 0 END) as total_income,
        SUM(CASE WHEN type = 'Expense' THEN amount ELSE 0 END) as total_expense
        FROM transactions WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $financial_summary = $stmt->fetch(PDO::FETCH_ASSOC);

    $totalIncome = $financial_summary['total_income'];
    $totalExpense = $financial_summary['total_expense'];
    $balance = $totalIncome - $totalExpense;

} catch (PDOException $e) {
    echo "Erro ao buscar dados: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estatísticas - Painel de Administração Financeira</title>
    <link rel="stylesheet" href="./assets/css/style-cp.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Estilos adicionais conforme necessário */
        .charts {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            align-items: center;
        }

        .chart-container {
            width: 400px;
            height: 400px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?> <!-- Incluindo o header.php -->
    <div class="container mt-5">
        <!-- Resumo Financeiro -->
        <div class="card mb-4">
            <div class="card-header">
                Resumo Financeiro
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="alert alert-success" role="alert">
                            <h4 class="alert-heading">Ganhos Totais</h4>
                            <p id="totalIncome" class="mb-0">R$ <?php echo number_format($totalIncome, 2, ',', '.'); ?></p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="alert alert-danger" role="alert">
                            <h4 class="alert-heading">Despesas Totais</h4>
                            <p id="totalExpense" class="mb-0">R$ <?php echo number_format($totalExpense, 2, ',', '.'); ?></p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="alert alert-info" role="alert">
                            <h4 class="alert-heading">Saldo Atual</h4>
                            <p id="balance" class="mb-0">R$ <?php echo number_format($balance, 2, ',', '.'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="charts">
            <div class="chart-container">
                <canvas id="lineChart"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="barChart"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="pieChart"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="doughnutChart"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="areaChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Dados PHP passados para o JavaScript
            const categories = <?php echo json_encode($categories); ?>;
            const annualExpenses = <?php echo json_encode($annual_expenses); ?>;
            const annualIncomes = <?php echo json_encode($annual_incomes); ?>;
            const totalExpense = <?php echo $monthly_totals['total_expense']; ?>;
            const totalIncome = <?php echo $monthly_totals['total_income']; ?>;
            const balance = <?php echo $monthly_totals['total_income'] - $monthly_totals['total_expense']; ?>;
            const months = <?php echo json_encode($months); ?>;
            const monthlyExpenses = <?php echo json_encode($monthly_expenses); ?>;
            const monthlyIncomes = <?php echo json_encode($monthly_incomes); ?>;

            // Atualizar os elementos HTML com os dados iniciais
            document.getElementById('totalExpense').textContent = totalExpense.toFixed(2);
            document.getElementById('totalIncome').textContent = totalIncome.toFixed(2);
            document.getElementById('balance').textContent = balance.toFixed(2);

            // Inicializar os gráficos com os dados recebidos
            new Chart(document.getElementById('lineChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: categories,
                    datasets: [{
                        label: 'Despesas',
                        data: annualExpenses,
                        fill: false,
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Ganhos',
                        data: annualIncomes,
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

            new Chart(document.getElementById('barChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: categories,
                    datasets: [{
                        label: 'Despesas',
                        data: annualExpenses,
                        backgroundColor: 'rgba(220, 53, 69, 0.8)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Ganhos',
                        data: annualIncomes,
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

            new Chart(document.getElementById('pieChart').getContext('2d'), {
                type: 'pie',
                data: {
                    labels: ['Despesas', 'Ganhos'],
                    datasets: [{
                        label: 'Despesas vs Ganhos',
                        data: [totalExpense, totalIncome],
                        backgroundColor: ['#dc3545', '#28a745']
                    }]
                }
            });

            new Chart(document.getElementById('doughnutChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Despesas', 'Ganhos'],
                    datasets: [{
                        label: 'Despesas vs Ganhos',
                        data: [totalExpense, totalIncome],
                        backgroundColor: ['#dc3545', '#28a745']
                    }]
                }
            });

            new Chart(document.getElementById('areaChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Balanço Mensal',
                        data: monthlyIncomes.map((income, index) => income - monthlyExpenses[index]),
                        fill: true,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
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
        });
    </script>
</body>
</html>
