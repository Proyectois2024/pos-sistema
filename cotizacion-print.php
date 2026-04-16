<?php

require_once "controladores/cotizaciones.controlador.php";
require_once "modelos/cotizaciones.modelo.php";

require_once "controladores/clientes.controlador.php";
require_once "modelos/clientes.modelo.php";

$id = $_GET["idDocto"];

$cotizacion = ControladorCotizaciones::ctrMostrarCotizacion("id", $id);
$detalle = ControladorCotizaciones::ctrMostrarDetalleCotizacion($id);
$cliente = ControladorClientes::ctrMostrarClientes("id", $cotizacion["id_cliente"]);

?>