<?php
session_start();
include 'config/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Consulta para obter o resumo financeiro
    $stmt = $pdo->prepare("SELECT 
        SUM(CASE WHEN type = 'Income' THEN amount ELSE 0 END) as total_income,
        SUM(CASE WHEN type = 'Expense' THEN amount ELSE 0 END) as total_expense
        FROM transactions WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $financial_summary = $stmt->fetch(PDO::FETCH_ASSOC);

    $totalIncome = $financial_summary['total_income'];
    $totalExpense = $financial_summary['total_expense'];
    $balance = $totalIncome - $totalExpense;

    // Dados anuais por categoria para o gráfico de barras
    $stmt = $pdo->prepare("SELECT 
        category, 
        SUM(CASE WHEN type = 'Expense' THEN amount ELSE 0 END) as total_expense,
        SUM(CASE WHEN type = 'Income' THEN amount ELSE 0 END) as total_income
        FROM transactions WHERE user_id = ? AND YEAR(date) = YEAR(CURRENT_DATE()) GROUP BY category");
    $stmt->execute([$user_id]);
    $annual_category_totals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $categories = [];
    $annual_expenses = [];
    $annual_incomes = [];
    foreach ($annual_category_totals as $row) {
        $categories[] = $row['category'];
        $annual_expenses[] = $row['total_expense'];
        $annual_incomes[] = $row['total_income'];
    }

    // Dados mensais para o gráfico de linha (últimos 12 meses)
    $monthly_data = [];
    for ($i = 0; $i < 12; $i++) {
        $month = date('m', strtotime("-$i months"));
        $stmt = $pdo->prepare("SELECT 
            SUM(CASE WHEN type = 'Expense' THEN amount ELSE 0 END) as total_expense,
            SUM(CASE WHEN type = 'Income' THEN amount ELSE 0 END) as total_income
            FROM transactions WHERE user_id = ? AND MONTH(date) = ?");
        $stmt->execute([$user_id, $month]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $monthly_data[] = [
            'month' => $month,
            'total_expense' => $data['total_expense'] ?? 0,
            'total_income' => $data['total_income'] ?? 0,
        ];
    }

    // Reverter a ordem para exibir do mês mais antigo para o mais recente
    $monthly_data = array_reverse($monthly_data);

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
    <title>Relatório Financeiro - Resumo Mensal e Anual</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/MonthlyPieChart.js"></script>
    <script src="assets/js/AnnualBarChart.js"></script>
    <script src="assets/js/LineChart.js"></script>
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

        <!-- Resumo Mensal e Anual -->
        <div class="row">
          <!-- Resumo Mensal: Gráfico de Pizza -->
<div class="col-md-6 mb-4 align-items-center justify-content-center">
    <div class="card container-pie-chart">
        <div class="card-header">
            Resumo Mensal
        </div>
        <div class="card-body">
            <canvas id="monthlyPieChart"></canvas>
        </div>
    </div>
</div>

<!-- Resumo Anual por Categoria: Gráfico de Barras -->
<div class="col-md-6 mb-4">
    <div class="card container-bar-chart">
        <div class="card-header">
            Resumo Anual por Categoria
        </div>
        <div class="card-body">
            <canvas id="annualBarChart"></canvas>
        </div>
    </div>
</div>

       <!-- Gráfico de Linha -->
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card text-center rounded-0 h-100"> <!-- Adicione a classe rounded-0 para remover bordas arredondadas e h-100 para altura 100% -->
            <div class="card-header">
                <h5 class="mb-0">Balanço Anual</h5>
            </div>
            <div class="card-body">
                <canvas id="lineChart"></canvas>
            </div>
        </div>
    </div>
</div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Configurar gráfico de pizza mensal
            const monthlyPieChart = new MonthlyPieChart('monthlyPieChart', <?php echo $monthly_data[0]['total_expense']; ?>, <?php echo $monthly_data[0]['total_income']; ?>);
            monthlyPieChart.init();

            // Configurar gráfico de barras anual por categoria
            const annualBarChart = new AnnualBarChart('annualBarChart', <?php echo json_encode($categories); ?>, <?php echo json_encode($annual_expenses); ?>, <?php echo json_encode($annual_incomes); ?>);
            annualBarChart.init();

            // Configurar gráfico de linha para balanço anual
            const lineChart = new LineChart('lineChart', <?php echo json_encode($categories); ?>, <?php echo json_encode($annual_expenses); ?>, <?php echo json_encode($annual_incomes); ?>, <?php echo json_encode($monthly_data); ?>);
            lineChart.init();
        });
    </script>
</body>
</html>
