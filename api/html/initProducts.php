<?php
$data = file_get_contents('allProducts.json');
$allProducts = json_decode($data, true); // Le deuxième argument permet de retourner un tableau associatif
foreach ($allProducts as $key => $value) {
    $allProducts[$key] = removeLastUnderscoreAndChar($value);
}
$allProducts = array_unique($allProducts);


function createObject($class, $data)
{
    $url = "http://dolibarr/api/index.php/" . $class; //mettre localhost (si sur machine) ou dolibarr (si avec minetest)
    $data_json = json_encode($data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json',
        'DOLAPIKEY: 0jTMui5CO7nf0ma59XEf0sdF91lTQ4ZA' // clef à changer
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
}

function createNewProduct($name, $label)
{
    $data = array(
        'ref' => $name,
        'label' => $label,
        'price' => createPrice($name),
        'status' => 1,
        'status_buy' => 1
    );
    createObject("products", $data);
}

function initAllProducts($productList)
{
    $dataTranslate = file_get_contents('productsEN-FR.json');
    $allProductsTranslate = json_decode($dataTranslate, true); // Le deuxième argument permet de retourner un tableau associatif
    foreach ($productList as $entry) {
        createNewProduct($entry, frenchName($entry, $allProductsTranslate));
        echo "product: " . $entry . " has been created\n";
    }
}

function separateWords($productList)
{
    $words = array(); // Initialisez le tableau en dehors de la boucle

    foreach ($productList as $entry) {
        $words_temp = explode(":", $entry); // Stockez le résultat dans un tableau temporaire

        $words_after_colon = $words_temp[1];
        $words_after_colon_without_ = str_replace('_', ' ', $words_after_colon);

        // Vérifiez si le mot existe déjà dans le tableau
        if (!in_array($words_after_colon_without_, $words)) {
            $words[] = $words_after_colon_without_; // Ajoutez le mot au tableau
        }
    }

    return $words;
}

function separateWord($product)
{
    $words_temp = explode(":", $product); // Stockez le résultat dans un tableau temporaire

    $words_after_colon = $words_temp[1];
    $words_after_colon_without_ = str_replace('_', ' ', $words_after_colon);

    return $words_after_colon_without_;
}


function frenchName($searchString, $allProductsTranslate)
{
    $separateWord = separateWord($searchString);
    foreach ($allProductsTranslate as $item) {
    if (isset($item['english']) && $item['english'] === $separateWord) {
            $englishName = $item['english'];
            break; // La chaîne a été trouvée, pas besoin de continuer la recherche
        }
    }

    if (!empty($englishName)) {
        // echo "Le nom en anglais de '$searchString' est : $englishName\n";
        return $item['french'];
    } else {
        echo "La chaîne '$searchString' n'a pas été trouvée dans le tableau.\n";
    }
}

function removeLastUnderscoreAndChar($input)
{
    return preg_replace('/_[a-zA-Z0-9]$/', '', $input);
}

function createPrice($name)
{ // fonction qui retourne le prix d'un item en fonction de son nom

    /*
     *  lors de craft certain prix on un +1 supplémentaire du prix total des objets craft surtout les outils comme bénéfice au craft
     */


<<<<<<< HEAD
<<<<<<< HEAD
    $name = separateWord($name);

// items fait de minerai
=======
    $prices = [
        "axe copper" => 4,
        "axe stone" => 8,
        "axe bronze" => 20,
        "axe steel" => 32,
        "axe mese" => 302,
        "axe diamond" => 62,
        "sword copper" => 3,
        "sword stone" => 5.5,
        "sword bronze" => 13.5,
        "sword steel" => 21.5,
        "sword mese" => 201.5,
        "sword diamond" => 41.5,
        "hoe copper" => 4,
        "hoe stone" => 6,
        "hoe steel" => 22,
        "shovel copper" => 3,
        "shovel stone" => 5,
        "shovel bronze" => 8,
        "shovel steel" => 13,
        "shovel mese" => 102,
        "shovel diamond" => 22,
        "trapdoor steel bar" => 13,
        "trapdoor steel" => 41,
        "trapdoor" => 6,
        "door steel bar" => 19,
        "door steel" => 61,
        "door obsidian glass" => 7,
        "door glass" => 7,
        "wood" => 0.25,
        "fence rail" => 0.25,
        "fence" => 1.25,
        "gate" => 7,
        "walls" => 1,
        "bar flat" => 3,
        "pane flat" => 0.4,
        "stick" => 1,
        "stone" => 2,
        "coal lump" => 3,
        "copper lump" => 4,
        "clay lump" => 2,
        "iron lump" => 5,
        "tin lump" => 6,
        "gold lumb" => 8,
        "mese crystal fragment" => 11,
        "mese crystal" => 100,
        "mese" => 900,
        "clay brick" => 2,
        "flint and steel" => 12,
        "diamondblock" => 180,
        "golddblock" => 108,
        "coalblock" => 3,
        "bronzeblock" => 54,
        "copperblock" => 45,
        "copper" => 5,
        "iron" => 10,
        "tin" => 7,
        "bronze" => 6,
        "gold" => 12,
        "diamond" => 20,
        "paper" => 3,
        "book" => 3,
        "bookshelf" => 16,
        "bucket" => 31,
        "screwdriver" => 12,
        "torch" => 1,
        "key" => 8,
        "binoculars" => 21,
        "mapping kit" => 27,
        "bug net" => 6,
        "boat" => 6,
        "cart" => 51,
        "gunpowder" => 1,
        "tnt stick" => 4,
        "tnt" => 30,
        "obsidian shard" => 1,
        "obsidian glass" => 1,
        "obsidian" => 9,
        "chest" => 8,
        "chest locked" => 18,
    ];
>>>>>>> 11f70d7 (refactor price)

=======
    $prices = [
        "axe copper" => 4,
        "axe stone" => 8,
        "axe bronze" => 20,
        "axe steel" => 32,
        "axe mese" => 302,
        "axe diamond" => 62,
        "sword copper" => 3,
        "sword stone" => 5.5,
        "sword bronze" => 13.5,
        "sword steel" => 21.5,
        "sword mese" => 201.5,
        "sword diamond" => 41.5,
        "hoe copper" => 4,
        "hoe stone" => 6,
        "hoe steel" => 22,
        "shovel copper" => 3,
        "shovel stone" => 5,
        "shovel bronze" => 8,
        "shovel steel" => 13,
        "shovel mese" => 102,
        "shovel diamond" => 22,
        "trapdoor steel bar" => 13,
        "trapdoor steel" => 41,
        "trapdoor" => 6,
        "door steel bar" => 19,
        "door steel" => 61,
        "door obsidian glass" => 7,
        "door glass" => 7,
        "wood" => 0.25,
        "fence rail" => 0.25,
        "fence" => 1.25,
        "gate" => 7,
        "walls" => 1,
        "bar flat" => 3,
        "pane flat" => 0.4,
        "stick" => 1,
        "stone" => 2,
        "coal lump" => 3,
        "copper lump" => 4,
        "clay lump" => 2,
        "iron lump" => 5,
        "tin lump" => 6,
        "gold lumb" => 8,
        "mese crystal fragment" => 11,
        "mese crystal" => 100,
        "mese" => 900,
        "clay brick" => 2,
        "flint and steel" => 12,
        "diamondblock" => 180,
        "golddblock" => 108,
        "coalblock" => 3,
        "bronzeblock" => 54,
        "copperblock" => 45,
        "copper" => 5,
        "iron" => 10,
        "tin" => 7,
        "bronze" => 6,
        "gold" => 12,
        "diamond" => 20,
        "paper" => 3,
        "book" => 3,
        "bookshelf" => 16,
        "bucket" => 31,
        "screwdriver" => 12,
        "torch" => 1,
        "key" => 8,
        "binoculars" => 21,
        "mapping kit" => 27,
        "bug net" => 6,
        "boat" => 6,
        "cart" => 51,
        "gunpowder" => 1,
        "tnt stick" => 4,
        "tnt" => 30,
        "obsidian shard" => 1,
        "obsidian glass" => 1,
        "obsidian" => 9,
        "chest" => 8,
        "chest locked" => 18,
    ];

>>>>>>> 33738e0 (refactor price)
    foreach ($prices as $key => $value) {
        if (strpos($name, $key) !== false) {
            return $value;
        }
    }
    // Default price
    return 1;
}

<<<<<<< HEAD
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $data = json_decode(file_get_contents('php://input'), true);
//     $allProducts = $data['allProducts'];
//     initAllProducts($allProducts);
// }

// initAllProducts($allProducts);

function read1Products()
{
    $ch = curl_init();
    $url = "http://localhost/api/index.php/products?sortfield=t.ref&sortorder=ASC&limit=1&ids_only=true";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    $headers = array(
        "Accept: application/json",
        "DOLAPIKEY: 0jTMui5CO7nf0ma59XEf0sdF91lTQ4ZA" // clef à changer
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $dataList = curl_exec($ch);
    curl_close($ch);
    return json_decode($dataList);
}

function isDolibarrProductListEmpty()
{
    $data = read1Products();
    if (empty($data)) {
        return true;
    }
    return false;
}

if (isDolibarrProductListEmpty()) {
    echo "Dolibarr product list is empty\n";
    initAllProducts($allProducts);
}
else {
    echo "Dolibarr product list is not empty\n";
}
=======
initAllProducts($allProducts);
>>>>>>> 11f70d7 (refactor price)
