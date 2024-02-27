<?php

use App\DolibarrAPI;
require_once "../DolibarrAPI.php";
require_once "../Psr4AutoloaderClass.php";


$dol = new DolibarrAPI();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');
    $decoded_data = json_decode($json_data, true);
    echo "inventoryManager : ". $json_data;
    if ($decoded_data === null) {
        echo "Le fichier JSON n'est pas valide pour l'inventaire.";
    } else {
        $dol->updateDataBase($decoded_data);
    }
} 
