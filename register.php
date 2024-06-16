<?php
include 'config/db.php';

// Verifica se o método de requisição é POST (quando o formulário é enviado)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT); // Gera o hash da senha

    try {
        // Prepara e executa a inserção do usuário no banco de dados
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hashedPassword]);
        header('Location: index.php'); // Redireciona para a página de login após o registro
        exit;
    } catch (PDOException $e) {
        $error = "Erro: " . $e->getMessage(); // Captura e exibe mensagem de erro se houver falha na inserção
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar - Gerenciador Financeiro</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h2 class="mt-5">Registrar</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div> <!-- Exibe mensagem de erro se houver falha no registro -->
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label for="username">Usuário:</label>
            <input type="text" name="username" id="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Senha:</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Registrar</button> <!-- Botão de registro -->
    </form>
    <p class="mt-3">Já possui uma conta? <a href="index.php">Faça login aqui</a></p> <!-- Link para página de login -->
</div>
<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
