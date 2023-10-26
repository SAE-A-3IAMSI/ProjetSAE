<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');
    $decoded_data = json_decode($json_data);
    if ($decoded_data === null) {
        echo "Le fichier JSON n'est pas valide pour l'inscription.";
    } else {
        // Créez un fichier texte sur le serveur avec les données JSON
        $response = createNewWarehouse($decoded_data->player_name);
    }
} else {
    echo "Aucune donnée JSON reçue pour l'inscription.";
}



function createObject($class, $data)
{
    $url = "http://dolibarr/api/index.php/".$class; // Lien vers l'api dolibarr
    $data_json = json_encode($data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json',
        'DOLAPIKEY: 0jTMui5CO7nf0ma59XEf0sdF91lTQ4ZA' // Clef à changer si différente 
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function createNewWarehouse($name)
{
    $data = array(
        'label' => $name,
        'statut' => 1
    );
    return createObject("warehouses", $data);
}
