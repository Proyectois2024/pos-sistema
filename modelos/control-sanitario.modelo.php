<?php

require_once "conexion.php";

class ModeloControlSanitario{

    /*=============================================
    CREAR REGISTRO SANITARIO
    =============================================*/

    static public function mdlIngresarSanitario($tabla, $datos){

        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla
            (id_animal, id_venta, producto, tipo, dosis, fecha_aplicacion,
             proxima_aplicacion, observaciones, fecha_registro)
            VALUES
            (:id_animal, :id_venta, :producto, :tipo, :dosis,
             :fecha_aplicacion, :proxima_aplicacion, :observaciones, NOW())");

        $stmt->bindParam(":id_animal", $datos["id_animal"], PDO::PARAM_INT);
        $stmt->bindParam(":id_venta", $datos["id_venta"], PDO::PARAM_INT);
        $stmt->bindParam(":producto", $datos["producto"], PDO::PARAM_STR);
        $stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
        $stmt->bindParam(":dosis", $datos["dosis"], PDO::PARAM_STR);
        $stmt->bindParam(":fecha_aplicacion", $datos["fecha_aplicacion"], PDO::PARAM_STR);
        $stmt->bindParam(":proxima_aplicacion", $datos["proxima_aplicacion"], PDO::PARAM_STR);
        $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

        if($stmt->execute()){
            return "ok";
        }else{
            return "error";
        }

        $stmt = null;
    }


    /*=============================================
    MOSTRAR REGISTROS
    =============================================*/

    static public function mdlMostrarSanitario($tabla){

        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id DESC");

        $stmt->execute();

        return $stmt->fetchAll();

        $stmt = null;
    }

}