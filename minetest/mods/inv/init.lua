local json = minetest.write_json
local http_api = minetest.request_http_api and minetest.request_http_api()
local item_name_drop = ""
local item_count_drop = 0
local item_name_place = ""
local player_die = false
local craft_inventory_craft = {}
local old_inventory = {}
local new_inventory = {}
local craft_success = false

minetest.register_privilege("inventaire", {
    description = "donne acces au inventaire"
})



minetest.register_craftitem("inventaire:coins", {
	description = "Coins",
	inventory_image = "coin.png",
	stack_max = 9999,
	groups = { coinvalue=1 },
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


minetest.register_on_joinplayer(function(ObjectRef, last_login)
        minetest.log("action", "Le joueur " .. ObjectRef:get_player_name() .. " a rejoint le serveur.")
    local playername = ObjectRef:get_player_name()

    -- Créez une table avec les données que vous souhaitez envoyer
    local data_to_send = {
        playername = playername,
    }

    -- Convertissez la table en JSON
    local json_str = minetest.write_json(data_to_send)
    local url = "http://api/Manager/PlayerOnLogManager.php"
    local receive_interval = 10

    local function fetch_callback(res)
        if not res.completed then
            minetest.log("error", "Pas de résultat.")
            return
        end

        -- Traitez le fichier JSON renvoyé
        local json_data = minetest.parse_json(res.data)
        if json_data then
            -- Faites quelque chose avec les données JSON
            minetest.log("action", "Données JSON reçues : " .. dump(json_data))

            -- Exemple de suppression et de remplacement de l'inventaire du joueur
            local player = minetest.get_player_by_name(playername)
            if player then
                -- Supprimez l'inventaire actuel du joueur
                player:get_inventory():clear()

                -- Vérifiez si la clé "inventory" existe dans les données JSON
                if json_data.inventory then
                    -- Ajoutez les nouveaux éléments à l'inventaire du joueur
                    for _, item in ipairs(json_data.inventory) do
                        player:get_inventory():add_item("main", ItemStack(item))
                    end
                else
                    minetest.log("warning", "La clé 'inventory' est manquante dans les données JSON.")
                end
            else
                minetest.log("error", "Joueur introuvable : " .. playername)
            end

        else
            minetest.log("error", "Erreur lors de l'analyse JSON.")
        end
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
    local function update_item_quantity(item_list, item_name, item_count)
    -- Vérifiez si l'objet est déjà dans la liste
    for _, existing_item in ipairs(item_list) do
        if existing_item.name == item_name then
            minetest.log("action", "L'objet " .. item_name .. " est déjà dans la liste. Mise à jour de la quantité...")
            -- Mettez à jour la quantité en ajoutant la nouvelle quantité
            existing_item.quantity = existing_item.quantity + item_count
            return true
        end
    end

    -- Supprimer les suffixes spécifiés du nom de l'item
    item_name = item_name:gsub("_[1-8a-d]$", "")

    -- Créez un objet avec des attributs pour le nom et la quantité
    local item = {
        name = item_name,
        quantity = item_count
    }

    -- Ajoutez l'objet à la liste
    minetest.log("action", "L'objet " .. item_name .. " n'est pas dans la liste. Ajout...")
    table.insert(item_list, item)

    return false
end

local function add_inventory_items(inv, item_list)
    for _, itemstack in pairs(inv) do
        if not itemstack:is_empty() then
            local item_name = itemstack:get_name()
            local item_count = itemstack:get_count()

            -- Mettez à jour la quantité dans la liste d'objets
            update_item_quantity(item_list, item_name, item_count)
        end
    end
end

-- Fonction pour vérifier si un élément est dans une liste
local function is_item_in_list(item_list, item_name)
    for _, existing_item in ipairs(item_list) do
        if existing_item.name == item_name then
            return true
        end
    end
    return false
end


local function add_inventory_drop_items(item_list)
    if item_name_drop ~= "" then
        minetest.log("action", player_name .. " a largué " .. item_count_drop .. " " .. item_name_drop)

        -- Mettez à jour la quantité en soustrayant le nombre d'objets dropés
        update_item_quantity(item_list, item_name_drop, -item_count_drop)

        item_name_drop = ""
        item_count_drop = 0
    end

    if item_name_place ~= "" then
        if not is_item_in_list(item_list, item_name_place) then
            -- Si item_name_place n'est pas dans item_list, ajoutez-le avec une quantité de 0
            table.insert(item_list, {name = item_name_place, quantity = 0})
        end
    end


    if player_die then
        for _, existing_item in ipairs(item_list) do
                -- Supprimez l'objet de la liste
                minetest.log("action", "Suppression de l'objet " .. existing_item.name .. " de la liste.")
                existing_item.quantity = 0
            end
        end
        player_die = false
        
end

function add_inventory_items_craft(inv, item_list)
    minetest.log("action", "add_craft_inventory_craft : ------------------------------------------------------------------")
    for _, item in pairs(inv) do
        local item_name = item.name
        local item_count = item.quantity

        minetest.log("action", "add_craft_inventory_craft : " .. item_name .. " x" .. item_count)

        -- Mettez à jour la quantité dans la liste d'objets
        update_item_quantity(item_list, item_name, item_count)
    end
end


-- Fonction pour comparer deux listes old_craft_inventory_craft et new_craft_inventory_craft
-- et ajouter les items disparus avec une quantité à 0 dans une nouvelle liste
local function find_disappeared_items(old_list, new_list)
    local disappeared_items = {}

    -- Créer une copie de la nouvelle liste pour marquer les éléments présents
    local new_list_copy = {}
    for _, item in pairs(new_list) do
        new_list_copy[item.name] = item.quantity
    end

    -- Parcourir l'ancienne liste pour identifier les items disparus
    for _, old_item in pairs(old_list) do
        local old_name = old_item.name
        local old_quantity = old_item.quantity
        local new_quantity = new_list_copy[old_name]

        if new_quantity == nil or new_quantity == 0 then
            -- Si l'élément est présent dans l'ancienne liste mais pas dans la nouvelle
            -- ou si la quantité est devenue nulle dans la nouvelle liste
            -- Ajouter l'élément à la liste des disparus avec une quantité à 0
            table.insert(disappeared_items, {
                name = old_name,
                quantity = 0
            })
        else
            -- Retirer l'élément de la copie de la nouvelle liste pour marquer qu'il a été traité
            new_list_copy[old_name] = nil
        end
    end

    return disappeared_items
end


-- Créez une table pour stocker les objets
local item_list = {}
local old_craft_inventory_craft = {}
local new_craft_inventory_craft = {}

add_inventory_items(old_inventory, old_craft_inventory_craft)
add_inventory_items(new_inventory, new_craft_inventory_craft)

craft_inventory_craft = find_disappeared_items(old_craft_inventory_craft, new_craft_inventory_craft)

-- afficher craft_inventory_craft
minetest.log("action", "craft_inventory_craft :")
for _, item in pairs(craft_inventory_craft) do
    minetest.log("action", item.name .. " x" .. item.quantity)
end

-- afficher old_craft_inventory_craft
minetest.log("action", "old_craft_inventory_craft :")
for _, item in pairs(old_craft_inventory_craft) do
    minetest.log("action", item.name .. " x" .. item.quantity)
end

-- afficher new_craft_inventory_craft
minetest.log("action", "new_craft_inventory_craft :")
for _, item in pairs(new_craft_inventory_craft) do
    minetest.log("action", item.name .. " x" .. item.quantity)
end

-- Ajouter les objets de l'inventaire de craft si aucun craft n'a été fait
add_inventory_items_craft(craft_inventory_craft, item_list)
add_inventory_items(craft_inventory, item_list)
-- Ajouter les objets de l'inventaire principal
add_inventory_items(main_inventory, item_list)
add_inventory_drop_items(item_list)


minetest.log("action", "Inventaire de " .. player_name .. " :")
for _, item in pairs(item_list) do
    minetest.log("action", item.name .. " x" .. item.quantity)
end 

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

minetest.register_chatcommand("crea", {
    params = "",
    description = "Active le mode créatif",
    privs = {interact = true}, -- Assurez-vous que le joueur a le privilège d'interagir pour exécuter la commande
    func = function(name, param)
        local player = minetest.get_player_by_name(name)

        if player then
            -- Ajoutez le privilège créatif au joueur
            local privs = minetest.get_player_privs(name)
            privs.creative = true
            minetest.set_player_privs(name, privs)

            -- Indiquez au joueur que le mode créatif est activé
            minetest.chat_send_player(name, "Mode créatif activé.")
        else
            minetest.chat_send_player(name, "Joueur introuvable.")
        end
    end,
})

minetest.register_chatcommand("survie", {
    params = "",
    description = "Active le mode survie",
    privs = {interact = true}, -- Assurez-vous que le joueur a le privilège d'interagir pour exécuter la commande
    func = function(name, param)
        local player = minetest.get_player_by_name(name)

        if player then
            -- Supprimez le privilège créatif du joueur
            local privs = minetest.get_player_privs(name)
            privs.creative = nil
            minetest.set_player_privs(name, privs)

            -- Indiquez au joueur que le mode créatif est désactivé
            minetest.chat_send_player(name, "Mode créatif désactivé.")
        else
            minetest.chat_send_player(name, "Joueur introuvable.")
        end
    end,
})


-- Hook pour gérer lorsqu'un joueur meurt
minetest.register_on_dieplayer(function(player)
    local player_name = player:get_player_name()

    -- Définissez la variable player_die sur true
    player_die = true

    save_inventory(player_name)
end)

-- Hook pour gérer lorsqu'un joueur casse un block
minetest.register_on_dignode(function(pos, oldnode, digger)
	local player_name = digger:get_player_name()
	save_inventory(player_name)
end)


-- Hook pour gérer lorsqu'un joueur mange un item
minetest.register_on_item_eat(function(hp_change, replace_with_item, itemstack, user, pointed_thing)
    local player_name = user:get_player_name()

    item_name_place = itemstack:get_name()

    save_inventory(player_name)
end)




minetest.register_on_craft(function(itemstack, player, old_craft_grid, craft_inv)
    craft_success = true
    local player_name = player:get_player_name()
    local inventory = player:get_inventory()

    old_inventory = new_inventory

    craft_success = false
end)


-- Hook pour gérer lorsqu'un joueur place un bloc
minetest.register_on_placenode(function(pos, newnode, placer, oldnode, itemstack, pointed_thing)
    local player_name = placer:get_player_name()

    -- Obtenez le nom de l'objet placé
    item_name_place = itemstack:get_name()

    -- Appel à save_inventory après le placement effectif du bloc
    minetest.after(0, function()
        save_inventory(player_name)
    end)
end)

minetest.register_on_player_inventory_action(function(player, action, inventory, inventory_info)
    minetest.log("action", "HOOK " .. player:get_player_name() .. " a effectué une action d'inventaire: " .. action)
    if action == "move" then
        local player_name = player:get_player_name()
        new_inventory = inventory:get_list("craft")
        save_inventory(player_name)
    elseif action ~= "take" then
        local player_name = player:get_player_name()
        save_inventory(player_name)
    end
end)

local old_item_drop = minetest.item_drop

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

    -- Appelez la fonction originale item_drop
    old_item_drop(itemstack, dropper, pos)
    
    -- Retournez le nom de l'objet et la quantité
    return item_name_drop, item_count_drop
end

minetest.register_on_item_pickup(function(itemstack, picker, pointed_thing, time_from_last_punch)
    minetest.log("action", "HOOK " .. picker:get_player_name() .. " a ramassé " .. itemstack:get_name() .. " x" .. itemstack:get_count())
    local player_name = picker:get_player_name()
    minetest.after(0, function()
        save_inventory(player_name)
    end)
end)


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
