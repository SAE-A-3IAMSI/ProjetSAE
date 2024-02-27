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

-- Donne les privilege du mod inventaire a tous les joueurs pour tapé les commandes
minetest.register_privilege("inventaire", {
    description = "donne acces aux commandes du mod inventaire"
})

-- Fonction pour supprimer l'inventaire du joueur
local function clear_inventory(player)
    local inv = player:get_inventory()
    inv:set_list("main", {})
end

-- Fonction pour supprimer l'inventaire de craft du joueur
local function clear_craft_inventory(player)
    local inv = player:get_inventory()
    inv:set_list("craft", {})
end

-- Fonction pour mapper les noms d'objets entre Dolibarr et Minetest
local function map_item_name(dolibarr_name)
    local first, rest = dolibarr_name:match("([^_]+)_(.*)")
    if first and rest then
        return first .. ":" .. rest
    else
        return dolibarr_name
    end
end

-- Fonction qui verifié si un item est stackable
local function is_not_stackable(stack)
    return stack:get_stack_max() == 1
end

-- Fonction qui give les items au joueur
local function give_items(player, items)
    local inv = player:get_inventory()

    for item_id, item_data in pairs(items) do
        local mapped_name = map_item_name(item_data.name)
        local total_quantity = tonumber(item_data.reel)
        local stack_size = 99

        if is_not_stackable(ItemStack(mapped_name)) then
            local target_inventory = inv:room_for_item("main", ItemStack(mapped_name)) and "main" or
                                     inv:room_for_item("craft", ItemStack(mapped_name)) and "craft"

            if target_inventory then
                for _ = 1, total_quantity do
                    if not inv:room_for_item(target_inventory, ItemStack(mapped_name)) then
                        minetest.log("warning", "L'inventaire du joueur est plein, certains objets n'ont pas pu être ajoutés.")
                        minetest.kick_player(player:get_player_name(), "L'inventaire est plein.")
                        return
                    end
                    inv:add_item(target_inventory, ItemStack(mapped_name))
                end
            end
        else
            while total_quantity > 0 do
                local stack = ItemStack(mapped_name .. " " .. math.min(total_quantity, stack_size))

                local target_inventory = inv:room_for_item("main", stack) and "main" or
                                         inv:room_for_item("craft", stack) and "craft"

                if target_inventory then
                    inv:add_item(target_inventory, stack)
                else
                    minetest.log("warning", "L'inventaire du joueur est plein, certains objets n'ont pas pu être ajoutés.")
                    minetest.kick_player(player:get_player_name(), " \nL'inventaire est plein, certains objets n'ont pas pu être ajoutés.\nVeuillez supprimer des objets avant de pouvoir rejoindre le serveur")
                    return
                end

                total_quantity = total_quantity - stack_size
            end
        end
    end
end

-- Fonction qui enregistre l'inventaire du joueur
local function save_inventory(player_name)
    local player = minetest.get_player_by_name(player_name)
    if not player then
        minetest.log("error", "Joueur introuvable.")
        return false, "Joueur introuvable."
    end

    local inventory = player:get_inventory()
    local main_inventory = inventory:get_list("main")
    local craft_inventory = inventory:get_list("craft")

    local item_list = {}

    -- Fonction pour mettre à jour la quantité d'un item dans la liste
    local function update_item_quantity(item_list, item_name, item_count)
        for _, existing_item in ipairs(item_list) do
            if existing_item.name == item_name then
                existing_item.quantity = math.max(existing_item.quantity + item_count, 0)
                return true
            end
        end

        item_name = item_name:gsub("_[1-8a-d]$", "")

        local item = {
            name = item_name,
            quantity = math.max(item_count, 0)
        }

        table.insert(item_list, item)

        return false
    end

    -- Fonction pour ajouter les items de l'inventaire à la liste
    local function add_inventory_items(inv, item_list)
        for _, itemstack in pairs(inv) do
            if not itemstack:is_empty() then
                local item_name = itemstack:get_name()
                local item_count = itemstack:get_count()
                update_item_quantity(item_list, item_name, item_count)
            end
        end
    end

    -- Fonction pour vérifier si un item est dans la liste
    local function is_item_in_list(item_list, item_name)
        for _, existing_item in ipairs(item_list) do
            if existing_item.name == item_name then
                return true
            end
        end
        return false
    end

    -- Fonction pour ajouter les items de l'inventaire de drop à la liste
    local function add_inventory_drop_items(item_list)
        if item_name_drop ~= "" then
            update_item_quantity(item_list, item_name_drop, -item_count_drop)
            item_name_drop = ""
            item_count_drop = 0
        end

        if item_name_place ~= "" then
            if not is_item_in_list(item_list, item_name_place) then
                table.insert(item_list, {name = item_name_place, quantity = 0})
            end
        end

        if player_die then
            for _, existing_item in ipairs(item_list) do
                existing_item.quantity = 0
            end
            player_die = false
        end
    end

    -- Fonction pour ajouter les items de l'inventaire de craft à la liste
    local function add_inventory_items_craft(inv, item_list)
        for _, item in pairs(inv) do
            local item_name = item.name
            local item_count = item.quantity

            update_item_quantity(item_list, item_name, item_count)
        end
    end

    -- Fonction pour trouver les items disparus
    local function find_disappeared_items(old_list, new_list)
        local disappeared_items = {}

        local new_list_copy = {}
        for _, item in pairs(new_list) do
            new_list_copy[item.name] = item.quantity
        end

        for _, old_item in pairs(old_list) do
            local old_name = old_item.name
            local old_quantity = old_item.quantity
            local new_quantity = new_list_copy[old_name]

            if new_quantity == nil or new_quantity == 0 then
                table.insert(disappeared_items, {
                    name = old_name,
                    quantity = 0
                })
            else
                new_list_copy[old_name] = nil
            end
        end

        return disappeared_items
    end

    local item_list = {}
    local old_craft_inventory_craft = {}
    local new_craft_inventory_craft = {}

    add_inventory_items(old_inventory, old_craft_inventory_craft)
    add_inventory_items(new_inventory, new_craft_inventory_craft)

    craft_inventory_craft = find_disappeared_items(old_craft_inventory_craft, new_craft_inventory_craft)

    add_inventory_items_craft(craft_inventory_craft, item_list)
    add_inventory_items(craft_inventory, item_list)
    add_inventory_items(main_inventory, item_list)
    add_inventory_drop_items(item_list)

    local player_inventory = {}

    if #item_list > 0 then
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

    local json_str = minetest.write_json(player_inventory)
    local url = "http://api/Manager/InventoryManager.php"
    local receive_interval = 10

    -- Fonction pour envoyer les données au serveur
    local function fetch_callback(res)
        if not res.completed then
            minetest.log("error", "Pas de résultat.")
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
    return true, "OUI."
end


-- Hook qui le lance quand un nouveau joueur rejoins le serveur
minetest.register_on_newplayer(function(player)
    local playername = player:get_player_name()
    local privs = minetest.get_player_privs(playername)
    privs["inventaire"] = true
    minetest.set_player_privs(playername, privs)

    local playernames = {
        player_name = playername,
    }

    local json_str = minetest.write_json(playernames)
    local url = "http://api/Manager/PlayerManager.php"
    local receive_interval = 10
    -- Fonction pour envoyer les données au serveur
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






-- Hook pour gérer lorsqu'un joueur se connecte
minetest.register_on_joinplayer(function(ObjectRef, last_login)
    local playername = ObjectRef:get_player_name()

    local data_to_send = {
        playername = playername,
    }

    local json_str = minetest.write_json(data_to_send)

    local url = "http://api/Manager/PlayerOnLogManager.php"
    local receive_interval = 1000

    -- Fonction pour envoyer les données au serveur
    local function fetch_callback(res)
        if not res.completed then
            minetest.log("error", "Pas de résultat.")
            return
        end

        local decoded_response = minetest.parse_json(res.data)
        if decoded_response then
            clear_inventory(ObjectRef)

            clear_craft_inventory(ObjectRef)

            give_items(ObjectRef, decoded_response)
        else
            minetest.log("error", "Réponse JSON invalide.")
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

-- Hook pour gérer lorsqu'un joueur meurt
minetest.register_on_dieplayer(function(player)
    local player_name = player:get_player_name()

   
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



-- Hook qui le lance quand un joueur craft un item
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

   
    item_name_place = itemstack:get_name()

 
    minetest.after(0, function()
        save_inventory(player_name)
    end)
end)

-- Hook qui le lance quand une action d'inventaire est affectué
minetest.register_on_player_inventory_action(function(player, action, inventory, inventory_info)
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
    
   
    item_name_drop = itemstack:get_name()
    
   
    item_count_drop = itemstack:get_count()
    
  
    save_inventory(player_name)


    old_item_drop(itemstack, dropper, pos)
    
 
    return item_name_drop, item_count_drop
end

-- Hook qui le lance quand un item est recupérer par un joueur
minetest.register_on_item_pickup(function(itemstack, picker, pointed_thing, time_from_last_punch)
    local player_name = picker:get_player_name()
    minetest.after(0, function()
        save_inventory(player_name)
    end)
end)





-- Commande pour enregistré l'inventaire de tous les joueurs
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


-- Commande pour enregistré l'inventaire du joueur
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

-- Commande pour activer le mode créatif
minetest.register_chatcommand("crea", {
    params = "",
    description = "Active le mode créatif",
    privs = {interact = true},
    func = function(name, param)
        local player = minetest.get_player_by_name(name)

        if player then
            local privs = minetest.get_player_privs(name)
            privs.creative = true
            minetest.set_player_privs(name, privs)

            minetest.chat_send_player(name, "Mode créatif activé.")
        else
            minetest.chat_send_player(name, "Joueur introuvable.")
        end
    end,
})

-- Commande pour activer le mode survie
minetest.register_chatcommand("survie", {
    params = "",
    description = "Active le mode survie",
    privs = {interact = true},
    func = function(name, param)
        local player = minetest.get_player_by_name(name)

        if player then
            local privs = minetest.get_player_privs(name)
            privs.creative = nil
            minetest.set_player_privs(name, privs)

            minetest.chat_send_player(name, "Mode créatif désactivé.")
        else
            minetest.chat_send_player(name, "Joueur introuvable.")
        end
    end,
})

-- Commande pour afficher l'invnetaire du joueur ciblé
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

-- Commande pour afficher le grille de craft
minetest.register_chatcommand("craft_inventory", {
    params = "",
    description = "Affiche la grille de craft actuelle.",
    func = function(name, param)
        local player = minetest.get_player_by_name(name)

        if player then
            local craft_inv = player:get_inventory():get_list("craft")

            
            minetest.chat_send_player(name, "Grille de craft actuelle :")

            for i, itemstack in ipairs(craft_inv) do
                local item_name = itemstack:get_name()
                local item_count = itemstack:get_count()
                minetest.chat_send_player(name, "Slot " .. i .. ": " .. item_name .. " x" .. item_count)
            end
        end
    end,
})
