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
$stmt = $pdo->prepare("SELECT posts.id, posts.title, posts.content, posts.created_at 
                       FROM posts 
                       WHERE posts.author_id = :user_id 
                       AND posts.status = 'published' 
                       ORDER BY posts.created_at DESC");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar se o post está sendo criado
    if (isset($_POST['title'], $_POST['content'])) {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $status = $_POST['status'] ?? 'draft';

        // Validar os dados
        if (empty($title) || empty($content)) {
            echo "Título e conteúdo são obrigatórios!";
        } else {
            // Inserir o post no banco de dados
            $stmt = $pdo->prepare("INSERT INTO posts (title, content, author_id, status) VALUES (:title, :content, :author_id, :status)");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':author_id', $user_id);
            $stmt->bindParam(':status', $status);
            $stmt->execute();

            echo "Post criado com sucesso!";
        }
    }

    // Verificar se o post está sendo deletado
    if (isset($_POST['delete_post_id'])) {
        $post_id = $_POST['delete_post_id'];

        // Preparar a consulta para excluir o post
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :post_id AND author_id = :user_id");
        $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        // Executar a consulta
        if ($stmt->execute()) {
            // Redirecionar para evitar reenvio de formulário e não mostrar mensagens de sucesso/erro após redirecionamento
            header("Location: user.php");
            exit();
        } else {
            echo "Erro ao deletar o post.";
        }
    }
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

        <!-- Formulário de exclusão -->
        <form action="" method="POST" onsubmit="return confirm('Você tem certeza que deseja excluir este post?');">
            <input type="hidden" name="delete_post_id" value="<?= $post['id'] ?>">
            <button type="submit">Deletar Post</button>
        </form>
    </li>
<?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="" method="POST">
    <label for="title">Título:</label>
    <input type="text" name="title" id="title" required>
    <br><br>
    
    <label for="content">Conteúdo:</label>
    <textarea name="content" id="content" required></textarea>
    <br><br>

    <label for="status">Status:</label>
    <select name="status" id="status">
        <option value="draft">Rascunho</option>
        <option value="published">Publicado</option>
    </select>
    <br><br>

    <button type="submit">Criar Post</button>
</form>

</body>
</html>
