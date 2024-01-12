<?php

use App\DolibarrAPI;

require_once "../DolibarrAPI.php";
require_once "../Psr4AutoloaderClass.php";

$dol = new DolibarrAPI();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérez les données JSON de la requête
    $json_data = file_get_contents('php://input');
    $decoded_data = json_decode($json_data, true);

    if ($decoded_data === null) {
        // En cas d'erreur de décodage JSON, renvoyez une réponse JSON d'erreur
        echo json_encode(["error" => "Le fichier JSON n'est pas valide."]);
    } else {
        // Appelez la fonction pour récupérer l'inventaire
        $response_data = $dol->getWarehouseStock($decoded_data["playername"]);

        // Convertissez le tableau en JSON
        $response_str = json_encode($response_data);

        // Répondez avec le JSON créé
        echo $response_str;
    }
} else {
    echo "Aucune donnée JSON reçue.";
}
