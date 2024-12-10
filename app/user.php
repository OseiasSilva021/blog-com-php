<?php
session_start();

// Verificar se o usuário está logado (se o ID do usuário está na sessão)
if (!isset($_SESSION['user_id'])) {
    echo "Você não está logado!";
    exit;
}

$user_id = $_SESSION['user_id']; // Obtém o ID do usuário da sessão

// Conexão com o banco de dados
$host = 'mysql';
$db = 'blogphp';
$user = 'usuario';
$pass = '123456';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erro ao conectar com o banco de dados: " . $e->getMessage();
    exit;
}

// Buscar informações do usuário com o ID armazenado na sessão
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Se o usuário não existir, exibe uma mensagem
if (!$user) {
    echo "Usuário não encontrado.";
    exit;
}

// Buscar os posts do usuário
$stmt = $pdo->prepare("SELECT posts.id, posts.title, posts.created_at 
                       FROM posts 
                       WHERE posts.author_id = :user_id 
                       AND posts.status = 'published' 
                       ORDER BY posts.created_at DESC");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?= htmlspecialchars($user['username']) ?> - Blog PHP</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        h1 { color: #333; }
        .user-info { background-color: white; padding: 20px; margin-bottom: 20px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .user-info p { margin: 5px 0; }
        .post-list { list-style-type: none; padding: 0; }
        .post-list li { background-color: white; padding: 10px; margin-bottom: 10px; border-radius: 4px; box-shadow: 0 0 5px rgba(0, 0, 0, 0.1); }
        .post-list a { color: #007bff; text-decoration: none; }
        .post-list a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <div class="user-info">
        <h1>Perfil de <?= htmlspecialchars($user['username']) ?></h1>
        <p>Email: <?= htmlspecialchars($user['email']) ?></p>
        <p>Membro desde: <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
    </div>

    <h2>Posts de <?= htmlspecialchars($user['username']) ?></h2>

    <?php if (empty($posts)): ?>
        <p>Este usuário ainda não publicou nenhum post.</p>
    <?php else: ?>
        <ul class="post-list">
            <?php foreach ($posts as $post): ?>
                <li>
                    <h3><a href="post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h3>
                    <p>Publicado em: <?= date('d/m/Y H:i', strtotime($post['created_at'])) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

</body>
</html>
