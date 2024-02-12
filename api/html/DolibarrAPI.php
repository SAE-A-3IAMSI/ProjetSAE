<?php

namespace App;

use App\Conf\Conf;

require_once "Conf/Conf.php";
require_once "Psr4AutoloaderClass.php";

use Exception;

class DolibarrAPI
{


    /*liste des fonctions principales:
- créer Entrepôt
- créer Produit
- supprimer Produit
- Modification (ajout/retrait) Entrepôt
- supprimer tous les produits
- initialiser les produits (à partir d'une array)
*/

    /* fichier updateInventory.php appelé à chaque modification de l'inventaire du joueur */

    /* à faire:
- faire un traitement du json
- faire un appel api à partir du json renvoyé par minetest (traitement des données json + fonctions)
- faire un appel api: initialisation des produits à la création du serveur
- faire un appel api: pour la création des entrepôts lorsque nouveau joueur créé
*/


    private string $dolapikey;
    private string $lien;


    public function __construct()
    {
        $this->dolapikey = Conf::getClefAPI();
        $this->lien = Conf::getLien();
    }

    public function getdolapikey(): string
    {
        return $this->dolapikey;
    }
    public function getlien(): string
    {
        return $this->lien;
    }

    // Fonction permettant de lire dans Dolibarr
    function readAllUsers()
    {
        $ch = curl_init();
        $url = $this->lien . "/users";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $headers = array(
            "Accept: application/json",
            "DOLAPIKEY: " . $this->dolapikey
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $dataList = curl_exec($ch);
        curl_close($ch);
        echo $dataList;
    }

    function readAllProducts()
    {
        $ch = curl_init();
        $url = $this->lien . "/products?sortfield=t.ref&sortorder=ASC&limit=500&ids_only=true";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $headers = array(
            "Accept: application/json",
            "DOLAPIKEY: " . $this->dolapikey
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $dataList = curl_exec($ch);
        curl_close($ch);
        return json_decode($dataList);
    }

    function getObjectByName($class, $name)
    {
        $url = $this->lien . "/" . $class . "?sortfield=t.rowid&sortorder=ASC&limit=100&sqlfilters=(t.ref%3Alike%3A'" . $name . "')";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'DOLAPIKEY: ' . $this->dolapikey
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    function addItemToStock($productName, $warehouseName, $qty)
    {
        $url = $this->lien . "/stockmovements";
        $data = array(
            'product_id' => $this->getProductIdByName($productName),
            'warehouse_id' => $this->getWarehouseIdByName($warehouseName),
            'qty' => $qty,
        );
        $data_json = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Accept: application/json',
                'DOLAPIKEY: ' . $this->dolapikey
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
    }


    function createObject($class, $data)
    {
        $url = $this->lien . "/" . $class;
        echo $url;
        $data_json = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Accept: application/json',
                "DOLAPIKEY: " . $this->dolapikey
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
    }


    function deleteProduct($id)
    {
        $ch = curl_init();
        $url = $this->lien . "/products/" . $id;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        $headers = array(
            "Accept: application/json",
            "DOLAPIKEY: " . $this->dolapikey
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
        echo "product: " . $id . " has been deleted\n";
    }

    function getItemStock($productName, $warehouseName)
    {
        $productId = $this->getProductIdByName($productName);
        $warehouseId = $this->getWarehouseIdByName($warehouseName);

        $url = $this->lien . "/products/" . $productId . "/stock?selected_warehouse_id=" . $warehouseId;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Accept: application/json',
                "DOLAPIKEY: " . $this->dolapikey
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $responseData = json_decode($response, true);
        if ($responseData !== null) {
            if (isset($responseData['stock_warehouses'])) {
                $real = $responseData['stock_warehouses'][$warehouseId]['real'];
                return $real;
            } else {
                echo "La réponse JSON est invalide.";
            }
        }
    }

    public function getItemStockById(
        $productId,
        $warehouseId
    ) {
        $url = $this->lien . "/products/" . $productId . "/stock?selected_warehouse_id=" . $warehouseId;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            "DOLAPIKEY: " . $this->dolapikey
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Essayez de décoder la réponse JSON
        $responseData = json_decode($response, true);


        // Lancez une exception si la réponse JSON est invalide
        if ($responseData === null) {
            throw new Exception("La réponse JSON est invalide.");
        }

        // Vérifiez si la clé 'stock_warehouses' est présente dans la réponse
        if (isset($responseData['stock_warehouses'])) {
            $real = $responseData['stock_warehouses'][$warehouseId]['real'];
            return $real;
        } else {
            // Ne faites rien si la clé 'stock_warehouses' est absente
            // ou lancez une exception personnalisée si nécessaire
            return null;
        }
    }

    function getWarehouseIdByName($name)
    {
        $data = $this->getObjectByName("warehouses", $name);
        $data = json_decode($data, true);
        foreach ($data as $entry) {
            if (isset($entry["id"])) {
                return $entry["id"];
            }
        }
    }

    function getProductIdByName($name)
    {
        $productName = $name;
        if (str_contains($name, ':')) {
            $productName = str_replace(':', '_', $name);
        }
        $data = $this->getObjectByName("products", $productName);
        $data = json_decode($data, true);
        foreach ($data as $entry) {
            if (isset($entry["id"])) {
                return $entry["id"];
            }
        }
    }

    function displayGetProductIdByName($name)
    {
        $productName = $name;
        if (str_contains($name, ':')) {
            $productName = str_replace(':', '_', $name);
        }
        $data = $this->getObjectByName("products", $productName);
        $data = json_decode($data, true);
        foreach ($data as $entry) {
            if (isset($entry["id"])) {
                echo $entry["id"];
            }
        }
    }

    function getProductQtyByName($name)
    {
        $productName = $name;
        if (str_contains($name, ':')) {
            $productName = str_replace(':', '_', $name);
        }
        $data = $this->getObjectByName("products", $productName);
        $data = json_decode($data, true);
        foreach ($data as $entry) {
            if (isset($entry["qty"])) {
                return $entry["qty"];
            }
        }
    }


    function createNewWarehouse($name)
    {
        $data = array(
            'label' => $name,
            'statut' => 1
        );
        $this->createObject("warehouses", $data);
    }

    function createNewProduct($name, $label)
    {
        $data = array(
            'ref' => $name,
            'label' => $label['french'],
            'price' => $label['price'],
            'status' => $label['status'],
            'status_buy' => $label['status_buy']
        );
        $this->createObject("products", $data);
    }


    function deleteAllProducts()
    {
        try {
            $allProductsId = $this->readAllProducts();
            foreach ($allProductsId as $entry) {
                $this->deleteProduct($entry);
                echo "product: " . $entry . " has been deleted\n";
            }
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }

    function initAllProducts($productList)
    {
        foreach ($productList as $entry) {
            $this->createNewProduct($entry['name'], $entry['label']);
            echo "product: " . $entry . " has been created\n";
        }
    }

    function updateDataBase($jsonData)
    {
        $userId = $jsonData['player_name'];
        foreach ($jsonData['inventory'] as $entry) {
            $stockQty = $this->getItemStock($entry['name'], $userId);
            $updatedStock = $entry['quantity'] - $stockQty;
            if ($updatedStock != 0) {
                $this->addItemToStock($entry['name'], $userId, $updatedStock);
            }
        }
    }

    function read1Products()
    {
        $ch = curl_init();
        $url = $this->lien . "/products?sortfield=t.ref&sortorder=ASC&limit=1&ids_only=true";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $headers = array(
            "Accept: application/json",
            "DOLAPIKEY: " . $this->dolapikey // clef à changer
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $dataList = curl_exec($ch);
        curl_close($ch);
        echo "dataList :\n" . $dataList . "\n";
        return json_decode($dataList);
    }

    //Fonction permettant de récupérer le stock des produits dans un entrepôt
    public function getWarehouseStock($warehouseName)
    {
        $warehouseId = $this->getWarehouseIdByName($warehouseName);
        $ch = curl_init();
        $url = $this->lien . "/warehouses/" . $warehouseId;
        curl_setopt(
            $ch,
            CURLOPT_URL,
            $url
        );
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $headers = array(
            "Accept: application/json",
            "DOLAPIKEY: " . $this->dolapikey
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $dataList = curl_exec($ch);
        curl_close($ch);

        $decodedData = json_decode($dataList, true);
        if (isset($decodedData['warehouse_stock_info'])) {
            return $decodedData['warehouse_stock_info'];
        } else {
            return array();
        }
    }


    // afficher le résultat de la fonction getWarehouseStock()
    function displayWarehouseStock($warehouseName)
    {
        $stockList = $this->getWarehouseStock($warehouseName);
        echo "Stock de l'entrepôt " . $warehouseName . " :\n";
        foreach ($stockList as $entry) {
            echo $entry['item'] . " : " . $entry['quantity'] . "\n";
        }
    }

    function getNameProductById($id)
    {
        $url = $this->lien . "/products/" . $id;
        $ch = curl_init();
        curl_setopt(
            $ch,
            CURLOPT_URL,
            $url
        );
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Accept: application/json',
                "DOLAPIKEY: " . $this->dolapikey
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $responseData = json_decode($response, true);
        if ($responseData !== null) {
            if (isset($responseData['ref'])) {
                return $responseData['ref'];
            } else {
                echo "La réponse JSON est invalide.";
            }
        }
    }


    /*
function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}*/
}
