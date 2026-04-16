<?php
require_once "conexion.php";

class ModeloCotizaciones {

    static public function mdlGuardarEncabezado($tabla, $datos) {

        $db = Conexion::conectar();

        $stmt = $db->prepare("
            INSERT INTO $tabla(codigo_docto, tipo, id_cliente, fecha, total)
            VALUES (:codigo, :tipo, :cliente, CURDATE(), :total)
        ");
        
        $stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
        $stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
        $stmt->bindParam(":cliente", $datos["id_cliente"], PDO::PARAM_INT);
        $stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);

        if($stmt->execute()){
            return $db->lastInsertId();
        } else {
            return "error";
        }

        $stmt = null;
    }

 static public function mdlGuardarDetalle($tabla, $datos) {

    $stmt = Conexion::conectar()->prepare("
        INSERT INTO $tabla(
            id_docto,
            id_producto,
            descripcion_item,
            cantidad,
            unidad_medida,
            precio_unitario,
            subtotal
        )
        VALUES (
            :id_docto,
            :id_producto,
            :desc,
            :cantidad,
            :unidad,
            :precio,
            :subtotal
        )
    ");
    
    $stmt->bindParam(":id_docto", $datos["id_docto"], PDO::PARAM_INT);
    $stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
    $stmt->bindParam(":desc", $datos["desc"], PDO::PARAM_STR);
    $stmt->bindParam(":cantidad", $datos["cantidad"], PDO::PARAM_STR);
    $stmt->bindParam(":unidad", $datos["unidad"], PDO::PARAM_STR);
    $stmt->bindParam(":precio", $datos["precio"], PDO::PARAM_STR);
    $stmt->bindParam(":subtotal", $datos["subtotal"], PDO::PARAM_STR);

    if($stmt->execute()){
        return "ok";
    } else {
        return "error";
    }

    $stmt = null;
}

    /*=============================================
MOSTRAR ENCABEZADO DE COTIZACION
=============================================*/
static public function mdlMostrarCotizacion($tabla, $item, $valor){

    $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :valor LIMIT 1");

    if($item == "id"){
        $stmt->bindParam(":valor", $valor, PDO::PARAM_INT);
    }else{
        $stmt->bindParam(":valor", $valor, PDO::PARAM_STR);
    }

    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = null;
}

/*=============================================
MOSTRAR DETALLE DE COTIZACION
=============================================*/
static public function mdlMostrarDetalleCotizacion($tabla, $idDocto){

    $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE id_docto = :id_docto ORDER BY id ASC");
    $stmt->bindParam(":id_docto", $idDocto, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = null;
}

static public function mdlActualizarEstadoCotizacion($tabla, $idCotizacion, $estado){

    $stmt = Conexion::conectar()->prepare("
        UPDATE $tabla
        SET estado = :estado
        WHERE id = :id
    ");

    $stmt->bindParam(":estado", $estado, PDO::PARAM_INT);
    $stmt->bindParam(":id", $idCotizacion, PDO::PARAM_INT);

    if($stmt->execute()){
        return "ok";
    }else{
        return "error";
    }

    $stmt = null;
}
}
