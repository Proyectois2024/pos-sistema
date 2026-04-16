<?php

require_once "conexion.php";

class ModeloProductos {

  /*=============================================
MOSTRAR PRODUCTOS POR SUCURSAL
=============================================*/
static public function mdlMostrarProductos($tabla, $item, $valor, $orden) {

  $conexion = Conexion::conectar();
  $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;
  $ordenSql = !empty($orden) ? $orden : "id";

  if ($item != null) {

    $stmt = $conexion->prepare("
      SELECT 
        p.*,
        c.categoria,
        COALESCE(ss.stock, 0) AS stock,
        COALESCE(ss.stock_minimo, 0) AS stock_minimo
      FROM productos p
      LEFT JOIN categorias c ON p.id_categoria = c.id
      LEFT JOIN stock_sucursal ss 
        ON ss.id_producto = p.id AND ss.id_sucursal = :id_sucursal
      WHERE p.$item = :valor
        AND p.id_sucursal = :id_sucursal
      ORDER BY p.id DESC
    ");

    $stmt->bindParam(":id_sucursal", $idSucursal, PDO::PARAM_INT);
    $stmt->bindParam(":valor", $valor, PDO::PARAM_STR);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);

  } else {

    $stmt = $conexion->prepare("
      SELECT 
        p.*,
        c.categoria,
        COALESCE(ss.stock, 0) AS stock,
        COALESCE(ss.stock_minimo, 0) AS stock_minimo
      FROM productos p
      LEFT JOIN categorias c ON p.id_categoria = c.id
      LEFT JOIN stock_sucursal ss 
        ON ss.id_producto = p.id AND ss.id_sucursal = :id_sucursal
      WHERE p.id_sucursal = :id_sucursal
      ORDER BY p.$ordenSql DESC
    ");

    $stmt->bindParam(":id_sucursal", $idSucursal, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}

  /*=============================================
  REGISTRO DE PRODUCTO
  Guarda producto global y luego stock en sucursal
  =============================================*/
  static public function mdlIngresarProducto($tabla, $datos){

    $conexion = Conexion::conectar();

    $stmt = $conexion->prepare("
  INSERT INTO $tabla 
  (id_categoria, codigo, descripcion, imagen, stock, stock_minimo, precio_compra, precio_venta, fecha_vencimiento, tipo_sanitario, id_sucursal) 
  VALUES 
  (:id_categoria, :codigo, :descripcion, :imagen, 0, 0, :precio_compra, :precio_venta, :fecha_vencimiento, :tipo_sanitario, :id_sucursal)
");

    $stmt->bindParam(":id_categoria", $datos["id_categoria"], PDO::PARAM_INT);
    $stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
    $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
    $stmt->bindParam(":imagen", $datos["imagen"], PDO::PARAM_STR);
    $stmt->bindParam(":precio_compra", $datos["precio_compra"], PDO::PARAM_STR);
    $stmt->bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);
    $stmt->bindParam(":fecha_vencimiento", $datos["fecha_vencimiento"], PDO::PARAM_STR);
    $stmt->bindParam(":tipo_sanitario", $datos["tipo_sanitario"], PDO::PARAM_STR);
    $stmt->bindParam(":id_sucursal", $datos["id_sucursal"], PDO::PARAM_INT);

    if ($stmt->execute()) {

      $idProducto = $conexion->lastInsertId();
      $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

      if($idSucursal > 0){

        $respStock = self::mdlCrearStockSucursal(
          $idProducto,
          $idSucursal,
          $datos["stock"],
          $datos["stock_minimo"]
        );

        if($respStock != "ok"){
          return "error";
        }
      }

      return "ok";

    } else {
      return "error";
    }
  }

  /*=============================================
EDITAR PRODUCTO
Edita solo el producto de la sucursal actual
=============================================*/
static public function mdlEditarProducto($tabla, $datos){

  $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

  $stmt = Conexion::conectar()->prepare("
    UPDATE $tabla SET 
      id_categoria = :id_categoria, 
      descripcion = :descripcion, 
      imagen = :imagen, 
      precio_compra = :precio_compra, 
      precio_venta = :precio_venta, 
      fecha_vencimiento = :fecha_vencimiento
    WHERE codigo = :codigo
      AND id_sucursal = :id_sucursal
  ");

  $stmt->bindParam(":id_categoria", $datos["id_categoria"], PDO::PARAM_INT);
  $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
  $stmt->bindParam(":imagen", $datos["imagen"], PDO::PARAM_STR);
  $stmt->bindParam(":precio_compra", $datos["precio_compra"], PDO::PARAM_STR);
  $stmt->bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);
  $stmt->bindParam(":fecha_vencimiento", $datos["fecha_vencimiento"], PDO::PARAM_STR);
  $stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
  $stmt->bindParam(":id_sucursal", $idSucursal, PDO::PARAM_INT);

  if($stmt->execute()){

    $producto = self::mdlMostrarProductoPorCodigo($tabla, $datos["codigo"], $idSucursal);

    if(!$producto || !isset($producto["id"])){
      return "error";
    }

    $idProducto = (int)$producto["id"];

    if($idSucursal > 0){

      $stockSucursal = self::mdlObtenerStockSucursal($idProducto, $idSucursal);

      if($stockSucursal){
        $resp1 = self::mdlActualizarStockSucursal($idProducto, $idSucursal, $datos["stock"]);
        $resp2 = self::mdlActualizarStockMinimoSucursal($idProducto, $idSucursal, $datos["stock_minimo"]);
      }else{
        $resp1 = self::mdlCrearStockSucursal($idProducto, $idSucursal, $datos["stock"], $datos["stock_minimo"]);
        $resp2 = "ok";
      }

      if($resp1 != "ok" || $resp2 != "ok"){
        return "error";
      }
    }

    return "ok";

  }else{
    return "error";
  }
}

/*=============================================
MOSTRAR PRODUCTO POR CODIGO
Si se manda sucursal, filtra por sucursal
=============================================*/
static public function mdlMostrarProductoPorCodigo($tabla, $codigo, $idSucursal = null){

  if($idSucursal !== null){

    $stmt = Conexion::conectar()->prepare("
      SELECT *
      FROM $tabla
      WHERE codigo = :codigo
        AND id_sucursal = :id_sucursal
      LIMIT 1
    ");

    $stmt->bindParam(":codigo", $codigo, PDO::PARAM_STR);
    $stmt->bindParam(":id_sucursal", $idSucursal, PDO::PARAM_INT);

  } else {

    $stmt = Conexion::conectar()->prepare("
      SELECT *
      FROM $tabla
      WHERE codigo = :codigo
      LIMIT 1
    ");

    $stmt->bindParam(":codigo", $codigo, PDO::PARAM_STR);
  }

  $stmt->execute();
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

/*=============================================
MOSTRAR PRODUCTO POR ID
=============================================*/
static public function mdlMostrarProductoPorId($tabla, $idProducto){

  $stmt = Conexion::conectar()->prepare("
    SELECT *
    FROM $tabla
    WHERE id = :id
    LIMIT 1
  ");

  $stmt->bindParam(":id", $idProducto, PDO::PARAM_INT);
  $stmt->execute();

  return $stmt->fetch(PDO::FETCH_ASSOC);
}

/*=============================================
CLONAR PRODUCTO A OTRA SUCURSAL
=============================================*/
static public function mdlClonarProductoASucursal($tabla, $productoOrigen, $idSucursalDestino){

  $conexion = Conexion::conectar();

  $stmt = $conexion->prepare("
    INSERT INTO $tabla
    (id_categoria, codigo, descripcion, imagen, stock, stock_minimo, precio_compra, precio_venta, fecha_vencimiento, tipo_sanitario, id_sucursal)
    VALUES
    (:id_categoria, :codigo, :descripcion, :imagen, 0, 0, :precio_compra, :precio_venta, :fecha_vencimiento, :tipo_sanitario, :id_sucursal)
  ");

  $stmt->bindParam(":id_categoria", $productoOrigen["id_categoria"], PDO::PARAM_INT);
  $stmt->bindParam(":codigo", $productoOrigen["codigo"], PDO::PARAM_STR);
  $stmt->bindParam(":descripcion", $productoOrigen["descripcion"], PDO::PARAM_STR);
  $stmt->bindParam(":imagen", $productoOrigen["imagen"], PDO::PARAM_STR);
  $stmt->bindParam(":precio_compra", $productoOrigen["precio_compra"], PDO::PARAM_STR);
  $stmt->bindParam(":precio_venta", $productoOrigen["precio_venta"], PDO::PARAM_STR);
  $stmt->bindParam(":fecha_vencimiento", $productoOrigen["fecha_vencimiento"], PDO::PARAM_STR);
  $stmt->bindParam(":tipo_sanitario", $productoOrigen["tipo_sanitario"], PDO::PARAM_STR);
  $stmt->bindParam(":id_sucursal", $idSucursalDestino, PDO::PARAM_INT);

  if($stmt->execute()){
    return $conexion->lastInsertId();
  }

  return false;
}

/*=============================================
BORRAR PRODUCTO
Solo borra el producto de la sucursal actual
=============================================*/
static public function mdlEliminarProducto($tabla, $datos) {

  $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

  $stmt = Conexion::conectar()->prepare("
    DELETE FROM $tabla
    WHERE id = :id
      AND id_sucursal = :id_sucursal
  ");

  $stmt->bindParam(":id", $datos, PDO::PARAM_INT);
  $stmt->bindParam(":id_sucursal", $idSucursal, PDO::PARAM_INT);

  return $stmt->execute() ? "ok" : "error";
}
  /*=============================================
  OBTENER STOCK DE SUCURSAL
  =============================================*/
  static public function mdlObtenerStockSucursal($idProducto, $idSucursal){

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
  CREAR STOCK EN SUCURSAL
  =============================================*/
  static public function mdlCrearStockSucursal($idProducto, $idSucursal, $stock, $stockMinimo){

    $stmt = Conexion::conectar()->prepare("
      INSERT INTO stock_sucursal(id_producto, id_sucursal, stock, stock_minimo)
      VALUES (:id_producto, :id_sucursal, :stock, :stock_minimo)
    ");

    $stmt->bindParam(":id_producto", $idProducto, PDO::PARAM_INT);
    $stmt->bindParam(":id_sucursal", $idSucursal, PDO::PARAM_INT);
    $stmt->bindParam(":stock", $stock, PDO::PARAM_STR);
    $stmt->bindParam(":stock_minimo", $stockMinimo, PDO::PARAM_STR);

    return $stmt->execute() ? "ok" : "error";
  }

  /*=============================================
  ACTUALIZAR STOCK EN SUCURSAL
  =============================================*/
  static public function mdlActualizarStockSucursal($idProducto, $idSucursal, $stockNuevo){

    $stmt = Conexion::conectar()->prepare("
      UPDATE stock_sucursal
      SET stock = :stock
      WHERE id_producto = :id_producto
        AND id_sucursal = :id_sucursal
    ");

    $stmt->bindParam(":stock", $stockNuevo, PDO::PARAM_STR);
    $stmt->bindParam(":id_producto", $idProducto, PDO::PARAM_INT);
    $stmt->bindParam(":id_sucursal", $idSucursal, PDO::PARAM_INT);

    return $stmt->execute() ? "ok" : "error";
  }

  /*=============================================
  ACTUALIZAR STOCK MÍNIMO EN SUCURSAL
  =============================================*/
  static public function mdlActualizarStockMinimoSucursal($idProducto, $idSucursal, $stockMinimo){

    $stmt = Conexion::conectar()->prepare("
      UPDATE stock_sucursal
      SET stock_minimo = :stock_minimo
      WHERE id_producto = :id_producto
        AND id_sucursal = :id_sucursal
    ");

    $stmt->bindParam(":stock_minimo", $stockMinimo, PDO::PARAM_STR);
    $stmt->bindParam(":id_producto", $idProducto, PDO::PARAM_INT);
    $stmt->bindParam(":id_sucursal", $idSucursal, PDO::PARAM_INT);

    return $stmt->execute() ? "ok" : "error";
  }

  /*=============================================
  ACTUALIZAR PRODUCTO
  Se deja para campos globales de productos
  =============================================*/
  static public function mdlActualizarProducto($tabla, $item1, $valor1, $valor) {

    $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE id = :id");
    $stmt->bindParam(":" . $item1, $valor1, PDO::PARAM_STR);
    $stmt->bindParam(":id", $valor, PDO::PARAM_STR);

    return $stmt->execute() ? "ok" : "error";
  }

  /*=============================================
  MOSTRAR SUMA VENTAS
  =============================================*/
  static public function mdlMostrarSumaVentas($tabla) {

    $stmt = Conexion::conectar()->prepare("SELECT SUM(ventas) as total FROM $tabla");
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /*=============================================
  CONTAR PRODUCTOS CON STOCK BAJO EN SUCURSAL
  =============================================*/
  static public function mdlMostrarProductosStockBajo($tabla, $limite){

    $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

    $stmt = Conexion::conectar()->prepare("
      SELECT COUNT(*) as total
      FROM stock_sucursal
      WHERE id_sucursal = :id_sucursal
        AND stock <= :limite
    ");

    $stmt->bindParam(":id_sucursal", $idSucursal, PDO::PARAM_INT);
    $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /*=============================================
CONTAR PRODUCTOS POR SUCURSAL
=============================================*/
static public function mdlContarProductosPorSucursal($tabla){

  $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

  $stmt = Conexion::conectar()->prepare("
    SELECT COUNT(*) as total
    FROM $tabla
    WHERE id_sucursal = :id_sucursal
  ");

  $stmt->bindParam(":id_sucursal", $idSucursal, PDO::PARAM_INT);
  $stmt->execute();

  return $stmt->fetch(PDO::FETCH_ASSOC);
}

}