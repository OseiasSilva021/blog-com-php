<?php
// Conexão com o banco de dados
include ('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);

    // Verificar se os campos estão vazios
    if (empty($username) || empty($password) || empty($email)) {
        $error = "Todos os campos são obrigatórios!";
    } else {
        // Verificar se o email já existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $error = "Este email já está registrado!";
        } else {
            // Hash da senha
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Inserir o novo usuário no banco de dados
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->execute();

            $success = "Registro realizado com sucesso! Agora você pode fazer login.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Blog PHP</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        h1, h3 { color: #333; }
        .form-container { max-width: 400px; margin: 0 auto; background-color: white; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input[type="text"], input[type="password"], input[type="email"] { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #0056b3; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>

    <div class="form-container">
        <h1>Registro de Usuário</h1>
        
        <?php if (!empty($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php elseif (!empty($success)): ?>
            <p class="success"><?= $success ?></p>
        <?php endif; ?>

        <form action="registro.php" method="POST">
            <input type="text" name="username" placeholder="Nome de Usuário" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Senha" required>
            <button type="submit">Registrar</button>
        </form>

        <p>Já tem uma conta? <a href="login.php">Faça login aqui</a></p>
    </div>

</body>
</html>
