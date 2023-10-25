<?php
$data = file_get_contents('allProducts.json');
$allProducts = json_decode($data, true); // Le deuxième argument permet de retourner un tableau associatif

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

function createNewProduct($name)
{
    $data = array(
        'ref' => $name,
        'label' => $name,
        // 'price' => $price,
        'status' => 1,
        'status_buy' => 1
    );
    createObject("products", $data);
}

function initAllProducts($productList)
{
    foreach ($productList as $entry) {
        createNewProduct($entry);
        echo "product: " . $entry . " has been created\n";
    }
}

// initAllProducts($allProducts);

function separerMot($productList)
{
    $mots = array(); // Initialisez le tableau en dehors de la boucle

    foreach ($productList as $entry) {
        $mots_temp = explode(":", $entry); // Stockez le résultat dans un tableau temporaire

        $mot_avant_deux_points = $mots_temp[0];

        // Vérifiez si le mot existe déjà dans le tableau
        if (!in_array($mot_avant_deux_points, $mots)) {
            $mots[] = $mot_avant_deux_points; // Ajoutez le mot au tableau
        }
    }

    var_dump($mots);
}

// echo separerMot($allProducts);

function enleverDoublons($productList)
{
    $mots = array(); // Initialisez le tableau en dehors de la boucle

    foreach ($productList as $entry) {
        if (!in_array($entry, $mots)) {
            $mots[] = $entry; // Ajoutez le mot au tableau
        }
    }

    var_dump($mots);
}

enleverDoublons($allProducts);

// if ($allProducts === null) {
//     echo "Erreur lors du décodage du JSON.";
// } else {
//     // Parcourir et afficher les éléments
//     foreach ($allProducts as $product) {
//         echo $product."\n";
//     }
// }

// Création du json avec l'ancien tableau de produits
// $data = json_encode($allProducts);
// file_put_contents('allProducts.json', $data);
