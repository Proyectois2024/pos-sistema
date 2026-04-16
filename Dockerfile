FROM php:8.2-apache

# Activar mod_rewrite
RUN a2enmod rewrite

# Desactivar mpm_event y activar prefork
RUN a2dismod mpm_event && a2enmod mpm_prefork

# Instalar extensiones necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar archivos
COPY . /var/www/html/

# Permisos
RUN chown -R www-data:www-data /var/www/html
