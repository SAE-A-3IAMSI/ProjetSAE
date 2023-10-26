<?php
$data = file_get_contents('allProducts.json');
$allProducts = json_decode($data, true); // Le deuxième argument permet de retourner un tableau associatif
foreach ($allProducts as $key => $value) {
    $allProducts[$key] = removeLastUnderscoreAndChar($value);
}
$allProducts = array_unique($allProducts);

function createObject($class, $data)
{
    $url = "http://localhost/api/index.php/" . $class;
    $data_json = json_encode($data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json',
        'DOLAPIKEY: Z35STg78bC2kPB8AIXke4rof3MlXqj17' // clef à changer
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
        'price' => addPriceToProduct($name),
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

// echo separerMot($allProducts);

function addPriceToProduct($product)
{
    $price = 0;
    if (strpos($product, "iron") !== false) {
        return $price += 10;
    }
    return $price;
}

function addPriceToProducts($allProducts)
{
    $price = 0;
    foreach ($allProducts as $product) {
        if (strpos($product, "iron") !== false) {
            echo $product . " contains iron\n";
            $price += 10;
        }
    }
    return $price;
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

initAllProducts($allProducts);