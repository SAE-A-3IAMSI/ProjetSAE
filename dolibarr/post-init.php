<?php
$servername = "mariadb";
$username = "Admin1";
$password = "minetest1234=+";
$dbname = "dolibarrSAE";
$apiKey = "SRh3NH7f32d0oUa7XfyYUA22Lhsq4o7T";

$userId = 1;
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$modulesToUpdate = [
    'MAIN_MODULE_API',
    'MAIN_MODULE_PRODUCT',
    'MAIN_MODULE_STOCK',
];

foreach ($modulesToUpdate as $module) {
    $sql = "INSERT INTO llx_const (name, value, type, visible, note) VALUES ('$module', '1', 'chaine', '0', '') ON DUPLICATE KEY UPDATE value = '1'";

    if ($conn->query($sql) !== TRUE) {
        echo "Error updating module $module: " . $conn->error . "\n";
    } else {
        echo "Module $module activated successfully\n";
    }
}
$apiProductionModeSql = "INSERT INTO llx_const (name, value, type, visible, note) VALUES ('API_PRODUCTION_MODE', '1', 'chaine', '0', '') ON DUPLICATE KEY UPDATE value = '1'";

if ($conn->query($apiProductionModeSql) !== TRUE) {
    echo "Error setting API production mode: " . $conn->error . "\n";
} else {
    echo "API production mode set successfully\n";
}

$updateApiKeySql = "UPDATE llx_user SET api_key='$apiKey' WHERE rowid=$userId";

if ($conn->query($updateApiKeySql) === TRUE) {
    echo "API key updated successfully for user $userId\n";
} else {
    echo "Error updating API key for user $userId: " . $conn->error . "\n";
}

$conn->close();
