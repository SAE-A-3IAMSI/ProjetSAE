<?php

namespace App\Conf;
require_once "../Psr4AutoloaderClass.php";

class Conf {

static private array $infodoli = array(
    'clefAPI' => 'B0ZC3k0QxkP3Yjfyyi5QKbq89ZF4O6i3',
    'lien' => 'http://dolibarr/api/index.php', // localhost à la place de localhost pour le faire fonctionner sur Docker
);

static public function getClefAPI() : string {
    return static::$infodoli['clefAPI'];
}
static public function getLien() : string {
    return static::$infodoli['lien'];
}
}
?>
