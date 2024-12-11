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
    /* Estilos CSS (como no seu código original) */
</style>
</head>
<body>
<h1><?= htmlspecialchars($post['title']) ?></h1>
<p><?= htmlspecialchars($post['content']) ?></p>
<p>Por <?= htmlspecialchars($post['username']) ?>, em <?= date('d/m/Y', strtotime($post['created_at'])) ?></p>

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

<!-- Formulário de Comentário -->
<?php if (isset($_SESSION['user_id'])): ?>
    <form action="comment.php" method="POST">
        <textarea name="content" placeholder="Escreva seu comentário..." required></textarea><br>
        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
        <button type="submit">Comentar</button>
    </form>
<?php else: ?>
    <p>Faça login para comentar.</p>
<?php endif; ?>

</body>
</html>
