<?php
session_start();

// Verificar se o usuário está logado (se o ID do usuário está na sessão)
if (!isset($_SESSION['user_id'])) {
    echo "Você não está logado!";
    exit;
}

$user_id = $_SESSION['user_id']; // Obtém o ID do usuário da sessão

// Conexão com o banco de dados
include ('db.php');

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
$stmt = $pdo->prepare("SELECT posts.id, posts.title, posts.content, posts.created_at, posts.author_id 
                       FROM posts 
                       WHERE posts.author_id = :user_id 
                       AND posts.status = 'published' 
                       ORDER BY posts.created_at DESC");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar se o post está sendo deletado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post_id'])) {
    $post_id = $_POST['delete_post_id'];

    // Preparar a consulta para excluir o post
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :post_id AND author_id = :user_id");
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

    // Executar a consulta
    if ($stmt->execute()) {
        // Redirecionar para evitar reenvio de formulário
        header("Location: user.php");
        exit();
    } else {
        echo "Erro ao deletar o post.";
    }
}

// Deletar comentário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment_id'])) {
    $comment_id = $_POST['delete_comment_id'];

    // Preparar a consulta para excluir o comentário
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = :comment_id AND post_id IN (SELECT id FROM posts WHERE author_id = :user_id)");
    $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

    // Executar a consulta
    if ($stmt->execute()) {
        // Redirecionar para evitar reenvio de formulário
        header("Location: user.php");
        exit();
    } else {
        echo "Erro ao deletar o comentário.";
    }
}

// Logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_unset(); // Limpa todas as variáveis de sessão
    session_destroy(); // Destroi a sessão
    header("Location: login.php"); // Redireciona para a página de login
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?= htmlspecialchars($user['username']) ?> - Blog PHP</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 20px;
        color: #333;
    }

    h1, h2 {
        text-align: center;
        color: #2c3e50;
    }

    h1 {
        font-size: 2.5em;
        margin-bottom: 10px;
    }

    h2 {
        font-size: 1.8em;
        margin-top: 40px;
        margin-bottom: 20px;
    }

    .user-info {
        background-color: #ffffff;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }

    .user-info p {
        margin: 5px 0;
        font-size: 1.1em;
    }

    .post-list {
        list-style-type: none;
        padding: 0;
        max-width: 800px;
        margin: 0 auto;
    }

    .post-list li {
        background-color: #ffffff;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease;
    }

    .post-list li:hover {
        transform: translateY(-5px);
    }

    .post-list h3 {
        font-size: 1.6em;
        margin-top: 0;
        color: #007bff;
    }

    .post-list h4 {
        font-size: 1.1em;
        color: #7f8c8d;
        margin: 10px 0;
    }

    .post-list p {
        font-size: 0.9em;
        color: #95a5a6;
    }

    .post-list a {
        color: #007bff;
        text-decoration: none;
    }

    .post-list a:hover {
        text-decoration: underline;
    }

    button {
        padding: 10px 15px;
        font-size: 16px;
        background-color: #007bff;
        color: white;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        transition: background-color 0.3s;
    }

    button:hover {
        background-color: #0056b3;
    }

    form {
        background-color: #ffffff;
        padding: 20px;
        margin-top: 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        max-width: 800px;
        margin-left: auto;
        margin-right: auto;
    }

    label {
        font-size: 1.1em;
        display: block;
        margin-bottom: 5px;
    }

    input[type="text"], textarea, select {
        width: 100%;
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 1em;
    }

    textarea {
        resize: vertical;
        min-height: 150px;
    }

    input[type="text"]:focus, textarea:focus, select:focus {
        border-color: #007bff;
        outline: none;
    }

    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .form-actions button {
        width: auto;
    }

    .delete-form {
        display: inline-block;
    }

    @media (max-width: 768px) {
        body {
            padding: 10px;
        }

        h1 {
            font-size: 2em;
        }

        h2 {
            font-size: 1.5em;
        }

        .user-info, .post-list, form {
            padding: 15px;
        }

        .post-list li {
            padding: 12px;
        }
    }
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
            <h4><?= htmlspecialchars($post['content']) ?></h4>
            <p>Publicado em: <?= date('d/m/Y H:i', strtotime($post['created_at'])) ?></p>

            <!-- Formulário de exclusão do post -->
            <form action="" method="POST" onsubmit="return confirm('Você tem certeza que deseja excluir este post?');">
                <input type="hidden" name="delete_post_id" value="<?= $post['id'] ?>">
                <button type="submit">Deletar Post</button>
            </form>

            <!-- Exibir comentários -->
            <h4>Comentários:</h4>
            <?php
                // Buscar comentários do post
                $stmt_comments = $pdo->prepare("SELECT comments.id, comments.content, comments.created_at, users.username 
                                                FROM comments 
                                                INNER JOIN users ON comments.user_id = users.id 
                                                WHERE comments.post_id = :post_id");
                $stmt_comments->bindParam(':post_id', $post['id']);
                $stmt_comments->execute();
                $comments = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <?php if (empty($comments)): ?>
                <p>Este post ainda não tem comentários.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($comments as $comment): ?>
                    <li>
                        <p><strong><?= htmlspecialchars($comment['username']) ?>:</strong> <?= htmlspecialchars($comment['content']) ?></p>
                        <p><small>Em <?= date('d/m/Y', strtotime($comment['created_at'])) ?></small></p>

                        <!-- Formulário de exclusão do comentário -->
                        <form action="" method="POST" onsubmit="return confirm('Você tem certeza que deseja excluir este comentário?');">
                            <input type="hidden" name="delete_comment_id" value="<?= $comment['id'] ?>">
                            <button type="submit">Deletar Comentário</button>
                        </form>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

        </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <!-- Formulário de logout -->
    <form action="" method="POST">
        <button type="submit" name="logout">Sair da Conta (Logout)</button>
    </form>

    <div style="width: 55%; margin:auto; padding: 1%; display: flex; justify-content: center;">
        <a href="index.php"><button type="submit">Ir para a Página dos Posts (Página Inicial)</button></a>
    </div>
</body>
</html>

