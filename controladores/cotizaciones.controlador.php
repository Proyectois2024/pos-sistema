<?php

class ControladorCotizaciones {

static public function ctrCrearCotizacion() {

    if(isset($_POST["codigo"])){

        if(
            empty($_POST["codigo"]) ||
            empty($_POST["tipo_docto"]) ||
            empty($_POST["id_cliente"]) ||
            empty($_POST["productosJsonCotizacion"])
        ){
            return;
        }

        $tabla = "cotizaciones_pedidos";

        $productos = json_decode($_POST["productosJsonCotizacion"], true);

        if(!is_array($productos) || count($productos) == 0){
            echo '<script>
                swal({
                    type: "error",
                    title: "No hay productos válidos en la cotización",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
            return;
        }

        $total = 0;

        foreach($productos as $producto){
            $sub = isset($producto["subtotal"]) ? floatval($producto["subtotal"]) : 0;
            if($sub > 0){
                $total += $sub;
            }
        }

        if($total <= 0){
            echo '<script>
                swal({
                    type: "error",
                    title: "El documento no tiene un total válido",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
            return;
        }

        $datos = array(
            "codigo" => trim($_POST["codigo"]),
            "tipo" => trim($_POST["tipo_docto"]),
            "id_cliente" => intval($_POST["id_cliente"]),
            "total" => $total
        );

        $idDocumento = ModeloCotizaciones::mdlGuardarEncabezado($tabla, $datos);

        if($idDocumento != "error"){

            $hayDetalleValido = false;

            foreach($productos as $producto){

                $idProducto  = isset($producto["id_producto"]) ? intval($producto["id_producto"]) : 0;
                $descripcion = isset($producto["descripcion"]) ? trim($producto["descripcion"]) : "";
                $cantidad    = isset($producto["cantidad"]) ? floatval($producto["cantidad"]) : 0;
                $unidad      = isset($producto["unidad"]) ? trim($producto["unidad"]) : "";
                $precio      = isset($producto["precio"]) ? floatval($producto["precio"]) : 0;
                $subtotal    = isset($producto["subtotal"]) ? floatval($producto["subtotal"]) : 0;

                if($idProducto <= 0 || $descripcion == "" || $cantidad <= 0 || $precio < 0 || $subtotal < 0){
                    continue;
                }

                $hayDetalleValido = true;

                $datosDetalle = array(
                    "id_docto" => $idDocumento,
                    "id_producto" => $idProducto,
                    "desc" => $descripcion,
                    "cantidad" => $cantidad,
                    "unidad" => $unidad,
                    "precio" => $precio,
                    "subtotal" => $subtotal
                );

                $respuestaDetalle = ModeloCotizaciones::mdlGuardarDetalle("detalles_docto", $datosDetalle);

                if($respuestaDetalle != "ok"){
                    echo '<script>
                        swal({
                            type: "error",
                            title: "Error al guardar el detalle del documento",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                    return;
                }
            }

            if(!$hayDetalleValido){
                echo '<script>
                    swal({
                        type: "error",
                        title: "Debes agregar al menos un producto válido",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }

            echo '<script>
                swal({
                    type: "success",
                    title: "Documento guardado correctamente",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then(function(result){
                    if(result.value){
                        window.location = "cotizaciones";
                    }
                });
            </script>';

        }else{

            echo '<script>
                swal({
                    type: "error",
                    title: "Error al guardar el encabezado del documento",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
    }
}

    /*=============================================
MOSTRAR COTIZACION
=============================================*/
static public function ctrMostrarCotizacion($item, $valor){

    $tabla = "cotizaciones_pedidos";
    return ModeloCotizaciones::mdlMostrarCotizacion($tabla, $item, $valor);
}

/*=============================================
MOSTRAR DETALLE DE COTIZACION
=============================================*/
static public function ctrMostrarDetalleCotizacion($idDocto){

    $tabla = "detalles_docto";
    return ModeloCotizaciones::mdlMostrarDetalleCotizacion($tabla, $idDocto);
}
}