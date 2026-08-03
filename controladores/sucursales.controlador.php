<?php

class ControladorSucursales {

    /*=============================================
    MOSTRAR SUCURSALES
    =============================================*/
    static public function ctrMostrarSucursales(?string $item = null, mixed $valor = null): array|bool {

        $tabla = "sucursales";

        return ModeloSucursales::mdlMostrarSucursales($tabla, $item, $valor);
    }
}
