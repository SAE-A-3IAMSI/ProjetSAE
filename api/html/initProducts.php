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
        'DOLAPIKEY: fcfvx5j1ptLP80I01U2xAZ520yrMQQES' // clef à changer
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
}

function createNewProduct($name, $label)
{
    $data = array(
        'ref' => $name,
        'label' => $label['french'],
        'price' => $label['price'],
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
        createNewProduct($entry, frenchNameAndPrice($entry, $allProductsTranslate));
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


function frenchNameAndPrice($searchString, $allProductsTranslate)
{
    $separateWord = separateWord($searchString);
    foreach ($allProductsTranslate as $item) {
        if (isset($item['english']) && $item['english'] === $separateWord) {
            // Correspondance trouvée, retourner le nom en français et le prix
            echo "La chaîne '$searchString' a été trouvée dans le tableau.\n";
            echo "Le prix est : " . $item['price'] . "\n";
            return [
                'french' => $item['french'],
                'price' => $item['price'],
            ];
        }
    }

    // Si la correspondance n'est pas trouvée
    echo "La chaîne '$searchString' n'a pas été trouvée dans le tableau.\n";

    // Retourner le mot en anglais et le prix par défaut
    return [
        'french' => $searchString, // Retourne le mot en anglais en l'absence de traduction française
        'price' => 1,
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

function read1Products()
{
    $ch = curl_init();
    $url = "http://dolibarr/api/index.php/products?sortfield=t.ref&sortorder=ASC&limit=1&ids_only=true"; //mettre localhost (si sur machine) ou dolibarr (si avec minetest)
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    $headers = array(
        "Accept: application/json",
        "DOLAPIKEY: fcfvx5j1ptLP80I01U2xAZ520yrMQQES" // clef à changer
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
        return false;
    }
    return true;
}

if (isDolibarrProductListEmpty()) {
    echo "Dolibarr product list is empty\n";
    initAllProducts($allProducts);
}
else {
    echo "Dolibarr product list is not empty\n";
}