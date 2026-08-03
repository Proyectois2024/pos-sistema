<?php

class ControladorTransferencias {

    /*=============================================
    REGISTRAR TRANSFERENCIA
    =============================================*/
    static public function ctrRegistrarTransferencia(){

        if(isset($_POST["idProductoTransferencia"])){

            $db = Conexion::conectar();

            try{

                $db->beginTransaction();

                $idProductoOrigen = (int)$_POST["idProductoTransferencia"];
                $idSucursalOrigen = (int)$_POST["idSucursalOrigen"];
                $idSucursalDestino = (int)$_POST["idSucursalDestino"];
                $cantidad = (float)$_POST["cantidadTransferencia"];
                $observacion = isset($_POST["observacionTransferencia"]) ? trim($_POST["observacionTransferencia"]) : "";
                $usuario = isset($_SESSION["id"]) ? (int)$_SESSION["id"] : 0;

                // Validaciones iniciales
                if($idProductoOrigen <= 0 || $idSucursalOrigen <= 0 || $idSucursalDestino <= 0 || $cantidad <= 0 || $usuario <= 0){
                    throw new Exception("Datos inválidos para la transferencia");
                }

                if($idSucursalOrigen === $idSucursalDestino){
                    throw new Exception("La sucursal origen y destino no pueden ser la misma");
                }

                // Obtener y validar producto origen
                $productoOrigen = ModeloProductos::mdlMostrarProductoPorId("productos", $idProductoOrigen);

                if(!$productoOrigen || !isset($productoOrigen["id"])){
                    throw new Exception("No se encontró el producto origen");
                }

                if((int)$productoOrigen["id_sucursal"] !== $idSucursalOrigen){
                    throw new Exception("El producto seleccionado no pertenece a la sucursal origen");
                }

                // Verificar stock disponible en origen
                $stockOrigen = ModeloProductos::mdlObtenerStockSucursal($idProductoOrigen, $idSucursalOrigen);

                if(!$stockOrigen || !isset($stockOrigen["stock"])){
                    throw new Exception("El producto no existe en la sucursal origen");
                }

                $stockActualOrigen = (float)$stockOrigen["stock"];

                if($stockActualOrigen < $cantidad){
                    throw new Exception("La sucursal origen no tiene stock suficiente");
                }

                // Descontar stock origen
                $nuevoStockOrigen = $stockActualOrigen - $cantidad;
                $respOrigen = ModeloProductos::mdlActualizarStockSucursal($idProductoOrigen, $idSucursalOrigen, $nuevoStockOrigen);

                if($respOrigen !== "ok"){
                    throw new Exception("No se pudo descontar stock en la sucursal origen");
                }

                // Buscar o clonar producto en destino
                $productoDestino = ModeloProductos::mdlMostrarProductoPorCodigo(
                    "productos",
                    $productoOrigen["codigo"],
                    $idSucursalDestino
                );

                if(!$productoDestino){

                    $nuevoIdProductoDestino = ModeloProductos::mdlClonarProductoASucursal(
                        "productos",
                        $productoOrigen,
                        $idSucursalDestino
                    );

                    if(!$nuevoIdProductoDestino){
                        throw new Exception("No se pudo crear el producto en la sucursal destino");
                    }

                    $respDestino = ModeloProductos::mdlCrearStockSucursal(
                        $nuevoIdProductoDestino,
                        $idSucursalDestino,
                        $cantidad,
                        isset($stockOrigen["stock_minimo"]) ? $stockOrigen["stock_minimo"] : 0
                    );

                    if($respDestino !== "ok"){
                        throw new Exception("No se pudo crear stock en la sucursal destino");
                    }

                    $idProductoDestino = $nuevoIdProductoDestino;

                } else {

                    $idProductoDestino = (int)$productoDestino["id"];
                    $stockDestino = ModeloProductos::mdlObtenerStockSucursal($idProductoDestino, $idSucursalDestino);

                    if($stockDestino && isset($stockDestino["stock"])){
                        $nuevoStockDestino = (float)$stockDestino["stock"] + $cantidad;
                        $respDestino = ModeloProductos::mdlActualizarStockSucursal($idProductoDestino, $idSucursalDestino, $nuevoStockDestino);
                    }else{
                        $respDestino = ModeloProductos::mdlCrearStockSucursal(
                            $idProductoDestino,
                            $idSucursalDestino,
                            $cantidad,
                            isset($stockOrigen["stock_minimo"]) ? $stockOrigen["stock_minimo"] : 0
                        );
                    }

                    if($respDestino !== "ok"){
                        throw new Exception("No se pudo sumar stock en la sucursal destino");
                    }
                }

                // Guardar registro de transferencia
                $datos = array(
                    "id_producto" => $idProductoOrigen,
                    "id_producto_destino" => $idProductoDestino,
                    "id_sucursal_origen" => $idSucursalOrigen,
                    "id_sucursal_destino" => $idSucursalDestino,
                    "cantidad" => $cantidad,
                    "observacion" => $observacion,
                    "usuario" => $usuario,
                    "fecha" => app_now(),
                    "estado" => 1
                );

                $respuesta = ModeloTransferencias::mdlRegistrarTransferencia("transferencias", $datos);

                if($respuesta !== "ok"){
                    throw new Exception("No se pudo guardar la transferencia");
                }

                $db->commit();

                echo '<script>
                    swal({
                        type: "success",
                        title: "Transferencia realizada correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "transferencias";
                        }
                    });
                </script>';

            }catch(Exception $e){

                if($db->inTransaction()){
                    $db->rollBack();
                }

                $mensajeError = addslashes($e->getMessage());

                echo '<script>
                    swal({
                        type: "error",
                        title: "Error al transferir",
                        text: "'.$mensajeError.'",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    MOSTRAR TRANSFERENCIAS
    =============================================*/
    static public function ctrMostrarTransferencias($item, $valor){

        return ModeloTransferencias::mdlMostrarTransferencias("transferencias", $item, $valor);
    }
}
