<?php

use App\DolibarrAPI;

require_once "../DolibarrAPI.php";
require_once "../Psr4AutoloaderClass.php";


$dol = new DolibarrAPI();

// $dol->displayGetProductIdByName("default:stick");
$dol->displayWarehouseStock("JoraxTV2");
