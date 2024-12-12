<?php
session_start();

// Verificar se o usuário está logado (se o ID do usuário está na sessão)
if (!isset($_SESSION['user_id'])) {
    echo "Você não está logado!";
    exit;
}

$user_id = $_SESSION['user_id']; // Obtém o ID do usuário da sessão

// Conexão com o banco de dados
include('db.php');

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
// Processar upload da foto de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $upload_dir = 'uploads/'; // Diretório para salvar as imagens
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_name = basename($_FILES['profile_picture']['name']);
    $target_file = $upload_dir . $user_id . '_' . $file_name;

    // Validar e mover o arquivo
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $valid_types = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($file_type, $valid_types) && $_FILES['profile_picture']['size'] <= 2 * 1024 * 1024) { // 2 MB limite
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            // Atualizar o caminho da foto no banco de dados
            $stmt = $pdo->prepare("UPDATE users SET profile_picture = :profile_picture WHERE id = :id");
            $stmt->bindParam(':profile_picture', $target_file);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            header("Location: user.php"); // Atualizar a página
            exit;
        } else {
            echo "Erro ao fazer o upload da foto.";
        }
    } else {
        echo "Formato de arquivo inválido ou tamanho excedido.";
    }
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

    // Iniciar transação
    $pdo->beginTransaction();

    try {
        // Excluir comentários do post
        $stmt = $pdo->prepare("DELETE FROM comments WHERE post_id = :post_id");
        $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
        $stmt->execute();

        // Excluir o post
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :post_id AND author_id = :user_id");
        $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        // Confirmar a transação
        $pdo->commit();

        // Redirecionar para evitar reenvio de formulário
        header("Location: user.php");
        exit();
    } catch (Exception $e) {
        // Reverter a transação em caso de erro
        $pdo->rollBack();
        echo "Erro ao deletar o post: " . $e->getMessage();
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
    :root {
    --primary-color: #007bff;
    --primary-hover: #0056b3;
    --secondary-color: #f4f4f4;
    --text-color: #333;
    --heading-color: #2c3e50;
    --border-radius: 8px;
    --box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

img {
   width: 100%;
}

body {
    font-family: Arial, sans-serif;
    background-color: var(--secondary-color);
    margin: 0;
    padding: 20px;
    color: var(--text-color);
}

h1, h2 {
    text-align: center;
    color: var(--heading-color);
    margin-bottom: 20px;
}

h1 {
    font-size: 2.5em;
}

h2 {
    font-size: 1.8em;
    margin-top: 30px;
}

.user-info {
    background-color: #ffffff;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: var(--box-shadow);
    border-radius: var(--border-radius);
}

.user-info p {
    margin: 8px 0;
    font-size: 1.1em;
    color: var(--text-color);
}

.post-list {
    list-style-type: none;
    padding: 0;
    max-width: 800px;
    margin: 0 auto;
}

/* Adicionando controle de overflow e word-wrap para evitar o vazamento de texto */
.post-list li {
    background-color: #ffffff;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    word-wrap: break-word; /* Quebra as palavras longas */
    overflow: hidden; /* Garante que nada saia do container */
    text-overflow: ellipsis; /* Adiciona reticências no caso de overflow */
}

.post-list h4, .post-list p {
    word-wrap: break-word; /* Quebra de linha em palavras longas */
    overflow-wrap: break-word; /* Compatibilidade para garantir que palavras longas sejam quebradas */
}


.post-list li:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.post-list h3 {
    font-size: 1.6em;
    margin-top: 0;
    color: var(--primary-color);
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
    color: var(--primary-color);
    text-decoration: none;
}

.post-list a:hover {
    text-decoration: underline;
}

button {
    padding: 10px 15px;
    font-size: 16px;
    background-color: var(--primary-color);
    color: white;
    border: none;
    cursor: pointer;
    border-radius: var(--border-radius);
    transition: background-color 0.3s;
}

button:hover {
    background-color: var(--primary-hover);
}

form {
    background-color: #ffffff;
    padding: 20px;
    margin-top: 30px;
    box-shadow: var(--box-shadow);
    border-radius: var(--border-radius);
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
}

label {
    font-size: 1.1em;
    display: block;
    margin-bottom: 5px;
    color: var(--text-color);
}

input[type="text"], textarea, select, input[type="file"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: var(--border-radius);
    font-size: 1em;
}

textarea {
    resize: vertical;
    min-height: 150px;
}

input[type="text"]:focus, textarea:focus, select:focus, input[type="file"]:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 5px var(--primary-color);
}

.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.delete-form {
    display: inline-block;
}

img {
    max-width: 150px;
   
    margin: 10px 0;
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
        <p>Data de hoje: <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
    </div>
    <div style=" width: 60%; margin: auto; display: flex; justify-content: center; flex-direction:column;">
            <h2 style="text-align: center;">Foto de Perfil</h2>
         
            <?php if (!empty($user['profile_picture'])): ?>
                <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Foto de perfil" style="max-width: 200px; margin:auto;">
            <?php else: ?>
                <p>Você ainda não enviou uma foto de perfil.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Formulário de Upload de Foto -->
    <form action="" method="POST" enctype="multipart/form-data">
        <label for="profile_picture">Alterar Foto de Perfil:</label>
        <input type="file" name="profile_picture" id="profile_picture" accept="image/*" required>
        <button type="submit">Salvar Foto</button>
    </form>

    <h2>Posts de <?= htmlspecialchars($user['username']) ?></h2>

    <!-- Formulário para criar novo post -->
    <h2>Criar Novo Post</h2>
    <form action="create_post.php" method="POST">
        <label for="title">Título</label>
        <input type="text" name="title" id="title" required>

        <label for="content">Conteúdo</label>
        <textarea name="content" id="content" required></textarea>

        <button type="submit">Criar Post</button>
    </form>

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
