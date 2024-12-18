# Blog PHP com Docker

Este é um projeto de blog simples desenvolvido em PHP, com integração ao MySQL. O ambiente de desenvolvimento e execução é configurado usando Docker, facilitando o uso e a configuração do projeto em qualquer máquina.

## Funcionalidades

- **Login de usuário**: Sistema de autenticação para que os usuários possam acessar seu perfil.
- **Criar posts**: Usuários autenticados podem criar posts com título, conteúdo e status (rascunho ou publicado).
- **Visualizar posts**: Exibição de posts criados pelo usuário, com informações sobre o título, conteúdo e data de publicação.
- **Excluir posts**: Funcionalidade de exclusão de posts, com confirmação antes de realizar a exclusão.
- **Comentar posts**: Usuários podem comentar tanto nos seus próprios posts quanto nos posts de outros usuários. Os comentários são exibidos abaixo de cada post.
- **Excluir comentários**: Usuários podem excluir seus próprios comentários nos posts, incluindo comentários em seus próprios posts e nos posts de outros usuários.

## Requisitos

- Docker: 20.10 ou superior
- Docker Compose: 1.29 ou superior

## Instalação

### Passo 1: Clonar o repositório

```bash
git clone https://github.com/seu-usuario/blog-php.git
cd blog-php
```

### Passo 2: Configurar o Docker

Certifique-se de ter o Docker e o Docker Compose instalados. Caso não tenha, siga as instruções de instalação na documentação do Docker e Docker Compose.

Inicialize os contêineres:

```bash
docker-compose up -d
```

### Passo 3: Configurar o Banco de Dados

Acesse o contêiner do MySQL:

```bash
docker exec -it blog-php-mysql bash
```

Acesse o MySQL:

```bash
mysql -u root -p
```

Crie o banco de dados:

```sql
CREATE DATABASE blogphp;
```

Configure as credenciais no arquivo `app/db.php`:

```php
<?php
$host = 'mysql';  // Nome do serviço MySQL no docker-compose
$db = 'blogphp';  // Nome do banco de dados
$user = 'root';   // Usuário MySQL (root por padrão)
$pass = 'senha';  // Senha do MySQL definida no docker-compose
```

### Passo 4: Acessando o Projeto

Acesse a aplicação em seu navegador:

```
http://localhost
```

## Estrutura de Arquivos

```
/blog-php
    /app
        comment.php           # Página para gerenciar comentários
        create_post.php       # Página para criar novos posts
        db.php                # Configurações de conexão com o banco de dados
        index.php             # Página inicial do blog
        login.php             # Página de login
        post.php              # Página de detalhes de um post específico
        posts.php             # Página de listagem de posts
        registro.php          # Página de registro do usuário
        user.php              # Página de perfil do usuário
    /assets                   # Diretório para recursos estáticos
    docker-compose.yml        # Arquivo de configuração do Docker
    Dockerfile                # Arquivo para configurar a imagem Docker do PHP
    README.md                 # Este arquivo
```

## Uso

### Criar Usuário e Logar

- Acesse a página de login (`login.php`) e faça login com um usuário existente ou crie um novo usuário no banco de dados.
- Após o login, você será redirecionado para o painel do usuário, onde pode criar, visualizar e excluir posts.

### Criar Post

- Na página de perfil do usuário (`user.php`), você pode criar um novo post, inserindo o título, conteúdo e status do post (rascunho ou publicado).
- O post será inserido no banco de dados e estará visível na lista de posts do usuário.

### Excluir Post

- Na lista de posts, você verá um botão para excluir posts.
- Ao clicar, será exibida uma mensagem de confirmação antes de a exclusão ser realizada.

### Comentar Post

- Na página de visualização de posts, os usuários podem adicionar comentários aos posts, seja de seu próprio perfil ou de outros usuários.
- Os comentários são exibidos abaixo do conteúdo do post.

### Excluir Comentário

- Os usuários podem excluir seus próprios comentários em qualquer post, incluindo os comentários em seus próprios posts ou posts de outros usuários.

## Docker Compose

O arquivo `docker-compose.yml` contém a configuração para os contêineres necessários para rodar o projeto, incluindo PHP e MySQL.

## Contribuindo

Se você deseja contribuir com o projeto, siga as etapas abaixo:

1. Faça um fork deste repositório.
2. Crie uma nova branch (`git checkout -b feature-nome-da-feature`).
3. Faça suas alterações e faça commit (`git commit -am 'Adiciona nova funcionalidade'`).
4. Envie para o seu fork (`git push origin feature-nome-da-feature`).
5. Crie um pull request.

## Licença

Este projeto está licenciado sob a MIT License - consulte o arquivo LICENSE para mais detalhes.

---

Feito com ❤️ por Oséias