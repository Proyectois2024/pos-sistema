<?php

class ControladorCotizaciones {

    /*=============================================
    CREAR COTIZACIÓN / PEDIDO
    =============================================*/
    static public function ctrCrearCotizacion() {

        if (isset($_POST["codigo"])) {

            if (
                empty($_POST["codigo"]) ||
                empty($_POST["tipo_docto"]) ||
                empty($_POST["id_cliente"]) ||
                empty($_POST["productosJsonCotizacion"])
            ) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡Los campos del documento no pueden ir vacíos!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }

            $productos = json_decode($_POST["productosJsonCotizacion"], true);

            if (!is_array($productos) || count($productos) == 0) {
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

            // Calcular total general a partir del arreglo de productos
            $total = 0;
            foreach ($productos as $producto) {
                $sub = isset($producto["subtotal"]) ? (float)$producto["subtotal"] : 0;
                if ($sub > 0) {
                    $total += $sub;
                }
            }

            if ($total <= 0) {
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

            $db = Conexion::conectar();

            try {

                $db->beginTransaction();

                $tabla = "cotizaciones_pedidos";
                $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;
                $idUsuario = isset($_SESSION["id"]) ? (int)$_SESSION["id"] : 0;

                $datos = array(
                    "codigo"      => trim($_POST["codigo"]),
                    "tipo"        => trim($_POST["tipo_docto"]),
                    "id_cliente"  => (int)$_POST["id_cliente"],
                    "id_sucursal" => $idSucursal,
                    "id_vendedor" => $idUsuario,
                    "total"       => $total
                );

                $idDocumento = ModeloCotizaciones::mdlGuardarEncabezado($tabla, $datos);

                if (!$idDocumento || $idDocumento === "error") {
                    throw new Exception("Error al guardar el encabezado del documento");
                }

                $hayDetalleValido = false;

                foreach ($productos as $producto) {

                    $idProducto  = isset($producto["id_producto"]) ? (int)$producto["id_producto"] : 0;
                    $descripcion = isset($producto["descripcion"]) ? trim($producto["descripcion"]) : "";
                    $cantidad    = isset($producto["cantidad"]) ? (float)$producto["cantidad"] : 0;
                    $unidad      = isset($producto["unidad"]) ? trim($producto["unidad"]) : "";
                    $precio      = isset($producto["precio"]) ? (float)$producto["precio"] : 0;
                    $subtotal    = isset($producto["subtotal"]) ? (float)$producto["subtotal"] : 0;

                    if ($idProducto <= 0 || $descripcion === "" || $cantidad <= 0 || $precio < 0 || $subtotal < 0) {
                        continue;
                    }

                    $hayDetalleValido = true;

                    $datosDetalle = array(
                        "id_docto"    => $idDocumento,
                        "id_producto" => $idProducto,
                        "desc"        => $descripcion,
                        "cantidad"    => $cantidad,
                        "unidad"      => $unidad,
                        "precio"      => $precio,
                        "subtotal"    => $subtotal
                    );

                    $respuestaDetalle = ModeloCotizaciones::mdlGuardarDetalle("detalles_docto", $datosDetalle);

                    if ($respuestaDetalle !== "ok") {
                        throw new Exception("Error al guardar el detalle del producto: " . $descripcion);
                    }
                }

                if (!$hayDetalleValido) {
                    throw new Exception("Debes agregar al menos un producto válido a la lista");
                }

                $db->commit();

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

            } catch (Exception $e) {

                if ($db->inTransaction()) {
                    $db->rollBack();
                }

                $mensajeError = addslashes($e->getMessage());

                echo '<script>
                    swal({
                        type: "error",
                        title: "Error al registrar la cotización",
                        text: "' . $mensajeError . '",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    MOSTRAR COTIZACIÓN / PEDIDO
    =============================================*/
    static public function ctrMostrarCotizacion($item = null, $valor = null) {

        $tabla = "cotizaciones_pedidos";
        return ModeloCotizaciones::mdlMostrarCotizacion($tabla, $item, $valor);
    }

    /*=============================================
    MOSTRAR DETALLE DE COTIZACIÓN / PEDIDO
    =============================================*/
    static public function ctrMostrarDetalleCotizacion($idDocto) {

        $tabla = "detalles_docto";
        return ModeloCotizaciones::mdlMostrarDetalleCotizacion($tabla, (int)$idDocto);
    }
}
