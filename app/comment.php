<?php
session_start();
include('db.php');

// Verificar se o usuário está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'];
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id'];

    // Inserir o comentário no banco de dados
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (:post_id, :user_id, :content)");
    $stmt->bindParam(':post_id', $post_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':content', $content);
    $stmt->execute();

    // Redirecionar para a página do post
    header("Location: post.php?id=$post_id");
    exit;
}
?>
