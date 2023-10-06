<?php
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

function createObject($class, $data)
{
    $url = "http://dolibarr/api/index.php/".$class;
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
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
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

function initAllProducts($productList)
{
    foreach ($productList as $entry) {
        createNewProduct($entry);
        echo "product: ".$entry." has been created\n";
    }
}

initAllProducts($allProducts);


