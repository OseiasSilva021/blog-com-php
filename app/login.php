<?php
session_start();

include ('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Verificar se os campos estão vazios
    if (empty($email) || empty($password)) {
        $error = "Todos os campos são obrigatórios!";
    } else {
        // Verificar se o usuário existe
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verificar a senha
            if (password_verify($password, $user['password'])) {
                // Senha correta, criar a sessão
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: user.php"); // Redirecionar para o painel do usuário
                exit;
            } else {
                $error = "Senha incorreta!";
            }
        } else {
            $error = "Usuário não encontrado!";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Blog PHP</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        h1, h3 { color: #333; }
        .form-container { max-width: 400px; margin: 0 auto; background-color: white; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input[type="email"], input[type="password"] { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #0056b3; }
        .error { color: red; }
    </style>
</head>
<body>

    <div class="form-container">
        <h1>Login</h1>

        <?php if (!empty($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Senha" required>
            <button type="submit">Entrar</button>
        </form>

        <p>Não tem uma conta? <a href="registro.php">Registre-se aqui</a></p>
    </div>

</body>
</html>
