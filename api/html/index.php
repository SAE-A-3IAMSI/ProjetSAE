<?php

/*liste des fonctions principales:
- créer Entrepôt
- créer Produit
- supprimer Produit
- Modification (ajout/retrait) Entrepôt
- supprimer tous les produits
- initialiser les produits (à partir d'une array)
*/

/* fichier index.php appelé à chaque modification de l'inventaire du joueur */

/* à faire:
- faire un traitement du json
- faire un appel api à partir du json renvoyé par minetest (traitement des données json + fonctions)
- faire un appel api: initialisation des produits à la création du serveur
- faire un appel api: pour la création des entrepôts lorsque nouveau joueur créé
*/

$allProducts = array(
    "default:stone",
    "default:dirt",
    "default:wood",
    "default:leaves",
    "default:sand",
    "default:water_source",
    "default:lava_source",
    "default:coalblock",
    "default:ironblock",
    "default:cobble",
    "default:brick",
    "default:glass",
    "default:torch",
    "default:chest",
    "default:furnace",
    "default:crafting_table",
    "default:ladder",
    "default:bookshelf",
    "default:sign_wall",
    "default:apple",
    "default:stick",
    "default:paper",
    "default:book",
    "default:coal_lump",
    "default:iron_lump",
    "default:copper_lump",
    "default:gold_lump",
    "default:mese_crystal",
    "default:diamond",
    "default:obsidian",
    "default:tree",
    "default:sapling",
    "default:junglegrass",
    "default:fern",
    "default:rose",
    "default:dandelion",
    "default:bush_leaves",
    "default:acacia_leaves",
    "default:pine_needles",
    "default:jungleleaves",
    "default:water_flowing",
    "default:lava_flowing",
    "default:snowblock",
    "default:snow",
    "default:cloud",
    "default:wooden_fence",
    "default:rail",
    "default:ladder_steel",
    "default:sign_wall_steel",
);

/*
function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}*/

function readAllUsers()
{
    $ch = curl_init();
    $url = "http://localhost/api/index.php/users";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    $headers = array(
        "Accept: application/json",
        "DOLAPIKEY: bVs76AE8sUyw2Hh5D3QAS3Wki70gNy5q" // clef à changer
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $dataList = curl_exec($ch);
    curl_close($ch);
    echo $dataList;
}

function readAllProducts()
{
    $ch = curl_init();
    $url = "http://localhost/api/index.php/products?sortfield=t.ref&sortorder=ASC&limit=100&ids_only=true";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    $headers = array(
        "Accept: application/json",
        "DOLAPIKEY: bVs76AE8sUyw2Hh5D3QAS3Wki70gNy5q" // clef à changer
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $dataList = curl_exec($ch);
    curl_close($ch);
    return json_decode($dataList);
}

function getObjectByName($class, $name)
{
    $url = "http://localhost/api/index.php/".$class."?sortfield=t.rowid&sortorder=ASC&limit=100&sqlfilters=(t.ref%3Alike%3A'".$name."')";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'DOLAPIKEY: bVs76AE8sUyw2Hh5D3QAS3Wki70gNy5q' // clef à changer
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response ;
}

function getWarehouseIdByName($name)
{
    $data = getObjectByName("warehouses", $name);
    $data = json_decode($data, true);
    foreach ($data as $entry) {
        if (isset($entry["id"])) {
            return $entry["id"];
        }
    }
}

function getProductIdByName($name)
{
    $productName = $name;
    if (str_contains($name, ':'))
    {
        $productName = str_replace(':', '_', $name);
    }
    $data = getObjectByName("products", $productName);
    $data = json_decode($data, true);
    foreach ($data as $entry) {
        if (isset($entry["id"])) {
            return $entry["id"];
        }
    }
}

function getProductQtyByName($name)
{
    $productName = $name;
    if (str_contains($name, ':'))
    {
        $productName = str_replace(':', '_', $name);
    }
    $data = getObjectByName("products", $productName);
    $data = json_decode($data, true);
    foreach ($data as $entry) {
        if (isset($entry["qty"])) {
            return $entry["qty"];
        }
    }
}

function addItemToStock($productName, $warehouseName, $qty)
{
    $url = "http://localhost/api/index.php/stockmovements";
    $data = array(
        'product_id' => getProductIdByName($productName),
        'warehouse_id' => getWarehouseIdByName($warehouseName),
        'qty' => $qty,
    );
    $data_json = json_encode($data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json',
        'DOLAPIKEY: bVs76AE8sUyw2Hh5D3QAS3Wki70gNy5q' // clef à changer
    ));
    //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
}

function createObject($class, $data)
{
    $url = "http://localhost/api/index.php/".$class;
    $data_json = json_encode($data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json',
        'DOLAPIKEY: bVs76AE8sUyw2Hh5D3QAS3Wki70gNy5q' // clef à changer
    ));
    //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
}

function createNewWarehouse($name)
{
    $data = array(
        'label' => $name,
        'statut' => 1
    );
    createObject("warehouses", $data);
}

function createNewProduct($name)
{
    $data = array(
        'ref' => $name,
        'label' => $name,
        'status' => 1,
        'status_buy' => 1
    );
    createObject("products", $data);
}

function deleteProduct($id)
{
    $ch = curl_init();
    $url = "http://localhost/api/index.php/products/".$id;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    $headers = array(
        "Accept: application/json",
        "DOLAPIKEY: bVs76AE8sUyw2Hh5D3QAS3Wki70gNy5q" // clef à changer
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
    echo "product: ".$id." has been deleted\n";
}

function deleteAllProducts()
{
    try{
        $allProductsId = readAllProducts();
        foreach ($allProductsId as $entry) {
            deleteProduct($entry);
            echo "product: ".$entry." has been deleted\n";
        }
    }
    catch (Exception $e) {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
    }
}

function initAllProducts($productList)
{
    foreach ($productList as $entry) {
        createNewProduct($entry);
        echo "product: ".$entry." has been created\n";
    }
}

function updateDataBase($jsonData)
{

    $userId = $jsonData['player_name'];

    foreach($jsonData['inventory'] as $entry){
        $stockQty = getItemStock($entry['name'], $userId);
        $updatedStock = $entry['quantity'] - $stockQty;
        addItemToStock($entry['name'], $userId, $updatedStock);
    }   
}

function getItemStock($productName, $warehouseName)
{
    $productId = getProductIdByName($productName);
    $warehouseId = getWarehouseIdByName($warehouseName);

    $url = "http://localhost/api/index.php/products/".$productId."/stock?selected_warehouse_id=".$warehouseId;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'DOLAPIKEY: bVs76AE8sUyw2Hh5D3QAS3Wki70gNy5q' // clef à changer
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $responseData = json_decode($response, true);
    if ($responseData !== null) {
    $real = $responseData['stock_warehouses'][$warehouseId]['real'];
        return $real;
    } else {
        echo "La réponse JSON est invalide.";
    }
}

//addItemToStock("default:dirt", "test", 50);
//echo getObjectByName("warehouses", "test");
initAllProducts($allProducts);
/*
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');
    $decoded_data = json_decode($json_data);
    if ($decoded_data === null) {
        echo "Le fichier JSON n'est pas valide pour l'inscription.";
    } else {
        // Créez un fichier texte sur le serveur avec les données JSON
        updateDataBase($decoded_data);
        $file_name = 'donnees2.json'; // Nom du fichier de destination
        $json_str = json_encode($decoded_data, JSON_PRETTY_PRINT);
        file_put_contents($file_name, $json_str);
    }
} else {
    echo "Aucune donnée JSON reçue pour l'inscription.";
}
*/
