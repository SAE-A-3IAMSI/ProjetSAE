<?php

namespace App\Conf;
require_once "Psr4AutoloaderClass.php";

class Conf {

static private array $infodoli = array(
    'clefAPI' => 'XZZgl1xqz74yYGO2C36brNcf2U705YJm',
    'lien' => 'http://localhost/api/index.php', // localhost à la place de localhost pour le faire fonctionner sur Docker
);

static public function getClefAPI() : string {
    return static::$infodoli['clefAPI'];
}
static public function getLien() : string {
    return static::$infodoli['lien'];
}
}
?>
