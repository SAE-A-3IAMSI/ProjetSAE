# Utilise l'image de base php:apache
FROM php:apache

# Définit le shell pour l'exécution des commandes
SHELL ["/bin/bash", "-c"]

# Active les modules Apache expires, headers, et rewrite en créant des liens symboliques dans mods-enabled
RUN ln -s ../mods-available/{expires,headers,rewrite}.load /etc/apache2/mods-enabled/

# Modifie la configuration d'Apache pour autoriser les directives d'override dans le répertoire /var/www/
RUN sed -e '/<Directory \/var\/www\/>/,/<\/Directory>/s/AllowOverride None/AllowOverride All/' -i /etc/apache2/apache2.conf

# Copie le fichier php.ini personnalisé dans le répertoire de configuration PHP
COPY ./php.ini /usr/local/etc/php/

# Copie des scripts PHP sur le serveur Apache
COPY ./html /var/www/html