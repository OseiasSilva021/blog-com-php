<?php
include('db.php');
session_start();

// Verificar se um post foi solicitado
if (!isset($_GET['id'])) {
    die('Post não encontrado.');
}

$post_id = $_GET['id'];

// Buscar o post específico
$stmt = $pdo->prepare("SELECT posts.id, posts.title, posts.content, posts.created_at, users.username 
FROM posts 
INNER JOIN users ON posts.author_id = users.id 
WHERE posts.id = :id AND posts.status = 'published'");
$stmt->bindParam(':id', $post_id);
$stmt->execute();
$post = $stmt->fetch(PDO::FETCH_ASSOC);

// Buscar comentários do post
$stmt_comments = $pdo->prepare("SELECT comments.content, comments.created_at, users.username 
FROM comments 
INNER JOIN users ON comments.user_id = users.id 
WHERE comments.post_id = :post_id");
$stmt_comments->bindParam(':post_id', $post_id);
$stmt_comments->execute();
$comments = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($post['title']) ?></title>
<style>
    /* Estilos gerais */
    body {
        font-family: 'Arial', sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f8f9fa;
        color: #333;
    }

    h1 {
        text-align: center;
        font-size: 2.5em;
        margin: 20px 0;
        color: #2c3e50;
    }

    h4 {
        font-size: 1.3em;
        color: #34495e;
    }

    p {
        font-size: 1.1em;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    small {
        color: #7f8c8d;
    }

    /* Post */
    .post {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .post p {
        font-size: 1.1em;
    }

    /* Comentários */
    .comments {
        max-width: 800px;
        margin: 30px auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .comments ul {
        list-style: none;
        padding: 0;
    }

    .comments li {
        border-bottom: 1px solid #eee;
        padding: 15px 0;
    }

    .comments li:last-child {
        border-bottom: none;
    }

    .comments p {
        font-size: 1em;
        color: #34495e;
    }

    .comments small {
        font-size: 0.9em;
        color: #7f8c8d;
    }

    /* Formulário de Comentário */
    .comment-form {
        max-width: 800px;
        margin: 30px auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .comment-form textarea {
        width: 100%;
        height: 100px;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #ccc;
        font-size: 1em;
        color: #34495e;
        resize: vertical;
    }

    .comment-form button, button{
        padding: 10px 20px;
        background-color: #007bff;
        color: #fff;
        font-size: 1em;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .comment-form button:hover, button:hover {
        background-color: #0056b3;
    }

    .comment-form p {
        text-align: center;
        font-size: 1.1em;
        color: #34495e;
    }

    /* Responsividade */
    @media (max-width: 768px) {
        body {
            padding: 10px;
        }

        .post, .comments, .comment-form {
            padding: 15px;
        }

        h1 {
            font-size: 2em;
        }

        .comment-form textarea {
            height: 80px;
        }
    }
</style>
</head>
<body>

<!-- Exibir o Post -->
<div class="post">
    <h1><?= htmlspecialchars($post['title']) ?></h1>
    <p><?= htmlspecialchars($post['content']) ?></p>
    <p>Por <?= htmlspecialchars($post['username']) ?>, em <?= date('d/m/Y', strtotime($post['created_at'])) ?></p>
</div>

<!-- Exibir Comentários -->
<div class="comments">
    <h4>Comentários:</h4>
    <?php if (empty($comments)): ?>
        <p>Este post ainda não tem comentários.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($comments as $comment): ?>
                <li>
                    <p><strong><?= htmlspecialchars($comment['username']) ?>:</strong> <?= htmlspecialchars($comment['content']) ?></p>
                    <p><small>Em <?= date('d/m/Y', strtotime($comment['created_at'])) ?></small></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<!-- Formulário para Comentários -->
<?php if (isset($_SESSION['user_id'])): ?>
    <div class="comment-form">
        <form action="comment.php" method="POST">
            <textarea name="content" placeholder="Escreva seu comentário..." required></textarea><br>
            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
            <button type="submit">Comentar</button>
        </form>
    </div>
<?php else: ?>
    <p>Faça login para comentar.</p>
<?php endif; ?>

<div style="width: 55%; margin:auto; padding: 1%; display: flex; justify-content: center;">
        <a href="index.php"><button>Ir para a Página dos Posts (Página Inicial)</button></a>
    </div>
</body>
</html>
