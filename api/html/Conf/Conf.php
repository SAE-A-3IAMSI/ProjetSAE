<?php


namespace Api\Conf;
require_once "../Psr4AutoloaderClass.php";

class Conf {

static private array $infodoli = array(
    'clefAPI' => 'SRh3NH7f32d0oUa7XfyYUA22Lhsq4o7T',
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

