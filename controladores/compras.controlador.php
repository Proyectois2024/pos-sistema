<?php

class ControladorCompras {

    /*=============================================
    CREAR COMPRA
    =============================================*/
    static public function ctrCrearCompra() {

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return;
        }

        if (isset($_POST["idProveedor"]) && isset($_POST["productos"]) && !empty($_POST["productos"])) {

            $db = Conexion::conectar();

            try {

                $db->beginTransaction();

                $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;
                $usuarioId  = isset($_SESSION["id"]) ? (int)$_SESSION["id"] : 0;

                if ($idSucursal <= 0) {
                    throw new Exception("El usuario no tiene una sucursal asignada");
                }

                $idProveedor = (int)$_POST["idProveedor"];
                $fechaCompra = isset($_POST["fechaCompra"]) && !empty($_POST["fechaCompra"]) ? trim($_POST["fechaCompra"]) : date("Y-m-d");

                if ($idProveedor <= 0) {
                    throw new Exception("Debes seleccionar un proveedor válido");
                }

                $datosCompra = array(
                    "id_proveedor" => $idProveedor,
                    "id_sucursal"  => $idSucursal,
                    "id_usuario"   => $usuarioId,
                    "fecha_compra" => $fechaCompra
                );

                // Insertar encabezado de compra
                $idCompra = ModeloCompras::mdlCrearCompra("compras", $datosCompra);

                if (!$idCompra || $idCompra === "error") {
                    throw new Exception("Error al registrar la cabecera de la compra");
                }

                $productos      = $_POST["productos"];
                $cantidades     = $_POST["cantidades"];
                $unidades       = $_POST["unidades"];
                $preciosCompra  = $_POST["preciosCompra"];
                $preciosVenta   = $_POST["preciosVenta"];

                $hayProductosProcesados = false;

                for ($i = 0; $i < count($productos); $i++) {

                    $idProducto   = (int)$productos[$i];
                    $cantidad     = (float)$cantidades[$i];
                    $unidad       = isset($unidades[$i]) ? trim($unidades[$i]) : "";
                    $precioCompra = (float)$preciosCompra[$i];
                    $precioVenta  = (float)$preciosVenta[$i];

                    if ($idProducto <= 0 || $cantidad <= 0) {
                        continue;
                    }

                    $hayProductosProcesados = true;

                    // 1. Insertar detalle
                    $detalle = array(
                        "id_compra"     => (int)$idCompra,
                        "id_producto"   => $idProducto,
                        "cantidad"      => $cantidad,
                        "unidad"        => $unidad,
                        "precio_compra" => $precioCompra,
                        "precio_venta"  => $precioVenta
                    );

                    $respDetalle = ModeloCompras::mdlInsertarDetalleCompra("detalle_compra", $detalle);

                    if (!$respDetalle || $respDetalle === "error") {
                        throw new Exception("Error al guardar el detalle del producto con ID: " . $idProducto);
                    }

                    // 2. Actualizar stock y precio
                    $respStock = ModeloCompras::mdlActualizarStockYPrecio(
                        "productos",
                        $idProducto,
                        $idSucursal,
                        $cantidad,
                        $precioCompra,
                        $precioVenta
                    );

                    if (!$respStock || $respStock === "error") {
                        throw new Exception("Error al actualizar el inventario del producto con ID: " . $idProducto);
                    }
                }

                if (!$hayProductosProcesados) {
                    throw new Exception("No se proporcionaron productos con cantidades válidas");
                }

                // Confirmar transacción si todo salió bien
                $db->commit();

                echo '<script>
                    swal({
                        type: "success",
                        title: "¡Compra guardada correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "compras";
                        }
                    });
                </script>';

            } catch (Exception $e) {

                // Revertir cambios en caso de fallo
                if ($db->inTransaction()) {
                    $db->rollBack();
                }

                $mensajeError = addslashes($e->getMessage());

                echo '<script>
                    swal({
                        type: "error",
                        title: "Error en la compra",
                        text: "' . $mensajeError . '",
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }

        } else {

            echo '<script>
                swal({
                    type: "warning",
                    title: "Datos incompletos",
                    text: "Por favor agrega al menos un producto antes de guardar.",
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
    }
}
?>
