<?php

use App\DolibarrAPI;

require_once "../DolibarrAPI.php";
require_once "../Psr4AutoloaderClass.php";

$dol = new DolibarrAPI();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');
    $decoded_data = json_decode($json_data, true);

    if ($decoded_data === null) {
        echo json_encode(["error" => "Le fichier JSON n'est pas valide."]);
    } else {
        $response_data = $dol->getWarehouseStock($decoded_data["playername"]);

        $response_str = json_encode($response_data);

        echo $response_str;
    }
} else {
    echo "Aucune donnée JSON reçue.";
}
