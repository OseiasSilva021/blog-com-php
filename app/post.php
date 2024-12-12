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
    /* Estilos Gerais */
body {
    font-family: 'Roboto', Arial, sans-serif;
    margin: 0;
    padding: 20px;
    background: linear-gradient(to bottom, #f8f9fa, #e9ecef);
    color: #343a40;
}

h1, h3 {
    color: #495057;
    text-align: center;
}

h1 {
    font-size: 2.8em;
    margin-bottom: 15px;
}

h3 {
    font-size: 1.5em;
    margin-bottom: 25px;
}

button {
    padding: 10px 25px;
    font-size: 16px;
    background: linear-gradient(to right, #007bff, #0056b3);
    color: white;
    border: none;
    border-radius: 50px;
    cursor: pointer;
    text-align: center;
    display: inline-block;
    margin: 10px auto;
    transition: all 0.3s ease;
}

button:hover {
    background: linear-gradient(to right, #0056b3, #003f8a);
    transform: scale(1.05);
}

a {
    text-decoration: none;
}

ul {
    list-style-type: none;
    padding: 0;
    max-width: 900px;
    margin: 0 auto;
}

li {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 25px;
    padding: 25px;
    transition: transform 0.3s, box-shadow 0.3s;
    overflow: hidden; /* Adiciona para prevenir o vazamento de conteúdo */
    text-overflow: ellipsis; /* Limita o texto com reticências se necessário */
    word-wrap: break-word; /* Quebra palavras longas */
    max-height: 800px; /* Limita a altura máxima */
}

li:hover {
    transform: translateY(-8px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

li h3 {
    margin-top: 0;
    font-size: 2em;
    color: #007bff;
    text-decoration: none;
}

li h4 {
    font-size: 1.2em;
    color: #6c757d;
    margin: 15px 0;
    word-wrap: break-word; /* Quebra palavras longas */
}

li p {
    font-size: 1em;
    color: #868e96;
    line-height: 1.6;
    word-wrap: break-word; /* Quebra palavras longas */
    overflow-wrap: break-word; /* Quebra palavras longas */
}

textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ced4da;
    border-radius: 8px;
    margin-top: 10px;
    font-size: 14px;
    resize: none;
}

textarea:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

form button {
    margin-top: 10px;
    width: 100%;
}

img {
    display: block;
    margin: 0 auto 20px;
    border-radius: 50%;
    width: 70px;
    height: 70px;
    object-fit: cover;
}

@media (max-width: 768px) {
    body {
        padding: 15px;
    }

    h1 {
        font-size: 2.2em;
    }

    h3 {
        font-size: 1.2em;
    }

    ul {
        padding: 0 10px;
    }

    li {
        padding: 20px;
    }

    button {
        font-size: 14px;
        padding: 8px 20px;
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
