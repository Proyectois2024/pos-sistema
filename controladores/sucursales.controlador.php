<?php

class ControladorSucursales {

  static public function ctrMostrarSucursales($item = null, $valor = null){

    $tabla = "sucursales";

    $respuesta = ModeloSucursales::mdlMostrarSucursales($tabla, $item, $valor);

    return $respuesta;
  }
}