FROM php:8.2-apache

# Instalar dependências necessárias
RUN apt-get update && apt-get install -y \
  libfreetype6-dev \
  libjpeg62-turbo-dev \
  libmcrypt-dev \
  libpng-dev \
  zlib1g-dev \
  libxml2-dev \
  libzip-dev \
  libonig-dev \
  graphviz

# Instalar extensões do PHP
RUN docker-php-ext-install -j$(nproc) \
  gd \
  mbstring \
  mysqli \
  pdo \
  pdo_mysql \
  zip

# Habilitar mod_rewrite do Apache
RUN a2enmod rewrite

# Copiar arquivo de configuração do Apache
# COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Reiniciar serviço do Apache
# RUN service apache2 restart

# Define o diretório de trabalho
WORKDIR /var/www/html

# Expor porta do Apache
EXPOSE 80
