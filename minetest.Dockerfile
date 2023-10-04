#Prend l'image de base minetest
FROM linuxserver/minetest:latest

#Copie le contenu du dossier mods (les mods avec les fichiers lua) dans le dossier mods du volume du serveur minetest
COPY ./mods /config/.minetest/mods

# Décommente et modifie secure.enable_security dans minetest.conf
RUN sed -i 's/# secure.enable_security = true/secure.enable_security = false/' /config/.minetest/main-config/minetest.conf

# Modifie load_mod_inventaire et load_mod_trade dans world.mt
RUN sed -i 's/load_mod_inventaire = false/load_mod_inventaire = true/' /config/.minetest/worlds/world/world.mt
RUN sed -i 's/load_mod_trade = false/load_mod_trade = true/' /config/.minetest/worlds/world/world.mt