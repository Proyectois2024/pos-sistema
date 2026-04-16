<?php

require_once "conexion.php";

class ModeloCompras {

  /*=============================================
  CREAR COMPRA
  =============================================*/
  static public function mdlCrearCompra($tabla, $datos) {
    $conexion = Conexion::conectar();

    $stmt = $conexion->prepare("
      INSERT INTO $tabla (id_proveedor, id_sucursal, fecha_compra) 
      VALUES (:id_proveedor, :id_sucursal, :fecha_compra)
    ");
    
    $stmt->bindParam(":id_proveedor", $datos["id_proveedor"], PDO::PARAM_INT);
    $stmt->bindParam(":id_sucursal", $datos["id_sucursal"], PDO::PARAM_INT);
    $stmt->bindParam(":fecha_compra", $datos["fecha_compra"], PDO::PARAM_STR);

    if ($stmt->execute()) {
      return $conexion->lastInsertId();
    } else {
      return false;
    }
  }

  /*=============================================
  INSERTAR DETALLE DE COMPRA
  =============================================*/
  static public function mdlInsertarDetalleCompra($tabla, $datos) {
    $stmt = Conexion::conectar()->prepare(
      "INSERT INTO $tabla (id_compra, id_producto, cantidad, unidad, precio_compra, precio_venta)
       VALUES (:id_compra, :id_producto, :cantidad, :unidad, :precio_compra, :precio_venta)"
    );

    $stmt->bindParam(":id_compra", $datos["id_compra"], PDO::PARAM_INT);
    $stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
    $stmt->bindParam(":cantidad", $datos["cantidad"], PDO::PARAM_STR);
    $stmt->bindParam(":unidad", $datos["unidad"], PDO::PARAM_STR);
    $stmt->bindParam(":precio_compra", $datos["precio_compra"], PDO::PARAM_STR);
    $stmt->bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);

    return $stmt->execute();
  }

  /*=============================================
  OBTENER STOCK DE PRODUCTO EN SUCURSAL
  =============================================*/
  static public function mdlObtenerStockSucursal($idProducto, $idSucursal) {
    $stmt = Conexion::conectar()->prepare("
      SELECT *
      FROM stock_sucursal
      WHERE id_producto = :id_producto AND id_sucursal = :id_sucursal
      LIMIT 1
    ");

    $stmt->bindParam(":id_producto", $idProducto, PDO::PARAM_INT);
    $stmt->bindParam(":id_sucursal", $idSucursal, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /*=============================================
  CREAR STOCK INICIAL EN SUCURSAL
  =============================================*/
  static public function mdlCrearStockSucursal($idProducto, $idSucursal, $cantidad) {
    $stmt = Conexion::conectar()->prepare("
      INSERT INTO stock_sucursal (id_producto, id_sucursal, stock, stock_minimo)
      VALUES (:id_producto, :id_sucursal, :stock, 0)
    ");

    $stmt->bindParam(":id_producto", $idProducto, PDO::PARAM_INT);
    $stmt->bindParam(":id_sucursal", $idSucursal, PDO::PARAM_INT);
    $stmt->bindParam(":stock", $cantidad, PDO::PARAM_STR);

    return $stmt->execute() ? "ok" : "error";
  }

  /*=============================================
  ACTUALIZAR STOCK EN SUCURSAL Y PRECIOS GLOBALES
  =============================================*/
  static public function mdlActualizarStockYPrecio($tabla, $id_producto, $id_sucursal, $cantidad, $precio_compra, $precio_venta) {
    $conexion = Conexion::conectar();

    $stockSucursal = self::mdlObtenerStockSucursal($id_producto, $id_sucursal);

    if($stockSucursal){

      $stmtStock = $conexion->prepare("
        UPDATE stock_sucursal
        SET stock = stock + :cantidad
        WHERE id_producto = :id_producto
          AND id_sucursal = :id_sucursal
      ");

      $stmtStock->bindParam(":cantidad", $cantidad, PDO::PARAM_STR);
      $stmtStock->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
      $stmtStock->bindParam(":id_sucursal", $id_sucursal, PDO::PARAM_INT);

      if(!$stmtStock->execute()){
        return false;
      }

    } else {

      $crearStock = self::mdlCrearStockSucursal($id_producto, $id_sucursal, $cantidad);

      if($crearStock != "ok"){
        return false;
      }
    }

    $stmtProducto = $conexion->prepare("
      UPDATE $tabla
      SET precio_compra = :precio_compra,
          precio_venta = :precio_venta
      WHERE id = :id
    ");

    $stmtProducto->bindParam(":precio_compra", $precio_compra, PDO::PARAM_STR);
    $stmtProducto->bindParam(":precio_venta", $precio_venta, PDO::PARAM_STR);
    $stmtProducto->bindParam(":id", $id_producto, PDO::PARAM_INT);

    return $stmtProducto->execute();
  }

  /*=============================================
OBTENER COMPRAS POR PROVEEDOR Y SUCURSAL
=============================================*/
static public function mdlObtenerComprasPorProveedor($id_proveedor) {

  $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

  if($idSucursal > 0){

    $stmt = Conexion::conectar()->prepare("
      SELECT *
      FROM compras
      WHERE id_proveedor = :id
        AND id_sucursal = :id_sucursal
      ORDER BY fecha_compra DESC
    ");

    $stmt->bindParam(":id", $id_proveedor, PDO::PARAM_INT);
    $stmt->bindParam(":id_sucursal", $idSucursal, PDO::PARAM_INT);

  }else{

    $stmt = Conexion::conectar()->prepare("
      SELECT *
      FROM compras
      WHERE id_proveedor = :id
      ORDER BY fecha_compra DESC
    ");

    $stmt->bindParam(":id", $id_proveedor, PDO::PARAM_INT);
  }

  $stmt->execute();

  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

  /*=============================================
  OBTENER DETALLES DE COMPRA
  =============================================*/
  static public function mdlObtenerDetallesCompra($id_compra) {
    $stmt = Conexion::conectar()->prepare("
      SELECT *
      FROM detalle_compra
      WHERE id_compra = :id
    ");

    $stmt->bindParam(":id", $id_compra, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
?>