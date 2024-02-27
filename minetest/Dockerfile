
#Prend l'image de base minetest
FROM linuxserver/minetest:5.7.0-ls129

#Copie le contenu du dossier mods (les mods avec les fichiers lua) dans le dossier mods du volume du serveur minetest
COPY ./mods /config/.minetest/mods

# Copie du fichier de configuration minetest.conf
COPY ./minetest.conf /config/.minetest/main-config/minetest.conf

# Copie du fichier de configuration du monde Minetest
COPY ./world.mt /config/.minetest/worlds/world/world.mt