<?php
session_start();
include('db.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo "Você não está logado!";
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    // Inserir o post no banco de dados
    $stmt = $pdo->prepare("INSERT INTO posts (title, content, author_id, status, created_at) 
                           VALUES (:title, :content, :author_id, 'published', NOW())");
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':author_id', $user_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        header("Location: user.php"); // Redirecionar após a criação do post
        exit();
    } else {
        echo "Erro ao criar o post.";
    }
}
?>
