<?php
session_start();
include 'config/db.php'; // Verifique se o caminho está correto

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(array('error' => 'Usuário não autenticado.'));
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Consulta para obter os dados do usuário
    $stmt = $pdo->prepare("SELECT 
        SUM(CASE WHEN type = 'Expense' THEN amount ELSE 0 END) as total_expense,
        SUM(CASE WHEN type = 'Income' THEN amount ELSE 0 END) as total_income
        FROM transactions WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $totals = $stmt->fetch(PDO::FETCH_ASSOC);

    // Dados para gráficos
    $stmt = $pdo->prepare("SELECT 
        category, 
        SUM(CASE WHEN type = 'Expense' THEN amount ELSE 0 END) as total_expense,
        SUM(CASE WHEN type = 'Income' THEN amount ELSE 0 END) as total_income
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

    // Preparar os dados para enviar como JSON
    $response = array(
        'categories' => $categories,
        'expenses' => $expenses,
        'incomes' => $incomes,
        'totalExpense' => $totals['total_expense'],
        'totalIncome' => $totals['total_income'],
        'balance' => $totals['total_income'] - $totals['total_expense']
    );

    // Retornar os dados como JSON
    echo json_encode($response);
} catch (PDOException $e) {
    // Lidar com erros de banco de dados
    http_response_code(500); // Internal Server Error
    echo json_encode(array('error' => 'Erro ao buscar dados do banco de dados.'));
    exit;
}
?>
