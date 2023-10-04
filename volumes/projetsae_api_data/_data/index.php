<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');
    $decoded_data = json_decode($json_data);
    if ($decoded_data === null) {
        echo "Le fichier JSON n'est pas valide.";
    } else {
        // Créez un fichier texte sur le serveur avec les données JSON
        $file_name = 'donnees.json'; // Nom du fichier de destination
        $json_str = json_encode($decoded_data, JSON_PRETTY_PRINT);

        if (file_put_contents($file_name, $json_str) !== false) {
            echo "Les données JSON ont été enregistrées dans $file_name avec succès.";
        } else {
            echo "Erreur lors de l'enregistrement des données JSON.";
        }
    }
} else {
    echo "Aucune donnée JSON reçue.";
}