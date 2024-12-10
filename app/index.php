<?php
$host = 'mysql';
$db = 'blogphp';
$user = 'usuario';
$pass = '123456';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conexão com o banco de dados estabelecida com sucesso!";
} catch (PDOException $e) {
    echo "Erro ao conectar com o banco de dados: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog PHP</title>
    <style>

        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        h1, h3 { color: #333; }
        button { padding: 10px 15px; font-size: 16px; background-color: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <h1>
        Seja bem vindo ao Blog!
    </h1>
    <h3>
        Aqui você verá as postagens criadas pelos usuários autenticados. Caso você queira criar posts, autentique-se
    </h3>
    <a href="registro.php"><button>Página de Registro</button></a>
</body>
</html>