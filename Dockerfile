# Imagem base do PHP com Apache
FROM php:8.2-apache

# Atualizar o sistema e instalar dependências
RUN apt-get update && apt-get install -y \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql
