<?php

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$file = __DIR__ . $path;

if ($path !== "/" && is_file($file)) {
    return false;
}

if ($path !== "/" && !isset($_GET["ruta"])) {
    $_GET["ruta"] = trim($path, "/");
}

require __DIR__ . "/index.php";
