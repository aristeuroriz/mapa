<?php


phpinfo();


$str = file_get_contents('http://localhost/mapa/js/pontos.json');

$json = json_decode($str, true);

echo '<pre>' . print_r($json, true) . '</pre>';


?>