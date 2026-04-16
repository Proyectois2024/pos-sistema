<?php

class ControladorCaja{

    /*=============================================
    MOSTRAR CAJA
    =============================================*/
    static public function ctrMostrarCaja($item, $valor){

        $tabla = "caja";
        $respuesta = ModeloCaja::mdlMostrarCaja($tabla, $item, $valor);

        return $respuesta;
    }

    /*=============================================
    OBTENER CAJA ABIERTA DE LA SUCURSAL
    =============================================*/
static public function ctrObtenerCajaAbierta(){

  $tabla = "caja";
  $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

  return ModeloCaja::mdlObtenerCajaAbierta($tabla, $idSucursal);
}
    /*=============================================
REGISTRAR MOVIMIENTO AUTOMÁTICO
=============================================*/
static public function ctrRegistrarMovimientoAutomatico($tipo, $descripcion, $monto, $referencia, $idReferencia, $fechaMovimiento = null){

    $usuarioId = isset($_SESSION["id"]) ? (int)$_SESSION["id"] : 0;
    $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

    if($usuarioId <= 0 || $idSucursal <= 0){
        return "error";
    }

    $cajaAbierta = ModeloCaja::mdlObtenerCajaAbierta("caja", $idSucursal);

    if(!$cajaAbierta || !is_array($cajaAbierta)){
        return "sin_caja";
    }

    if(!isset($cajaAbierta["id_sucursal"]) || (int)$cajaAbierta["id_sucursal"] !== $idSucursal){
        return "error";
    }

    $monto = (float)$monto;

    if($monto <= 0){
        return "error";
    }

    $datos = array(
        "id_caja" => (int)$cajaAbierta["id"],
        "tipo" => $tipo,
        "descripcion" => $descripcion,
        "monto" => $monto,
        "fecha" => ($fechaMovimiento !== null && $fechaMovimiento !== "") ? $fechaMovimiento : app_now(),
        "usuario" => $usuarioId,
        "id_sucursal" => $idSucursal,
        "referencia" => $referencia,
        "id_referencia" => (int)$idReferencia
    );

    return ModeloCaja::mdlRegistrarMovimientoCaja("movimientos_caja", $datos);
}

    /*=============================================
    CREAR APERTURA DE CAJA
    =============================================*/
    static public function ctrCrearApertura(){

        if(isset($_POST["nuevoMontoApertura"])){

            $montoApertura = trim($_POST["nuevoMontoApertura"]);
            $usuarioId = isset($_SESSION["id"]) ? (int)$_SESSION["id"] : 0;
            $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

            if($usuarioId <= 0 || $idSucursal <= 0){

                echo '<script>
                    swal({
                        type: "error",
                        title: "El usuario no tiene sucursal asignada",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "crear-venta";
                        }
                    });
                </script>';

                return;
            }

            if(!preg_match('/^[0-9]+(\.[0-9]{1,2})?$/', $montoApertura)){

                echo '<script>
                    swal({
                        type: "error",
                        title: "¡El monto no puede ir vacío o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "crear-venta";
                        }
                    });
                </script>';

                return;
            }

            if((float)$montoApertura < 0){
                echo '<script>
                    swal({
                        type: "error",
                        title: "El monto de apertura no puede ser negativo",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "crear-venta";
                        }
                    });
                </script>';
                return;
            }

            $cajaAbierta = ModeloCaja::mdlObtenerCajaAbierta("caja", $idSucursal);

            if($cajaAbierta && is_array($cajaAbierta)){

                echo '<script>
                    swal({
                        type: "error",
                        title: "Ya existe una caja abierta en esta sucursal",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "crear-venta";
                        }
                    });
                </script>';

                return;
            }

            $tabla = "caja";
            $fecha = app_now();

            $datos = array(
                "fecha_apertura" => $fecha,
                "monto_apertura" => $montoApertura,
                "usuario" => $usuarioId,
                "id_sucursal" => $idSucursal,
                "estado" => 1
            );

            $respuesta = ModeloCaja::mdlIngresarApertura($tabla, $datos);

            if($respuesta == "ok"){

                echo '<script>
                    swal({
                        type: "success",
                        title: "La caja ha sido abierta correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "crear-venta";
                        }
                    });
                </script>';
            }
        }
    }

    /*=============================================
CERRAR CAJA
=============================================*/
static public function ctrCerrarCaja(){

    if(isset($_POST["montoCierre"])){

        $usuarioId = isset($_SESSION["id"]) ? (int)$_SESSION["id"] : 0;
        $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;
        $montoCierre = (float)$_POST["montoCierre"];
        $idCajaPost = isset($_POST["idCaja"]) ? (int)$_POST["idCaja"] : 0;

        if($usuarioId <= 0 || $idSucursal <= 0){

            echo '<script>
                swal({
                    type: "error",
                    title: "El usuario no tiene sucursal asignada",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then(function(result){
                    if (result.value) {
                        window.location = "caja";
                    }
                });
            </script>';

            return;
        }

        if($montoCierre < 0){

            echo '<script>
                swal({
                    type: "error",
                    title: "El monto de cierre no puede ser negativo",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then(function(result){
                    if (result.value) {
                        window.location = "caja";
                    }
                });
            </script>';

            return;
        }

        $cajaAbierta = ModeloCaja::mdlObtenerCajaAbierta("caja", $idSucursal);

        if(!$cajaAbierta || !is_array($cajaAbierta)){

            echo '<script>
                swal({
                    type: "error",
                    title: "No hay una caja abierta para cerrar en esta sucursal",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then(function(result){
                    if (result.value) {
                        window.location = "caja";
                    }
                });
            </script>';

            return;
        }

        if(!isset($cajaAbierta["id_sucursal"]) || (int)$cajaAbierta["id_sucursal"] !== $idSucursal){

            echo '<script>
                swal({
                    type: "error",
                    title: "La caja abierta no pertenece a tu sucursal",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then(function(result){
                    if (result.value) {
                        window.location = "caja";
                    }
                });
            </script>';

            return;
        }

        if($idCajaPost > 0 && (int)$cajaAbierta["id"] !== $idCajaPost){

            echo '<script>
                swal({
                    type: "error",
                    title: "La caja a cerrar no coincide con la caja abierta actual",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then(function(result){
                    if (result.value) {
                        window.location = "caja";
                    }
                });
            </script>';

            return;
        }

        $montoApertura = isset($cajaAbierta["monto_apertura"]) ? (float)$cajaAbierta["monto_apertura"] : 0;

        $ingresos = ModeloCaja::mdlSumarIngresosCaja("movimientos_caja", (int)$cajaAbierta["id"]);
        $egresos = ModeloCaja::mdlSumarEgresosCaja("movimientos_caja", (int)$cajaAbierta["id"]);

        $totalIngresos = (is_array($ingresos) && isset($ingresos["total"])) ? (float)$ingresos["total"] : 0;
        $totalEgresos = (is_array($egresos) && isset($egresos["total"])) ? (float)$egresos["total"] : 0;

        $valorEsperado = $montoApertura + $totalIngresos - $totalEgresos;
        $diferencia = $montoCierre - $valorEsperado;

        $tabla = "caja";
        $fecha = app_now();

        $datos = array(
            "id" => (int)$cajaAbierta["id"],
            "fecha_cierre" => $fecha,
            "monto_cierre" => $montoCierre,
            "diferencia" => $diferencia,
            "estado" => 0
        );

        $respuesta = ModeloCaja::mdlCerrarCaja($tabla, $datos);

        if($respuesta == "ok"){

            echo '<script>
                swal({
                    type: "success",
                    title: "La caja ha sido cerrada correctamente",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then(function(result){
                    if (result.value) {
                        window.location = "caja";
                    }
                });
            </script>';
        }
    }
}
}