<?php

class ControladorGastos {

  /*=============================================
  CREAR GASTO
  =============================================*/
  static public function ctrCrearGasto() {

    if (isset($_POST["descripcionGasto"])) {

      $db = Conexion::conectar();

      try{

        $db->beginTransaction();

        $usuarioId = isset($_SESSION["id"]) ? (int)$_SESSION["id"] : 0;
        $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

        $descripcion = isset($_POST["descripcionGasto"]) ? trim($_POST["descripcionGasto"]) : "";
        $monto = isset($_POST["monto"]) ? (float)$_POST["monto"] : 0;
        $fecha = isset($_POST["fechaGasto"]) && trim($_POST["fechaGasto"]) !== "" ? trim($_POST["fechaGasto"]) : app_now("Y-m-d");
        $tipo = isset($_POST["tipo"]) && trim($_POST["tipo"]) !== "" ? trim($_POST["tipo"]) : "otro";

        if($usuarioId <= 0 || $idSucursal <= 0){
          throw new Exception("El usuario no tiene sucursal asignada");
        }

        if($descripcion === ""){
          throw new Exception("La descripción del gasto es obligatoria");
        }

        if($monto <= 0){
          throw new Exception("El monto del gasto debe ser mayor a cero");
        }

        $cajaAbierta = ControladorCaja::ctrObtenerCajaAbierta($idSucursal);

        if(!$cajaAbierta || !is_array($cajaAbierta)){
          throw new Exception("Debes tener una caja abierta para registrar un gasto");
        }

        if(!isset($cajaAbierta["id_sucursal"]) || (int)$cajaAbierta["id_sucursal"] !== $idSucursal){
          throw new Exception("La caja abierta no pertenece a tu sucursal");
        }

        $idProveedor = ($tipo === "proveedor" && !empty($_POST["idProveedor"]))
          ? (int)$_POST["idProveedor"]
          : null;

        $datos = array(
          "tipo"         => $tipo,
          "id_proveedor" => $idProveedor,
          "descripcion"  => $descripcion,
          "monto"        => $monto,
          "fecha"        => $fecha,
          "id_caja"      => (int)$cajaAbierta["id"],
          "id_sucursal"  => $idSucursal,
          "usuario"      => $usuarioId,
          "creado_por"   => $usuarioId
        );

        $respuesta = ModeloGastos::mdlInsertarGasto("gastos", $datos);

        if(!$respuesta || $respuesta === "error"){
          throw new Exception("No se pudo registrar el gasto");
        }

        $idGasto = (int)$respuesta;

        $respCaja = ControladorCaja::ctrRegistrarMovimientoAutomatico(
          "gasto",
          "Gasto: ".$descripcion,
          $monto,
          "gasto",
          $idGasto
        );

        if($respCaja === "sin_caja"){
          throw new Exception("No hay una caja abierta para registrar el egreso");
        }

        if($respCaja !== "ok"){
          throw new Exception("No se pudo registrar el movimiento de caja del gasto");
        }

        $db->commit();

        echo '<script>
          Swal.fire({
            icon: "success",
            title: "¡Gasto registrado!",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
          }).then(function(result){
            if(result.value){ window.location = "gastos"; }
          });
        </script>';

      }catch(Exception $e){

        if($db->inTransaction()){
          $db->rollBack();
        }

        $mensajeError = addslashes($e->getMessage());

        echo '<script>
          Swal.fire({
            icon: "error",
            title: "Error al registrar el gasto",
            text: "'.$mensajeError.'",
            confirmButtonText: "Cerrar"
          });
        </script>';
      }
    }
  }

  /*=============================================
  EDITAR GASTO
  =============================================*/
  static public function ctrEditarGasto() {

    if (isset($_POST["idGastoEditar"])) {

      $db = Conexion::conectar();

      try{

        $db->beginTransaction();

        $idGasto = (int)$_POST["idGastoEditar"];
        $descripcion = isset($_POST["descripcionGastoEditar"]) ? trim($_POST["descripcionGastoEditar"]) : "";
        $monto = isset($_POST["montoEditar"]) ? (float)$_POST["montoEditar"] : 0;
        $fecha = isset($_POST["fechaGastoEditar"]) ? trim($_POST["fechaGastoEditar"]) : "";
        $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

        if($idSucursal <= 0){
          throw new Exception("El usuario no tiene sucursal asignada");
        }

        if($idGasto <= 0){
          throw new Exception("Gasto inválido");
        }

        if($descripcion === ""){
          throw new Exception("La descripción del gasto es obligatoria");
        }

        if($monto <= 0){
          throw new Exception("El monto del gasto debe ser mayor a cero");
        }

        if($fecha === ""){
          throw new Exception("La fecha del gasto es obligatoria");
        }

        $gastoActual = ModeloGastos::mdlMostrarGastoPorId("gastos", $idGasto);

        if(!$gastoActual || !is_array($gastoActual)){
          throw new Exception("El gasto no existe");
        }

        if(!isset($gastoActual["id_sucursal"]) || (int)$gastoActual["id_sucursal"] !== $idSucursal){
          throw new Exception("No puedes editar gastos de otra sucursal");
        }

        $datos = array(
          "id"          => $idGasto,
          "descripcion" => $descripcion,
          "monto"       => $monto,
          "fecha"       => $fecha
        );

        $respuesta = ModeloGastos::mdlEditarGasto("gastos", $datos);

        if ($respuesta !== "ok") {
          throw new Exception("No se pudo actualizar el gasto");
        }

        $movimientoCaja = ModeloGastos::mdlMostrarMovimientoCajaPorReferencia(
          "movimientos_caja",
          "gasto",
          $idGasto
        );

        if($movimientoCaja && is_array($movimientoCaja)){

          $respMovimiento = ModeloGastos::mdlActualizarMovimientoCajaGasto(
            "movimientos_caja",
            array(
              "id" => (int)$movimientoCaja["id"],
              "descripcion" => "Gasto: ".$descripcion,
              "monto" => $monto,
              "fecha" => $fecha." 00:00:00"
            )
          );

          if($respMovimiento !== "ok"){
            throw new Exception("No se pudo actualizar el movimiento de caja del gasto");
          }
        }

        $db->commit();

        echo '<script>
          Swal.fire({
            icon: "success",
            title: "¡Gasto actualizado!",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
          }).then(function(result){
            if(result.value){ window.location = "gastos"; }
          });
        </script>';

      }catch(Exception $e){

        if($db->inTransaction()){
          $db->rollBack();
        }

        $mensajeError = addslashes($e->getMessage());

        echo '<script>
          Swal.fire({
            icon: "error",
            title: "Error al actualizar el gasto",
            text: "'.$mensajeError.'",
            confirmButtonText: "Cerrar"
          });
        </script>';
      }
    }
  }

  /*=============================================
  MOSTRAR GASTOS CON FILTROS
  =============================================*/
  static public function ctrMostrarGastos($idProveedor = null, $mes = null, $anio = null) {
    return ModeloGastos::mdlMostrarGastos("gastos", $idProveedor, $mes, $anio);
  }

  /*=============================================
  MOSTRAR GASTO POR ID
  =============================================*/
  static public function ctrMostrarGastoPorId($id) {
    return ModeloGastos::mdlMostrarGastoPorId("gastos", $id);
  }

  /*=============================================
  ELIMINAR GASTO
  =============================================*/
  static public function ctrEliminarGasto($id) {

    $db = Conexion::conectar();

    try{

      $db->beginTransaction();

      $idGasto = (int)$id;
      $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

      if($idSucursal <= 0){
        throw new Exception("El usuario no tiene sucursal asignada");
      }

      if($idGasto <= 0){
        throw new Exception("Gasto inválido");
      }

      $gastoActual = ModeloGastos::mdlMostrarGastoPorId("gastos", $idGasto);

      if(!$gastoActual || !is_array($gastoActual)){
        throw new Exception("El gasto no existe");
      }

      if(!isset($gastoActual["id_sucursal"]) || (int)$gastoActual["id_sucursal"] !== $idSucursal){
        throw new Exception("No puedes eliminar gastos de otra sucursal");
      }

      $movimientoCaja = ModeloGastos::mdlMostrarMovimientoCajaPorReferencia(
        "movimientos_caja",
        "gasto",
        $idGasto
      );

      if($movimientoCaja && is_array($movimientoCaja)){
        $respEliminarMovimiento = ModeloGastos::mdlEliminarMovimientoCaja("movimientos_caja", (int)$movimientoCaja["id"]);
        if($respEliminarMovimiento !== "ok"){
          throw new Exception("No se pudo eliminar el movimiento de caja del gasto");
        }
      }

      $respuesta = ModeloGastos::mdlEliminarGasto("gastos", $idGasto);

      if($respuesta !== "ok"){
        throw new Exception("No se pudo eliminar el gasto");
      }

      $db->commit();
      return "ok";

    }catch(Exception $e){

      if($db->inTransaction()){
        $db->rollBack();
      }

      return "error";
    }
  }
  static public function ctrSumaTotalGastos(){

  $tabla = "gastos";
  return ModeloGastos::mdlSumaTotalGastos($tabla);
}
}