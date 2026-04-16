<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once "../controladores/gastos.controlador.php";
require_once "../modelos/gastos.modelo.php";

require_once "../controladores/proveedores.controlador.php";
require_once "../modelos/proveedores.modelo.php";

// Obtener un gasto específico por ID (para llenar el modal de edición)
if (isset($_POST["idGasto"])) {
  $id = $_POST["idGasto"];
  $gasto = ControladorGastos::ctrMostrarGastoPorId($id);
  echo json_encode($gasto);
  return;
}

// Eliminar gasto por ID
if (isset($_POST["idGastoEliminar"])) {
  $id = $_POST["idGastoEliminar"];
  $respuesta = ControladorGastos::ctrEliminarGasto($id);
  echo json_encode(["status" => $respuesta]);
  return;
}

class TablaGastos {

  public function mostrarTablaGastos() {
    $idProveedor = isset($_GET["proveedor"]) && $_GET["proveedor"] !== "" ? $_GET["proveedor"] : null;
    $mes = isset($_GET["mes"]) && $_GET["mes"] !== "" ? $_GET["mes"] : null;
    $anio = isset($_GET["anio"]) && $_GET["anio"] !== "" ? $_GET["anio"] : null;

    $gastos = ControladorGastos::ctrMostrarGastos($idProveedor, $mes, $anio);

    $data = [];

    foreach ($gastos as $i => $gasto) {
      $nombreProveedor = $gasto["proveedor"] ?? "Sin proveedor";
      $tipo = ucfirst($gasto["tipo"] ?? "Otro");

      $acciones = "<div class='btn-group'>
        <button class='btn btn-warning btnEditarGasto' idGasto='" . $gasto["id"] . "' data-toggle='modal' data-target='#modalEditarGasto'><i class='fa fa-pencil'></i></button>
        <button class='btn btn-danger btnEliminarGasto' idGasto='" . $gasto["id"] . "'><i class='fa fa-times'></i></button>
      </div>";

      $data[] = [
  ($i + 1), // Número de fila
  htmlspecialchars($gasto["descripcion"]),
  "Q. " . number_format($gasto["monto"], 2),
  htmlspecialchars($nombreProveedor),
  htmlspecialchars($tipo),
  $gasto["fecha"],
  $acciones
];
    }

    echo json_encode(["data" => $data]);
  }
}

$activar = new TablaGastos();
$activar->mostrarTablaGastos();
