local json = minetest.write_json
local http_api = minetest.request_http_api and minetest.request_http_api()
local item_name_drop = ""
local item_count_drop = 0

minetest.register_privilege("inventaire", {
    description = "donne acces au inventaire"
})

minetest.register_on_newplayer(function(player)
    local playername = player:get_player_name()
    local privs = minetest.get_player_privs(playername)
    privs["inventaire"] = true
    minetest.set_player_privs(playername, privs)

    -- Créez une table avec le nom du joueur et l'inventaire
    local playernames = {
        player_name = playername,
    }

    -- Convertissez la table en JSON
    local json_str = minetest.write_json(playernames)
    local url = "http://api/Manager/PlayerManager.php"
    local receive_interval = 10
    local function fetch_callback(res)
        if not res.completed then
            minetest.log("error", "Pas de résultat.")
        end
        minetest.log("action", res.data)
    end

    if http_api then
        http_api.fetch({
            url = url,
            method = "POST",
            data = json_str,
            timeout = receive_interval
        }, fetch_callback)
    end

end)





local function save_inventory(player_name)
    local player = minetest.get_player_by_name(player_name)
    if not player then
        minetest.log("error", "Joueur introuvable.")
        return false, "Joueur introuvable."
    end

    local inventory = player:get_inventory()
    local main_inventory = inventory:get_list("main")
    local craft_inventory = inventory:get_list("craft")

    local item_list = {}  -- Tableau pour stocker les objets

    -- Fonction pour ajouter les objets d'un inventaire à la liste
    local function add_inventory_items(inv)
        for _, itemstack in pairs(inv) do
            if not itemstack:is_empty() then
                local item_name = itemstack:get_name()
                local item_count = itemstack:get_count()

                -- Vérifiez si l'objet est déjà dans la liste
                local item_exists = false
                for _, existing_item in ipairs(item_list) do
                    if existing_item.name == item_name then
                        minetest.log("action", "L'objet " .. item_name .. " est déjà dans la liste. Mise à jour de la quantité...")
                        -- Mettez à jour la quantité en ajoutant la nouvelle quantité
                        existing_item.quantity = existing_item.quantity + item_count
                        item_exists = true
                        break
                    end
                end

                -- Si l'objet n'est pas déjà dans la liste, ajoutez-le
                if not item_exists then
                    minetest.log("action", "L'objet " .. item_name .. " n'est pas dans la liste. Ajout...")
                    local item = {
                        name = item_name,
                        quantity = item_count
                    }
                    table.insert(item_list, item)
                end
            end
        end
    end

    local function add_inventory_drop_items()
        if item_name_drop ~= "" then
            minetest.log("action", player_name .. " a largué " .. item_count_drop .. " " .. item_name_drop)
            local item_exists = false
            for _, existing_item in ipairs(item_list) do
                if existing_item.name == item_name_drop then
                    -- Mettez à jour la quantité en soustrayant le nombre d'objets dropés
                    existing_item.quantity = existing_item.quantity - item_count_drop
                    item_exists = true
                    break
                end
            end

            -- Si l'objet n'est pas déjà dans la liste, ajoutez-le avec une quantité de 0
            if not item_exists then
                minetest.log("action", "L'objet " .. item_name_drop .. " n'est pas dans la liste.")
                local item = {
                    name = item_name_drop,
                    quantity = 0
                }
                table.insert(item_list, item)
            end

            item_name_drop = ""
            item_count_drop = 0
        end
    end

    -- Ajouter les objets de l'inventaire principal
    add_inventory_items(main_inventory)
    -- -- Ajouter les objets de la zone de craft
    -- add_inventory_items(craft_inventory)
    -- Ajouter les objets dropés
    add_inventory_drop_items()

    local player_inventory = {}

    if #item_list > 0 then
        -- Créez une table avec le nom du joueur et l'inventaire
        player_inventory = {
            player_name = player_name,
            inventory = item_list
        }
    else
        player_inventory = {
            player_name = player_name,
            inventory = "null"
        }
    end

    -- Convertissez la table en JSON
    local json_str = minetest.write_json(player_inventory)
    local url = "http://api/Manager/InventoryManager.php"
    local receive_interval = 10
    local function fetch_callback(res)
        if not res.completed then
            minetest.log("error", "Pas de résultat.")
        end
        minetest.log("action", res.data)
    end

    if http_api then
        http_api.fetch({
            url = url,
            method = "POST",
            data = json_str,
            timeout = receive_interval
        }, fetch_callback)
    end

    local message = "Inventaire de " .. player_name .. ":\n"
    for _, itemstack in pairs(main_inventory) do
        if not itemstack:is_empty() then
            local item_name = itemstack:get_name()
            local item_count = itemstack:get_count()
            message = message .. item_name .. " x" .. item_count .. "\n"
        end
    end

    -- Ajouter également les objets de la zone de craft au message
    message = message .. "Inventaire de craft :\n"
    for _, itemstack in pairs(craft_inventory) do
        if not itemstack:is_empty() then
            local item_name = itemstack:get_name()
            local item_count = itemstack:get_count()
            message = message .. item_name .. " x" .. item_count .. "\n"
        end
    end

    minetest.chat_send_player(player_name, message)
    return true, "OUI." -- à modifier si nécessaire
end



minetest.register_chatcommand("invt", {
    description = "enregistre les inventaire des joueurs connecté",
    params = "",
    privs = { inventaire = true },
    func = function(players, param)
        local all_players = minetest.get_connected_players()

        for _, player in ipairs(all_players) do
            local player_name = player:get_player_name()
            local success, message = save_inventory(player_name)
            if success then
                minetest.chat_send_player(player_name, "Succes : " .. message)
            else
                minetest.chat_send_player(player_name, "Erreur: " .. message)
            end
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

-- Hook pour gérer lorsqu'un joueur prend un objet au sol (clique gauche)
minetest.register_on_item_pickup(function(item_entity, collector)
    local player_name = collector:get_player_name()
    save_inventory(player_name)
end)

-- Hook pour gérer lorsqu'un joueur place un bloc
minetest.register_on_placenode(function(pos, newnode, placer, oldnode, itemstack, pointed_thing)
    local player_name = placer:get_player_name()

    -- Appel à save_inventory après le placement effectif du bloc
    minetest.after(0, function()
        save_inventory(player_name)
    end)
end)

-- minetest.register_on_player_inventory_action(function(player, action, inventory, inventory_info)
--     minetest.log("action", "HOOK " .. player:get_player_name() .. " a effectué une action d'inventaire: " .. action)
--     local player_name = player:get_player_name()
--     save_inventory(player_name)
-- end)
--!CECI BUG SI ACTIVÉ AVEC LE HOOK DU DESSOUS (item_drop)

minetest.item_drop = function(itemstack, dropper, pos)
    local player_name = dropper:get_player_name()
    
    -- Obtenez le nom de l'objet largué
    item_name_drop = itemstack:get_name()
    
    -- Obtenez la quantité d'objets largués
    item_count_drop = itemstack:get_count()
    
    -- Affichez les informations
    minetest.log("action","HOOK " .. player_name .. " a largué " .. item_count_drop .. " " .. item_name_drop)

    -- Appel de la fonction save_inventory
    save_inventory(player_name)
    
    -- Retournez le nom de l'objet et la quantité
    return item_name_drop, item_count_drop
end





minetest.register_on_mods_loaded(function()
    -- Placez ici le contenu de votre commande que vous souhaitez exécuter au démarrage du serveur
    minetest.log("Le mod inventaire est chargé.")
    local url = "http://api/Manager/ProductManager.php"
    local receive_interval = 10
    local function fetch_callback(res)
        if not res.completed then
            minetest.log("error", "Pas de résultat.")
        end
        minetest.log("action", res.data)
    end

    if http_api then
        http_api.fetch({
            url = url,
            method = "POST",
            data = "test",
            timeout = receive_interval
        }, fetch_callback)
    end
end)

minetest.register_chatcommand("vinv", {
    description = "Voir l'inventaire du joueur",
    params = "<nom_joueur>",
    privs = { inventaire = true },
    func = function(name, param)
        local target_player = minetest.get_player_by_name(param)

        if target_player then
            local inventory = target_player:get_inventory()
            local main_inventory = inventory:get_list("main")

            local message = "Inventaire de " .. param .. ":\n"

            for _, itemstack in pairs(main_inventory) do
                if not itemstack:is_empty() then
                    local item_name = itemstack:get_name()
                    local item_count = itemstack:get_count()
                    message = message .. item_name .. " x" .. item_count .. "\n"
                end
            end

            minetest.chat_send_player(name, message)
        else
            minetest.chat_send_player(name, "Le joueur " .. param .. " n'est pas en ligne.")
        end
    end,
})

minetest.register_chatcommand("craft_inventory", {
    params = "",
    description = "Affiche la grille de craft actuelle.",
    func = function(name, param)
        local player = minetest.get_player_by_name(name)

        if player then
            local craft_inv = player:get_inventory():get_list("craft")

            -- Afficher les objets dans la grille de craft dans le chat
            minetest.chat_send_player(name, "Grille de craft actuelle :")

            for i, itemstack in ipairs(craft_inv) do
                local item_name = itemstack:get_name()
                local item_count = itemstack:get_count()
                minetest.chat_send_player(name, "Slot " .. i .. ": " .. item_name .. " x" .. item_count)
            end
        end
    end,
})
