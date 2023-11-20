<?php

use App\Conf\DolibarrAPI;
require_once "DolibarrAPI.php";
require_once "Psr4AutoloaderClass.php";


$dol = new DolibarrAPI();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');
    $decoded_data = json_decode($json_data);
    if ($decoded_data === null) {
        echo "Le fichier JSON n'est pas valide pour l'inscription.";
    } else {
        // Créez un fichier texte sur le serveur avec les données JSON
        $dol->createNewWarehouse($decoded_data->player_name);
        
    }
} else {
    echo "Aucune donnée JSON reçue pour l'inscription.";
}



