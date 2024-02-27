<?php

use App\DolibarrAPI;
require_once "../DolibarrAPI.php";
require_once "../Psr4AutoloaderClass.php";


$data = file_get_contents('allProducts.json');
$allProducts = json_decode($data, true);
foreach ($allProducts as $key => $value) {
    $allProducts[$key] = removeLastUnderscoreAndChar($value);
}
$allProducts = array_unique($allProducts);





function initAllProducts($productList)
{
    echo "initAllProducts\n";
    $dataTranslate = file_get_contents('productsEN-FR.json');
    $allProductsTranslate = json_decode($dataTranslate, true);
    foreach ($productList as $entry) {
        $dol = new DolibarrAPI();
        $dol->createNewProduct($entry, frenchNameAndPrice($entry, $allProductsTranslate));
        echo "product: " . $entry . " has been created\n";
    }
}

function separateWords($productList)
{
    $words = array();

    foreach ($productList as $entry) {
        $words_temp = explode(":", $entry);

        $words_after_colon = $words_temp[1];
        $words_after_colon_without_ = str_replace('_', ' ', $words_after_colon);

        if (!in_array($words_after_colon_without_, $words)) {
            $words[] = $words_after_colon_without_;
        }
    }

    return $words;
}

function separateWord($product)
{
    $words_temp = explode(":", $product);

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
            echo "La chaîne '$searchString' a été trouvée dans le tableau.\n";
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

    echo "La chaîne '$searchString' n'a pas été trouvée dans le tableau.\n";

    return [
        'french' => $separateWord,
        'price' => 0,
        'status' => 0,
        'status_buy' => 0
    ];
}

function removeLastUnderscoreAndChar($input)
{
    return preg_replace('/_[a-zA-Z0-9]$/', '', $input);
}

function isDolibarrProductListEmpty()
{
    $dol = new DolibarrAPI();
    $data = $dol->read1Products();

    if (isset($data->error) && $data->error->code == 404 && $data->error->message == "Not Found: No product found") {
        return true; 
    }

    return false; 
}

if (isDolibarrProductListEmpty()) {
    echo "Dolibarr product list is empty\n";
    initAllProducts($allProducts);
} else {
    echo "Dolibarr product list is not empty\n";
}
