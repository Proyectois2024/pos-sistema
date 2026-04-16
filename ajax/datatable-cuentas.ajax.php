<?php

require_once "../controladores/ventas.controlador.php";
require_once "../modelos/ventas.modelo.php";

class TablaCuentas{

  public function mostrarTablaCuentas(){

    $item = null;
    $valor = null;

    $ventas = ControladorVentas::ctrMostrarVentas($item, $valor);

    $datosJson = '{
      "data": [';

    for($i = 0; $i < count($ventas); $i++){

      if($ventas[$i]["metodo_pago"] == "Crédito"){

        $botones = "<div class='btn-group'>
                      <button class='btn btn-success btnAbonar'
                      idVenta='".$ventas[$i]["id"]."'>Abonar</button>
                    </div>";

        $datosJson .='[
          "'.$ventas[$i]["codigo"].'",
          "'.$ventas[$i]["cliente"].'",
          "Q '.$ventas[$i]["total"].'",
          "Q '.$ventas[$i]["saldo"].'",
          "'.date("d/m/Y H:i", strtotime($ventas[$i]["fecha_vencimiento"])).'",
          "'.$ventas[$i]["estado"].'",
          "'.$botones.'"
        ],';

      }

    }

    $datosJson = substr($datosJson, 0, -1);
    $datosJson .= '] }';

    echo $datosJson;

  }

}

$activarCuentas = new TablaCuentas();
$activarCuentas -> mostrarTablaCuentas();