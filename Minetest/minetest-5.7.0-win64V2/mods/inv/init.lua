local json = minetest.write_json

minetest.register_privilege("inventaire", {
    description = "donne acces au inventaire"
})

minetest.register_chatcommand("invt", {
    description = "enregistre les inventaire des joueurs connecté",
    params = "",
    privs = { inventaire = true },
    func = function(player_name, param)
        local all_players = minetest.get_connected_players()

        local player_inventories = {}  -- Tableau pour stocker les inventaires de tous les joueurs

        for _, player in ipairs(all_players) do
            local player_name = player:get_player_name()
            local inventory = player:get_inventory()
            local main_inventory = inventory:get_list("main")

            local item_list = {}  -- Tableau pour stocker les noms des objets avec leur quantité

            for _, itemstack in pairs(main_inventory) do
                if not itemstack:is_empty() then
                    local item_name = itemstack:get_name()
                    local item_count = itemstack:get_count()
                    table.insert(item_list, { name = item_name, count = item_count })
                end
            end

            player_inventories[player_name] = item_list
        end

        if next(player_inventories) then
            local json_str = json(player_inventories)  -- Convertir la table en chaîne JSON

            -- Enregistrement dans un fichier
            local file_path = minetest.get_worldpath() .. "/inventories.json"
            local file = io.open(file_path, "w")
            if file then
                file:write(json_str)
                file:close()
                return true, "Inventaires des joueurs enregistrés dans inventories.json."
            else
                return false, "Impossible d'ouvrir le fichier pour enregistrement."
            end
        else
            return true, "Aucun joueur connecté ou inventaires vides."
        end
    end,
})



minetest.register_chatcommand("inv", {
    description = "enregistre les inventaire du joueur en parametres",
    params = "<player_name>",
    privs = { inventaire = true },
    func = function(name, param)
        local success, message = save_inventory(param)
        if success then
            minetest.chat_send_player(name, message)
        else
            minetest.chat_send_player(name, "Erreur: " .. message)
        end
    end,
})


-- Fonction pour enregistrer l'inventaire du joueur dans un fichier JSON
local function save_inventory(player_name)
    local player = minetest.get_player_by_name(player_name)
    if not player then
        return false, "Joueur introuvable."
    end

    local inventory = player:get_inventory()
    local main_inventory = inventory:get_list("main")

    local item_list = {}  -- Tableau pour stocker les objets

    for _, itemstack in pairs(main_inventory) do
        if not itemstack:is_empty() then
            local item_name = itemstack:get_name()
            local item_count = itemstack:get_count()

            -- Créez un objet avec des attributs pour le nom et la quantité
            local item = {
                name = item_name,
                quantity = item_count
            }

            table.insert(item_list, item)
        end
    end

    if #item_list > 0 then
        -- Créez une table avec le nom du joueur et l'inventaire
        local player_inventory = {
            player_name = player_name,
            inventory = item_list
        }

        -- Convertissez la table en JSON
        local json_str = minetest.write_json(player_inventory)

        -- Définissez le chemin du fichier JSON en local
        local file_path = minetest.get_worldpath() .. "/inventory_" .. player_name .. ".json"
        local file = io.open(file_path, "w")
        if file then
            file:write(json_str)
            file:close()
            return true, "Données d'inventaire enregistrées localement dans le fichier."
        else
            return false, "Impossible d'ouvrir le fichier pour enregistrement local."
        end
    else
        return true, "L'inventaire du joueur est vide."
    end
end

-- -- Hook pour gérer lorsqu'un joueur fais un clique gauche
-- minetest.register_on_punchnode(function(pos, node, puncher, pointed_thing)
-- 	local player_name = puncher:get_player_name()
-- 	save_inventory(player_name)
-- end)

-- Hook pour gérer lorsqu'un joueur casse un block
minetest.register_on_dignode(function(pos, oldnode, digger)
	local player_name = digger:get_player_name()
	save_inventory(player_name)
end)

-- Hook pour gérer lorsqu'un joueur place un block
minetest.register_on_placenode(function(pos, newnode, placer, oldnode, itemstack, pointed_thing)
	local player_name = placer:get_player_name()
	save_inventory(player_name)
end)

-- Hook pour gérer lorsqu'un joueur fabrique un item
minetest.register_on_craft(function(itemstack, player, old_craft_grid, craft_inv)
	local player_name = player:get_player_name()
	save_inventory(player_name)
end)
