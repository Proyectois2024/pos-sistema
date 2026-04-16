<?php
require_once "conexion.php";

class ModeloGastos {

  /*=============================================
  INSERTAR GASTO
  =============================================*/
  static public function mdlInsertarGasto($tabla, $datos) {

    $db = Conexion::conectar();

    $stmt = $db->prepare("
      INSERT INTO $tabla (
        tipo,
        id_proveedor,
        descripcion,
        monto,
        fecha,
        id_caja,
        usuario,
        creado_por,
        id_sucursal
      )
      VALUES (
        :tipo,
        :id_proveedor,
        :descripcion,
        :monto,
        :fecha,
        :id_caja,
        :usuario,
        :creado_por,
        :id_sucursal
      )
    ");

    $stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);

    if ($datos["id_proveedor"] !== null) {
      $stmt->bindParam(":id_proveedor", $datos["id_proveedor"], PDO::PARAM_INT);
    } else {
      $stmt->bindValue(":id_proveedor", null, PDO::PARAM_NULL);
    }

    $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
    $stmt->bindParam(":monto", $datos["monto"], PDO::PARAM_STR);
    $stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);

    if(isset($datos["id_caja"]) && $datos["id_caja"] !== null){
      $stmt->bindParam(":id_caja", $datos["id_caja"], PDO::PARAM_INT);
    }else{
      $stmt->bindValue(":id_caja", null, PDO::PARAM_NULL);
    }

    if(isset($datos["usuario"]) && $datos["usuario"] !== null){
      $stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_INT);
    }else{
      $stmt->bindValue(":usuario", null, PDO::PARAM_NULL);
    }

    if(isset($datos["creado_por"]) && $datos["creado_por"] !== null){
      $stmt->bindParam(":creado_por", $datos["creado_por"], PDO::PARAM_INT);
    }else{
      $creadoPor = 0;
      $stmt->bindParam(":creado_por", $creadoPor, PDO::PARAM_INT);
    }

    if(isset($datos["id_sucursal"]) && $datos["id_sucursal"] !== null){
      $stmt->bindParam(":id_sucursal", $datos["id_sucursal"], PDO::PARAM_INT);
    }else{
      $stmt->bindValue(":id_sucursal", null, PDO::PARAM_NULL);
    }

    if($stmt->execute()){
      $respuesta = $db->lastInsertId();
    }else{
      $respuesta = "error";
    }

    $stmt = null;
    return $respuesta;
  }

  /*=============================================
  MOSTRAR GASTOS CON FILTROS
  =============================================*/
  static public function mdlMostrarGastos($tabla, $idProveedor = null, $mes = null, $anio = null) {

    $conexion = Conexion::conectar();
    $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

    $where = [];
    $params = [];

    if ($idSucursal > 0) {
      $where[] = "g.id_sucursal = :id_sucursal";
      $params[":id_sucursal"] = $idSucursal;
    }

    if ($idProveedor !== null) {
      $where[] = "g.id_proveedor = :idProveedor";
      $params[":idProveedor"] = $idProveedor;
    }

    if ($mes !== null) {
      $where[] = "MONTH(g.fecha) = :mes";
      $params[":mes"] = $mes;
    }

    if ($anio !== null) {
      $where[] = "YEAR(g.fecha) = :anio";
      $params[":anio"] = $anio;
    }

    $sql = "SELECT g.*, p.nombre as proveedor
            FROM $tabla g
            LEFT JOIN proveedores p ON g.id_proveedor = p.id";

    if (count($where) > 0) {
      $sql .= " WHERE " . implode(" AND ", $where);
    }

    $sql .= " ORDER BY g.fecha DESC, g.id DESC";

    $stmt = $conexion->prepare($sql);

    foreach ($params as $key => $val) {
      if($key === ":id_sucursal" || $key === ":idProveedor" || $key === ":mes" || $key === ":anio"){
        $stmt->bindValue($key, $val, PDO::PARAM_INT);
      }else{
        $stmt->bindValue($key, $val);
      }
    }

    $stmt->execute();
    $respuesta = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = null;
    return $respuesta;
  }

  /*=============================================
  ELIMINAR GASTO
  =============================================*/
  static public function mdlEliminarGasto($tabla, $id) {

    $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);

    $respuesta = $stmt->execute() ? "ok" : "error";

    $stmt = null;
    return $respuesta;
  }

  /*=============================================
  EDITAR GASTO
  =============================================*/
  static public function mdlEditarGasto($tabla, $datos) {

    $stmt = Conexion::conectar()->prepare("
      UPDATE $tabla
      SET descripcion = :descripcion,
          monto = :monto,
          fecha = :fecha
      WHERE id = :id
    ");

    $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
    $stmt->bindParam(":monto", $datos["monto"], PDO::PARAM_STR);
    $stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
    $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

    $respuesta = $stmt->execute() ? "ok" : "error";

    $stmt = null;
    return $respuesta;
  }

  /*=============================================
  MOSTRAR GASTO POR ID
  =============================================*/
  static public function mdlMostrarGastoPorId($tabla, $id) {

    $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

    if($idSucursal > 0){

      $stmt = Conexion::conectar()->prepare("
        SELECT g.*, p.nombre AS proveedor
        FROM $tabla g
        LEFT JOIN proveedores p ON g.id_proveedor = p.id
        WHERE g.id = :id
          AND g.id_sucursal = :id_sucursal
        LIMIT 1
      ");

      $stmt->bindParam(":id", $id, PDO::PARAM_INT);
      $stmt->bindParam(":id_sucursal", $idSucursal, PDO::PARAM_INT);

    }else{

      $stmt = Conexion::conectar()->prepare("
        SELECT g.*, p.nombre AS proveedor
        FROM $tabla g
        LEFT JOIN proveedores p ON g.id_proveedor = p.id
        WHERE g.id = :id
        LIMIT 1
      ");

      $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    }

    $stmt->execute();

    $respuesta = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = null;
    return $respuesta;
  }

  /*=============================================
  BUSCAR MOVIMIENTO DE CAJA DE UN GASTO
  =============================================*/
  static public function mdlMostrarMovimientoCajaPorReferencia($tabla, $referencia, $idReferencia) {

    $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

    if($idSucursal > 0){

      $stmt = Conexion::conectar()->prepare("
        SELECT *
        FROM $tabla
        WHERE referencia = :referencia
          AND id_referencia = :id_referencia
          AND id_sucursal = :id_sucursal
        ORDER BY id DESC
        LIMIT 1
      ");

      $stmt->bindParam(":referencia", $referencia, PDO::PARAM_STR);
      $stmt->bindParam(":id_referencia", $idReferencia, PDO::PARAM_INT);
      $stmt->bindParam(":id_sucursal", $idSucursal, PDO::PARAM_INT);

    }else{

      $stmt = Conexion::conectar()->prepare("
        SELECT *
        FROM $tabla
        WHERE referencia = :referencia
          AND id_referencia = :id_referencia
        ORDER BY id DESC
        LIMIT 1
      ");

      $stmt->bindParam(":referencia", $referencia, PDO::PARAM_STR);
      $stmt->bindParam(":id_referencia", $idReferencia, PDO::PARAM_INT);
    }

    $stmt->execute();

    $respuesta = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = null;
    return $respuesta;
  }

  /*=============================================
  ACTUALIZAR MOVIMIENTO DE CAJA DE GASTO
  =============================================*/
  static public function mdlActualizarMovimientoCajaGasto($tabla, $datos) {

    $stmt = Conexion::conectar()->prepare("
      UPDATE $tabla
      SET descripcion = :descripcion,
          monto = :monto,
          fecha = :fecha
      WHERE id = :id
    ");

    $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
    $stmt->bindParam(":monto", $datos["monto"], PDO::PARAM_STR);
    $stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
    $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

    $respuesta = $stmt->execute() ? "ok" : "error";

    $stmt = null;
    return $respuesta;
  }

  /*=============================================
  ELIMINAR MOVIMIENTO DE CAJA
  =============================================*/
  static public function mdlEliminarMovimientoCaja($tabla, $id) {

    $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);

    $respuesta = $stmt->execute() ? "ok" : "error";

    $stmt = null;
    return $respuesta;
  }

  /*=============================================
SUMAR TOTAL DE GASTOS POR SUCURSAL
=============================================*/
static public function mdlSumaTotalGastos($tabla){

  $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

  if($idSucursal > 0){

    $stmt = Conexion::conectar()->prepare("
      SELECT SUM(monto) as total 
      FROM $tabla 
      WHERE id_sucursal = :id_sucursal
    ");

    $stmt->bindParam(":id_sucursal", $idSucursal, PDO::PARAM_INT);

  }else{

    $stmt = Conexion::conectar()->prepare("
      SELECT SUM(monto) as total 
      FROM $tabla
    ");
  }

  $stmt->execute();

  $respuesta = $stmt->fetch(PDO::FETCH_ASSOC);

  $stmt = null;

  return $respuesta;
}
}