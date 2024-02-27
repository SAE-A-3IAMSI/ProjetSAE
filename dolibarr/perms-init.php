<?php
$servername = "mariadb";
$username = "root";
$password = "SAEroot";
$dbname = "dolibarrSAE";
$apiKey = "SRh3NH7f32d0oUa7XfyYUA22Lhsq4o7T";
$userId = 1;


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$modules = ['produit', 'stock'];


foreach ($modules as $module) {
    $sql_rights_def = "SELECT id FROM llx_rights_def WHERE module = '$module'";
    $result_rights_def = $conn->query($sql_rights_def);

    if ($result_rights_def) {
        while ($row_rights_def = $result_rights_def->fetch_assoc()) {
            $rightId = $row_rights_def['id'];

            $sql_check_rights = "SELECT rowid FROM llx_user_rights WHERE fk_user = $userId AND fk_id = $rightId";
            $result_check_rights = $conn->query($sql_check_rights);

            if ($result_check_rights->num_rows == 0) {
                $sql_insert_rights = "INSERT INTO llx_user_rights (fk_user, fk_id) VALUES ($userId, $rightId)";
                if ($conn->query($sql_insert_rights)) {
                    echo "Droit $rightId pour le module $module attribué avec succès à l'utilisateur $userId.\n";
                } else {
                    echo "Erreur lors de l'attribution du droit $rightId pour le module $module à l'utilisateur $userId : " . $conn->error . "\n";
                }
            }
        }
    } else {
        echo "Erreur lors de la récupération des droits pour le module $module : " . $conn->error . "\n";
    }
}

$conn->close();