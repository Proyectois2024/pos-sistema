<?php

require_once "conexion.php";

class ModeloProveedores {

  /*=============================================
  CREAR PROVEEDOR
  =============================================*/
  static public function mdlIngresarProveedor($tabla, $datos) {

  $stmt = Conexion::conectar()->prepare(
    "INSERT INTO $tabla(nombre, empresa, email, telefono, direccion) 
     VALUES (:nombre, :empresa, :email, :telefono, :direccion)"
  );

  $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
  $stmt->bindParam(":empresa", $datos["empresa"], PDO::PARAM_STR); // usa PARAM_STR si es texto
  $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
  $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
  $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);

  if ($stmt->execute()) {
    return "ok";
  } else {
    return "error";
  }

  $stmt->close();
  $stmt = null;
}


  /*=============================================
  MOSTRAR PROVEEDORES
  =============================================*/
  static public function mdlMostrarProveedores($tabla, $item, $valor) {

    if ($item != null) {

      $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");
      $stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
      $stmt->execute();

      return $stmt->fetch();

    } else {

      $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");
      $stmt->execute();

      return $stmt->fetchAll();
    }

    $stmt->close();
    $stmt = null;
  }

  /*=============================================
  EDITAR PROVEEDOR
  =============================================*/
  static public function mdlEditarProveedor($tabla, $datos) { 

    $stmt = Conexion::conectar()->prepare( "UPDATE $tabla SET nombre = :nombre, empresa = :empresa, email = :email, telefono = :telefono, direccion = :direccion WHERE id = :id" ); 

    $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT); 
    $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR); 
    $stmt->bindParam(":empresa", $datos["empresa"], PDO::PARAM_STR); 
    $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR); 
    $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR); 
    $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR); 

    if ($stmt->execute()) { return "ok"; } else { return "error"; } 
    $stmt = null; 

  }

  /*=============================================
  ELIMINAR PROVEEDOR
  =============================================*/
  static public function mdlEliminarProveedor($tabla, $datos) {

    $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
    $stmt->bindParam(":id", $datos, PDO::PARAM_INT);

    if ($stmt->execute()) {
      return "ok";
    } else {
      return "error";
    }

    $stmt->close();
    $stmt = null;
  }

  /*=============================================
  ACTUALIZAR CAMPO PROVEEDOR (opcional)
  =============================================*/
  static public function mdlActualizarProveedor($tabla, $item1, $valor1, $valor) {

    $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE id = :id");
    $stmt->bindParam(":".$item1, $valor1, PDO::PARAM_STR);
    $stmt->bindParam(":id", $valor, PDO::PARAM_STR);

    if ($stmt->execute()) {
      return "ok";
    } else {
      return "error";
    }

    $stmt->close();
    $stmt = null;
  }
}

?>
