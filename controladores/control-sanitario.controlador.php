<?php

class ControladorControlSanitario {

    /*=============================================
    CREAR REGISTRO SANITARIO
    =============================================*/
    static public function ctrCrearSanitario() {

        if (isset($_POST["idVentaSanitaria"]) || isset($_POST["idAnimal"])) {

            $idAnimal          = isset($_POST["idAnimal"]) ? (int)$_POST["idAnimal"] : 0;
            $idVenta           = !empty($_POST["idVentaSanitaria"]) ? (int)$_POST["idVentaSanitaria"] : null;
            $producto          = isset($_POST["productoSanitario"]) ? trim(strip_tags($_POST["productoSanitario"])) : "";
            $tipo              = isset($_POST["tipoSanitario"]) ? trim(strip_tags($_POST["tipoSanitario"])) : "tratamiento";
            $dosis             = isset($_POST["dosisSanitaria"]) ? trim(strip_tags($_POST["dosisSanitaria"])) : "";
            $fechaAplicacion   = !empty($_POST["fechaAplicacion"]) ? trim($_POST["fechaAplicacion"]) : app_now("Y-m-d");
            $proximaAplicacion = !empty($_POST["proximaDosis"]) ? trim($_POST["proximaDosis"]) : null;
            $observaciones     = isset($_POST["observacionesSanitaria"]) ? trim(strip_tags($_POST["observacionesSanitaria"])) : "";
            $idSucursal        = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;
            $usuarioId         = isset($_SESSION["id"]) ? (int)$_SESSION["id"] : 0;

            // Validaciones básicas
            if ($idAnimal <= 0 && empty($producto)) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡Debes seleccionar un animal y especificar el producto o tratamiento!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }

            $tabla = "control_sanitario";

            $datos = array(
                "id_animal"          => $idAnimal,
                "id_venta"           => $idVenta,
                "producto"           => $producto,
                "tipo"               => $tipo,
                "dosis"              => $dosis,
                "fecha_aplicacion"   => $fechaAplicacion,
                "proxima_aplicacion" => $proximaAplicacion,
                "observaciones"      => $observaciones,
                "id_sucursal"        => $idSucursal,
                "usuario"            => $usuarioId
            );

            $respuesta = ModeloControlSanitario::mdlIngresarSanitario($tabla, $datos);

            if ($respuesta == "ok") {

                echo '<script>
                    swal({
                        type: "success",
                        title: "Registro sanitario guardado correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "control-sanitario";
                        }
                    });
                </script>';

            } else {

                echo '<script>
                    swal({
                        type: "error",
                        title: "Error al guardar el registro sanitario",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }


    /*=============================================
    MOSTRAR REGISTROS SANITARIOS
    =============================================*/
    static public function ctrMostrarSanitario($item = null, $valor = null) {

        $tabla = "control_sanitario";

        return ModeloControlSanitario::mdlMostrarSanitario($tabla, $item, $valor);

    }

    /*=============================================
    ELIMINAR REGISTRO SANITARIO
    =============================================*/
    static public function ctrEliminarSanitario() {

        if (isset($_GET["idSanitario"])) {

            $tabla = "control_sanitario";
            $datos = (int)$_GET["idSanitario"];

            $respuesta = ModeloControlSanitario::mdlEliminarSanitario($tabla, $datos);

            if ($respuesta == "ok") {

                echo '<script>
                    swal({
                        type: "success",
                        title: "El registro ha sido eliminado correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "control-sanitario";
                        }
                    });
                </script>';

            }
        }
    }

}
