<?php

use App\DolibarrAPI;
require_once "../DolibarrAPI.php";
require_once "../Psr4AutoloaderClass.php";


$dol = new DolibarrAPI();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');
    $decoded_data = json_decode($json_data, true);
    echo "inventoryManager : "+ $json_data;
    if ($decoded_data === null) {
        echo "Le fichier JSON n'est pas valide pour l'inventaire.";
    } else {
        // Créez un fichier texte sur le serveur avec les données JSON
        $dol->updateDataBase($decoded_data);
        //$file_name = 'donnees2.json'; // Nom du fichier de destination
        //$json_str = json_encode($decoded_data, JSON_PRETTY_PRINT);
        //file_put_contents($file_name, $json_str);
    }
} 
