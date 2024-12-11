<?php
include('db.php');
session_start();

// Buscar todos os posts publicados
$stmt = $pdo->prepare(
    "SELECT DISTINCT posts.id, posts.title, posts.content, posts.created_at, users.username 
    FROM posts 
    INNER JOIN users ON posts.author_id = users.id 
    WHERE posts.status = 'published' 
    ORDER BY posts.created_at DESC"
);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar comentários de cada post
foreach ($posts as $key => $post) {
    $stmt_comments = $pdo->prepare(
        "SELECT comments.content, comments.created_at, users.username 
        FROM comments 
        INNER JOIN users ON comments.user_id = users.id 
        WHERE comments.post_id = :post_id"
    );
    $stmt_comments->bindParam(':post_id', $post['id']);
    $stmt_comments->execute();
    $posts[$key]['comments'] = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Blog PHP</title>
<style>
/* Estilos */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 20px;
    background-color: #f4f4f4;
    color: #333;
}

h1, h3 {
    color: #2c3e50;
    text-align: center;
}

h1 {
    font-size: 2.5em;
    margin-bottom: 10px;
}

h3 {
    font-size: 1.3em;
    margin-bottom: 30px;
}

button {
    padding: 10px 20px;
    font-size: 16px;
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    text-align: center;
    display: block;
    margin: 0 auto;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #0056b3;
}

a {
    text-decoration: none;
}

ul {
    list-style-type: none;
    padding: 0;
    max-width: 800px;
    margin: 0 auto;
}

li {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    padding: 20px;
    transition: transform 0.3s;
}

li:hover {
    transform: translateY(-5px);
}

li h3 {
    margin-top: 0;
    font-size: 1.8em;
    color: #007bff;
}

li h4 {
    font-size: 1.1em;
    color: #7f8c8d;
    margin: 10px 0;
}

li p {
    font-size: 0.9em;
    color: #95a5a6;
}

@media (max-width: 768px) {
    body {
        padding: 10px;
    }

    h1 {
        font-size: 2em;
    }

    h3 {
        font-size: 1.1em;
    }

    ul {
        padding: 0 10px;
    }

    li {
        padding: 15px;
    }
}
</style>
</head>
<body>
<h1>Seja bem-vindo ao Blog!</h1>
<h3>Aqui você verá as postagens criadas pelos usuários autenticados. Caso queira criar posts, autentique-se.</h3>
<a href="registro.php"><button>Página de Registro</button></a>
<a href="user.php" style="margin: 1%;"><button>Vá para a Página do Usuário (caso já esteja logado)</button></a>

<?php if (empty($posts)): ?>
    <p>Ainda não há posts publicados.</p>
<?php else: ?>
    <ul>
        <?php foreach ($posts as $index => $post): ?>
            <li>
                <h3><a href="post.php?id=<?= $post['id'] ?>">[Post <?= $index + 1 ?>] <?= htmlspecialchars($post['title']) ?></a></h3>
                <h4><?= htmlspecialchars($post['content']) ?></h4>
                <p>Por <?= htmlspecialchars($post['username']) ?>, em <?= date('d/m/Y', strtotime($post['created_at'])) ?></p>

                <!-- Exibir Comentários -->
                <h4>Comentários:</h4>
                <?php if (empty($post['comments'])): ?>
                    <p>Este post ainda não tem comentários.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($post['comments'] as $comment): ?>
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
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
</body>
</html>
