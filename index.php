<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once "controladores/plantilla.controlador.php";
require_once "controladores/usuarios.controlador.php";
require_once "controladores/categorias.controlador.php";
require_once "controladores/productos.controlador.php";
require_once "controladores/clientes.controlador.php";
require_once "controladores/ventas.controlador.php";
require_once "controladores/proveedores.controlador.php";
require_once "controladores/gastos.controlador.php";
require_once "controladores/caja.controlador.php";
require_once "controladores/control-sanitario.controlador.php";
require_once "controladores/cotizaciones.controlador.php";
require_once "controladores/sucursales.controlador.php";
require_once "controladores/transferencias.controlador.php";

require_once "modelos/usuarios.modelo.php";
require_once "modelos/categorias.modelo.php";
require_once "modelos/productos.modelo.php";
require_once "modelos/clientes.modelo.php";
require_once "modelos/ventas.modelo.php";
require_once "modelos/proveedores.modelo.php";
require_once "modelos/gastos.modelo.php";
require_once "modelos/caja.modelo.php";
require_once "modelos/control-sanitario.modelo.php";
require_once "modelos/cotizaciones.modelo.php";
require_once "modelos/sucursales.modelo.php";
require_once "modelos/transferencias.modelo.php";

$plantilla = new ControladorPlantilla();
$plantilla -> ctrPlantilla();
