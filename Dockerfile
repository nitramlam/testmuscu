# Utiliser l'image PHP officielle (sans Apache)
FROM php:7.4-cli

# Installer les extensions PHP nécessaires pour se connecter à MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copier ton code source dans le container
COPY . /var/www/html/

# Définir le répertoire de travail
WORKDIR /var/www/html

# Exposer le port 80 (utile si tu veux faire un petit serveur avec PHP - mais pas nécessaire ici)
EXPOSE 80