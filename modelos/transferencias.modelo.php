<?php

require_once "conexion.php";

class ModeloTransferencias {

    /*=============================================
    REGISTRAR TRANSFERENCIA
    =============================================*/
    static public function mdlRegistrarTransferencia($tabla, $datos){

        $stmt = Conexion::conectar()->prepare("
            INSERT INTO $tabla(
                id_producto,
                id_sucursal_origen,
                id_sucursal_destino,
                cantidad,
                observacion,
                usuario,
                fecha,
                estado
            ) VALUES (
                :id_producto,
                :id_sucursal_origen,
                :id_sucursal_destino,
                :cantidad,
                :observacion,
                :usuario,
                :fecha,
                :estado
            )
        ");

        $stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
        $stmt->bindParam(":id_sucursal_origen", $datos["id_sucursal_origen"], PDO::PARAM_INT);
        $stmt->bindParam(":id_sucursal_destino", $datos["id_sucursal_destino"], PDO::PARAM_INT);
        $stmt->bindParam(":cantidad", $datos["cantidad"], PDO::PARAM_STR);
        $stmt->bindParam(":observacion", $datos["observacion"], PDO::PARAM_STR);
        $stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_INT);
        $stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_INT);

        return $stmt->execute() ? "ok" : "error";
    }

    /*=============================================
    MOSTRAR TRANSFERENCIAS
    =============================================*/
    static public function mdlMostrarTransferencias($tabla, $item, $valor){

        $conexion = Conexion::conectar();

        if($item !== null){

            $stmt = $conexion->prepare("
                SELECT t.*,
                       p.descripcion AS producto,
                       so.nombre AS sucursal_origen,
                       sd.nombre AS sucursal_destino,
                       u.nombre AS usuario_nombre
                FROM $tabla t
                INNER JOIN productos p ON p.id = t.id_producto
                INNER JOIN sucursales so ON so.id = t.id_sucursal_origen
                INNER JOIN sucursales sd ON sd.id = t.id_sucursal_destino
                INNER JOIN usuarios u ON u.id = t.usuario
                WHERE t.$item = :valor
                ORDER BY t.id DESC
            ");

            $stmt->bindParam(":valor", $valor, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        }else{

            $stmt = $conexion->prepare("
                SELECT t.*,
                       p.descripcion AS producto,
                       so.nombre AS sucursal_origen,
                       sd.nombre AS sucursal_destino,
                       u.nombre AS usuario_nombre
                FROM $tabla t
                INNER JOIN productos p ON p.id = t.id_producto
                INNER JOIN sucursales so ON so.id = t.id_sucursal_origen
                INNER JOIN sucursales sd ON sd.id = t.id_sucursal_destino
                INNER JOIN usuarios u ON u.id = t.usuario
                ORDER BY t.id DESC
            ");

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}