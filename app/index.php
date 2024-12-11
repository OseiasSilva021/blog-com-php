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

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $profile_picture = $stmt->fetchColumn();

    if ($profile_picture) {
        echo "<img src='" . htmlspecialchars($profile_picture) . "' alt='Foto de perfil' style='width: 70px; border-radius: 50%;'>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Blog PHP</title>
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
}

li p {
    font-size: 1em;
    color: #868e96;
    line-height: 1.6;
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
