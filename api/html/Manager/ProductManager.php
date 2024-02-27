<?php

use Api\DolibarrAPI;
require_once "../DolibarrAPI.php";
require_once "../Psr4AutoloaderClass.php";


$data = file_get_contents('allProducts.json');
$allProducts = json_decode($data, true); // Le deuxième argument permet de retourner un tableau associatif
foreach ($allProducts as $key => $value) {
    $allProducts[$key] = removeLastUnderscoreAndChar($value);
}
$allProducts = array_unique($allProducts);





function initAllProducts($productList)
{
    echo "initAllProducts\n";
    $dataTranslate = file_get_contents('productsEN-FR.json');
    $allProductsTranslate = json_decode($dataTranslate, true); // Le deuxième argument permet de retourner un tableau associatif
    foreach ($productList as $entry) {
        $dol = new DolibarrAPI();
        $dol->createNewProduct($entry, frenchNameAndPrice($entry, $allProductsTranslate));
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

    if ($words_temp[0] === 'dye' || $words_temp[0] === 'wool') {
        $words_after_colon_without_ = $words_temp[0] . " " . $words_after_colon_without_;
    }
    return $words_after_colon_without_;
}


function frenchNameAndPrice($searchString, $allProductsTranslate)
{
    $separateWord = separateWord($searchString);
    foreach ($allProductsTranslate as $item) {
        if (isset($item['english']) && $item['english'] === $separateWord) {
            // Correspondance trouvée, retourner le nom en français et le prix
            echo "La chaîne '$searchString' a été trouvée dans le tableau.\n";
            // echo "Le prix est : " . $item['price'] . "\n";
            if (isset($item['status']) && isset($item['status_buy']) && isset($item['price'])) {
                return [
                    'french' => $item['french'],
                    'price' => $item['price'],
                    'status' => $item['status'],
                    'status_buy' => $item['status_buy']
                ];
            } else {
                return [
                    'french' => $item['french'],
                    'price' => 0,
                    'status' => 0,
                    'status_buy' => 0
                ];
            }
        }
    }

    // Si la correspondance n'est pas trouvée
    echo "La chaîne '$searchString' n'a pas été trouvée dans le tableau.\n";

    // Retourner le mot en anglais et le prix par défaut
    return [
        'french' => $separateWord, // Retourne le mot en anglais en l'absence de traduction française
        'price' => 0,
        'status' => 0,
        'status_buy' => 0
    ];
}

function removeLastUnderscoreAndChar($input)
{
    return preg_replace('/_[a-zA-Z0-9]$/', '', $input);
}


// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $data = json_decode(file_get_contents('php://input'), true);
//     $allProducts = $data['allProducts'];
//     initAllProducts($allProducts);
// }

// initAllProducts($allProducts);



function isDolibarrProductListEmpty()
{
    $dol = new DolibarrAPI();
    $data = $dol->read1Products();

    // Vérifier si la réponse contient le message "No product found"
    if (isset($data->error) && $data->error->code == 404 && $data->error->message == "Not Found: No product found") {
        return true; // La liste est vide
    }

    return false; // La liste n'est pas vide
}

if (isDolibarrProductListEmpty()) {
    echo "Dolibarr product list is empty\n";
    initAllProducts($allProducts);
} else {
    echo "Dolibarr product list is not empty\n";

}

