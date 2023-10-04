#Prend l'image de base minetest
FROM linuxserver/minetest:latest

#Copie le contenu du dossier mods (les mods avec les fichiers lua) dans le dossier mods du volume du serveur minetest
COPY ./mods /config/.minetest/mods