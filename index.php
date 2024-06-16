<?php
session_start();
include 'config/db.php';

// Verifica se o método de requisição é POST (quando o formulário é enviado)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Busca usuário no banco de dados pelo username
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Verifica se o usuário existe e se a senha está correta
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id']; // Inicia a sessão com o ID do usuário
        header('Location: dashboard.php'); // Redireciona para o painel após o login
        exit;
    } else {
        $error = "Nome de usuário ou senha inválidos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gerenciador Financeiro</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h2 class="mt-5">Login</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div> <!-- Exibe mensagem de erro se houver -->
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
        <button type="submit" class="btn btn-primary">Login</button> <!-- Botão de login -->
    </form>
    <p class="mt-3">Não possui uma conta? <a href="register.php">Registre-se aqui</a></p> <!-- Link para registro -->
</div>
<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
