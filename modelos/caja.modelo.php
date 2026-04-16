<?php

require_once "conexion.php";

class ModeloCaja{

    /*=============================================
    MOSTRAR CAJA
    =============================================*/
    static public function mdlMostrarCaja($tabla, $item, $valor){

        $columnasPermitidas = array("id", "usuario", "estado", "id_sucursal");

        if($item === null){

            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id DESC");
            $stmt->execute();
            $respuesta = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = null;
            return $respuesta;
        }

        if(!in_array($item, $columnasPermitidas)){
            return array();
        }

        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :valor ORDER BY id DESC LIMIT 1");

        if($item === "id" || $item === "usuario" || $item === "estado" || $item === "id_sucursal"){
            $stmt->bindParam(":valor", $valor, PDO::PARAM_INT);
        }else{
            $stmt->bindParam(":valor", $valor, PDO::PARAM_STR);
        }

        $stmt->execute();
        $respuesta = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = null;
        return $respuesta;
    }

    /*=============================================
    OBTENER CAJA ABIERTA POR SUCURSAL
    =============================================*/
    static public function mdlObtenerCajaAbierta($tabla, $idSucursal){

        $stmt = Conexion::conectar()->prepare("
            SELECT *
            FROM $tabla
            WHERE id_sucursal = :id_sucursal
              AND estado = 1
            ORDER BY id DESC
            LIMIT 1
        ");

        $stmt->bindParam(":id_sucursal", $idSucursal, PDO::PARAM_INT);
        $stmt->execute();
        $respuesta = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = null;
        return $respuesta;
    }

    /*=============================================
    GUARDAR APERTURA
    =============================================*/
    static public function mdlIngresarApertura($tabla, $datos){

        $stmt = Conexion::conectar()->prepare("
            INSERT INTO $tabla(fecha_apertura, monto_apertura, usuario, id_sucursal, estado)
            VALUES (:fecha_apertura, :monto_apertura, :usuario, :id_sucursal, :estado)
        ");

        $stmt->bindParam(":fecha_apertura", $datos["fecha_apertura"], PDO::PARAM_STR);
        $stmt->bindParam(":monto_apertura", $datos["monto_apertura"], PDO::PARAM_STR);
        $stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_INT);
        $stmt->bindParam(":id_sucursal", $datos["id_sucursal"], PDO::PARAM_INT);
        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_INT);

        $respuesta = $stmt->execute() ? "ok" : "error";

        $stmt = null;
        return $respuesta;
    }

    /*=============================================
    CERRAR CAJA
    =============================================*/
    static public function mdlCerrarCaja($tabla, $datos){

        $stmt = Conexion::conectar()->prepare("
            UPDATE $tabla
            SET fecha_cierre = :fecha_cierre,
                monto_cierre = :monto_cierre,
                diferencia = :diferencia,
                estado = :estado
            WHERE id = :id
        ");

        $stmt->bindParam(":fecha_cierre", $datos["fecha_cierre"], PDO::PARAM_STR);
        $stmt->bindParam(":monto_cierre", $datos["monto_cierre"], PDO::PARAM_STR);
        $stmt->bindParam(":diferencia", $datos["diferencia"], PDO::PARAM_STR);
        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_INT);
        $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

        $respuesta = $stmt->execute() ? "ok" : "error";

        $stmt = null;
        return $respuesta;
    }

    /*=============================================
    REGISTRAR MOVIMIENTO DE CAJA
    =============================================*/
    static public function mdlRegistrarMovimientoCaja($tabla, $datos){

        $stmt = Conexion::conectar()->prepare("
            INSERT INTO $tabla(
                id_caja,
                tipo,
                descripcion,
                monto,
                fecha,
                usuario,
                id_sucursal,
                referencia,
                id_referencia
            ) VALUES (
                :id_caja,
                :tipo,
                :descripcion,
                :monto,
                :fecha,
                :usuario,
                :id_sucursal,
                :referencia,
                :id_referencia
            )
        ");

        $stmt->bindParam(":id_caja", $datos["id_caja"], PDO::PARAM_INT);
        $stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
        $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
        $stmt->bindParam(":monto", $datos["monto"], PDO::PARAM_STR);
        $stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
        $stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_INT);
        $stmt->bindParam(":id_sucursal", $datos["id_sucursal"], PDO::PARAM_INT);
        $stmt->bindParam(":referencia", $datos["referencia"], PDO::PARAM_STR);
        $stmt->bindParam(":id_referencia", $datos["id_referencia"], PDO::PARAM_INT);

        $respuesta = $stmt->execute() ? "ok" : "error";

        $stmt = null;
        return $respuesta;
    }

    /*=============================================
    SUMAR INGRESOS DE CAJA
    =============================================*/
    static public function mdlSumarIngresosCaja($tabla, $idCaja){

        $stmt = Conexion::conectar()->prepare("
            SELECT COALESCE(SUM(monto),0) AS total
            FROM $tabla
            WHERE id_caja = :id_caja
              AND tipo IN ('venta','abono','ingreso')
        ");

        $stmt->bindParam(":id_caja", $idCaja, PDO::PARAM_INT);
        $stmt->execute();
        $respuesta = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = null;
        return $respuesta;
    }

    /*=============================================
    SUMAR EGRESOS DE CAJA
    =============================================*/
    static public function mdlSumarEgresosCaja($tabla, $idCaja){

        $stmt = Conexion::conectar()->prepare("
            SELECT COALESCE(SUM(monto),0) AS total
            FROM $tabla
            WHERE id_caja = :id_caja
              AND tipo IN ('gasto','retiro','egreso')
        ");

        $stmt->bindParam(":id_caja", $idCaja, PDO::PARAM_INT);
        $stmt->execute();
        $respuesta = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = null;
        return $respuesta;
    }

    /*=============================================
    SUMAR GASTOS DE CAJA DESDE TABLA GASTOS POR SUCURSAL
    =============================================*/
    static public function mdlSumarGastosCaja($fechaApertura, $idSucursal){

        $stmt = Conexion::conectar()->prepare("
            SELECT COALESCE(SUM(monto),0) AS total
            FROM gastos
            WHERE fecha >= :fechaApertura
              AND id_sucursal = :id_sucursal
        ");

        $stmt->bindParam(":fechaApertura", $fechaApertura, PDO::PARAM_STR);
        $stmt->bindParam(":id_sucursal", $idSucursal, PDO::PARAM_INT);
        $stmt->execute();
        $respuesta = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = null;
        return $respuesta;
    }

    /*=============================================
    SUMAR CREDITOS PENDIENTES POR SUCURSAL
    =============================================*/
    static public function mdlSumarCreditosPendientes($idSucursal){

        $stmt = Conexion::conectar()->prepare("
            SELECT COALESCE(SUM(total),0) AS total
            FROM ventas
            WHERE estado_credito = 'pendiente'
              AND estado = 1
              AND id_sucursal = :id_sucursal
        ");

        $stmt->bindParam(":id_sucursal", $idSucursal, PDO::PARAM_INT);
        $stmt->execute();
        $respuesta = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = null;
        return $respuesta;
    }

    static public function mdlSumarMovimientosCaja($idCaja, $tipos = array()){

    $conexion = Conexion::conectar();

    $sql = "SELECT COALESCE(SUM(monto), 0) as total FROM movimientos_caja WHERE id_caja = :id_caja";

    if(!empty($tipos)){
        $placeholders = array();
        foreach($tipos as $index => $tipo){
            $placeholders[] = ":tipo".$index;
        }
        $sql .= " AND tipo IN (".implode(",", $placeholders).")";
    }

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":id_caja", $idCaja, PDO::PARAM_INT);

    if(!empty($tipos)){
        foreach($tipos as $index => $tipo){
            $stmt->bindValue(":tipo".$index, $tipo, PDO::PARAM_STR);
        }
    }

    $stmt->execute();

    $respuesta = $stmt->fetch(PDO::FETCH_ASSOC);

    return $respuesta ? (float)$respuesta["total"] : 0;
}
}