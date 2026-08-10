<?php

require_once "conexion.php";

class ModeloVentas{

    /*=============================================
    MOSTRAR VENTAS
    =============================================*/
    static public function mdlMostrarVentas($tabla, $item, $valor){

        $tablasPermitidas = array("ventas", "abonos");
        $columnasPermitidasVentas = array(
            "id",
            "codigo",
            "id_cliente",
            "id_vendedor",
            "id_sucursal",
            "metodo_pago",
            "estado_pago",
            "estado_credito",
            "estado",
            "fecha",
            "fecha_vencimiento"
        );

        if(!in_array($tabla, $tablasPermitidas)){
            return array();
        }

        if($item !== null){

            if(!in_array($item, $columnasPermitidasVentas)){
                return array();
            }

            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :valor ORDER BY id ASC");

            if(in_array($item, array("id", "codigo", "id_cliente", "id_vendedor", "id_sucursal", "estado"))){
                $stmt->bindParam(":valor", $valor, PDO::PARAM_INT);
                $stmt->execute();
                $respuesta = $stmt->fetch(PDO::FETCH_ASSOC);
            }else{
                $stmt->bindParam(":valor", $valor, PDO::PARAM_STR);
                $stmt->execute();
                $respuesta = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            $stmt = null;
            return $respuesta;

        }else{

            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id ASC");
            $stmt->execute();
            $respuesta = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = null;
            return $respuesta;
        }
    }

    /*=============================================
    REGISTRO DE VENTA
    =============================================*/
    static public function mdlIngresarVenta($tabla, $datos){

        $db = Conexion::conectar();

        $stmt = $db->prepare("
            INSERT INTO $tabla(
                codigo,
                id_cliente,
                id_vendedor,
                id_sucursal,
                productos,
                impuesto,
                neto,
                total,
                metodo_pago,
                estado_pago,
                estado_credito,
                fecha_vencimiento
            )
            VALUES (
                :codigo,
                :id_cliente,
                :id_vendedor,
                :id_sucursal,
                :productos,
                :impuesto,
                :neto,
                :total,
                :metodo_pago,
                :estado_pago,
                :estado_credito,
                :fecha_vencimiento
            )
        ");

        $stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
        $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
        $stmt->bindParam(":id_vendedor", $datos["id_vendedor"], PDO::PARAM_INT);
        $stmt->bindParam(":id_sucursal", $datos["id_sucursal"], PDO::PARAM_INT);
        $stmt->bindParam(":productos", $datos["productos"], PDO::PARAM_STR);
        $stmt->bindParam(":impuesto", $datos["impuesto"], PDO::PARAM_STR);
        $stmt->bindParam(":neto", $datos["neto"], PDO::PARAM_STR);
        $stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
        $stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);
        $stmt->bindParam(":estado_pago", $datos["estado_pago"], PDO::PARAM_STR);
        $stmt->bindParam(":estado_credito", $datos["estado_credito"], PDO::PARAM_STR);

        if(isset($datos["fecha_vencimiento"]) && $datos["fecha_vencimiento"] !== null && $datos["fecha_vencimiento"] !== ""){
            $stmt->bindParam(":fecha_vencimiento", $datos["fecha_vencimiento"], PDO::PARAM_STR);
        }else{
            $fechaVencimientoNull = null;
            $stmt->bindParam(":fecha_vencimiento", $fechaVencimientoNull, PDO::PARAM_NULL);
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
    EDITAR VENTA
    =============================================*/
    static public function mdlEditarVenta($tabla, $datos){

        $stmt = Conexion::conectar()->prepare("
            UPDATE $tabla SET
                id_cliente = :id_cliente,
                id_vendedor = :id_vendedor,
                productos = :productos,
                impuesto = :impuesto,
                neto = :neto,
                total = :total,
                metodo_pago = :metodo_pago,
                estado_pago = :estado_pago,
                estado_credito = :estado_credito,
                fecha_vencimiento = :fecha_vencimiento
            WHERE id = :id
        ");

        $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
        $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
        $stmt->bindParam(":id_vendedor", $datos["id_vendedor"], PDO::PARAM_INT);
        $stmt->bindParam(":productos", $datos["productos"], PDO::PARAM_STR);
        $stmt->bindParam(":impuesto", $datos["impuesto"], PDO::PARAM_STR);
        $stmt->bindParam(":neto", $datos["neto"], PDO::PARAM_STR);
        $stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
        $stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);
        $stmt->bindParam(":estado_pago", $datos["estado_pago"], PDO::PARAM_STR);
        $stmt->bindParam(":estado_credito", $datos["estado_credito"], PDO::PARAM_STR);

        if(isset($datos["fecha_vencimiento"]) && $datos["fecha_vencimiento"] !== null && $datos["fecha_vencimiento"] !== ""){
            $stmt->bindParam(":fecha_vencimiento", $datos["fecha_vencimiento"], PDO::PARAM_STR);
        }else{
            $fechaVencimientoNull = null;
            $stmt->bindParam(":fecha_vencimiento", $fechaVencimientoNull, PDO::PARAM_NULL);
        }

        $respuesta = $stmt->execute() ? "ok" : "error";

        $stmt = null;
        return $respuesta;
    }

    
/*=============================================
CREAR REGISTRO DE CREDITO
=============================================*/
static public function mdlCrearCredito($datos){

    $stmt = Conexion::conectar()->prepare("
        INSERT INTO creditos(id_venta, id_cliente, total_venta, saldo_pendiente, estado)
        VALUES (:id_venta, :id_cliente, :total_venta, :saldo_pendiente, :estado)
    ");

    $stmt->bindParam(":id_venta", $datos["id_venta"], PDO::PARAM_INT);
    $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
    $stmt->bindParam(":total_venta", $datos["total_venta"], PDO::PARAM_STR);
    $stmt->bindParam(":saldo_pendiente", $datos["saldo_pendiente"], PDO::PARAM_STR);
    $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_INT);

    $respuesta = $stmt->execute() ? "ok" : "error";

    $stmt = null;
    return $respuesta;
}

/*=============================================
ACTUALIZAR SALDO DE CREDITO
=============================================*/
static public function mdlActualizarCreditoPorVenta($idVenta){

    $db = Conexion::conectar();

    $stmtVenta = $db->prepare("
        SELECT id, id_cliente, total, metodo_pago
        FROM ventas
        WHERE id = :id
        LIMIT 1
    ");
    $stmtVenta->bindParam(":id", $idVenta, PDO::PARAM_INT);
    $stmtVenta->execute();
    $venta = $stmtVenta->fetch(PDO::FETCH_ASSOC);
    $stmtVenta = null;

    if(!$venta){
        return "error";
    }

    if($venta["metodo_pago"] !== "Credito"){
        return "ok";
    }

    $stmtAbonos = $db->prepare("
        SELECT COALESCE(SUM(monto),0) AS total_abonado
        FROM abonos
        WHERE id_venta = :id_venta
    ");
    $stmtAbonos->bindParam(":id_venta", $idVenta, PDO::PARAM_INT);
    $stmtAbonos->execute();
    $abonos = $stmtAbonos->fetch(PDO::FETCH_ASSOC);
    $stmtAbonos = null;

    $totalVenta = (float)$venta["total"];
    $totalAbonado = (float)$abonos["total_abonado"];
    $saldoPendiente = $totalVenta - $totalAbonado;

    if($saldoPendiente < 0){
        $saldoPendiente = 0;
    }

    $estadoCredito = ($saldoPendiente <= 0) ? 0 : 1;

    $stmtExiste = $db->prepare("
        SELECT id
        FROM creditos
        WHERE id_venta = :id_venta
        LIMIT 1
    ");
    $stmtExiste->bindParam(":id_venta", $idVenta, PDO::PARAM_INT);
    $stmtExiste->execute();
    $credito = $stmtExiste->fetch(PDO::FETCH_ASSOC);
    $stmtExiste = null;

    if($credito){

        $stmtUpdate = $db->prepare("
            UPDATE creditos
            SET total_venta = :total_venta,
                saldo_pendiente = :saldo_pendiente,
                estado = :estado
            WHERE id_venta = :id_venta
        ");

        $stmtUpdate->bindParam(":total_venta", $totalVenta, PDO::PARAM_STR);
        $stmtUpdate->bindParam(":saldo_pendiente", $saldoPendiente, PDO::PARAM_STR);
        $stmtUpdate->bindParam(":estado", $estadoCredito, PDO::PARAM_INT);
        $stmtUpdate->bindParam(":id_venta", $idVenta, PDO::PARAM_INT);

        $ok = $stmtUpdate->execute();

    }else{

        $stmtInsert = $db->prepare("
            INSERT INTO creditos(id_venta, id_cliente, total_venta, saldo_pendiente, estado)
            VALUES (:id_venta, :id_cliente, :total_venta, :saldo_pendiente, :estado)
        ");

        $stmtInsert->bindParam(":id_venta", $idVenta, PDO::PARAM_INT);
        $stmtInsert->bindParam(":id_cliente", $venta["id_cliente"], PDO::PARAM_INT);
        $stmtInsert->bindParam(":total_venta", $totalVenta, PDO::PARAM_STR);
        $stmtInsert->bindParam(":saldo_pendiente", $saldoPendiente, PDO::PARAM_STR);
        $stmtInsert->bindParam(":estado", $estadoCredito, PDO::PARAM_INT);

        $ok = $stmtInsert->execute();
    }

    return $ok ? "ok" : "error";
}

    /*=============================================
    ACTUALIZAR ESTADO DE CRÉDITO
    =============================================*/
    static public function mdlActualizarEstadoCredito($tabla, $idVenta){

        $db = Conexion::conectar();

        $stmtVenta = $db->prepare("SELECT total, metodo_pago FROM $tabla WHERE id = :id");
        $stmtVenta->bindParam(":id", $idVenta, PDO::PARAM_INT);
        $stmtVenta->execute();
        $venta = $stmtVenta->fetch(PDO::FETCH_ASSOC);
        $stmtVenta = null;

        if(!$venta){
            return "error";
        }

        if(!isset($venta["metodo_pago"]) || $venta["metodo_pago"] !== "Credito"){

            $estadoPago = "pagado";
            $estadoCredito = "pagado";
            $comentario = "";

        }else{

            $stmtAbonos = $db->prepare("SELECT COALESCE(SUM(monto),0) AS total_abonado FROM abonos WHERE id_venta = :id_venta");
            $stmtAbonos->bindParam(":id_venta", $idVenta, PDO::PARAM_INT);
            $stmtAbonos->execute();
            $abonos = $stmtAbonos->fetch(PDO::FETCH_ASSOC);
            $stmtAbonos = null;

            $totalVenta = isset($venta["total"]) ? (float)$venta["total"] : 0;
            $totalAbonado = (is_array($abonos) && isset($abonos["total_abonado"])) ? (float)$abonos["total_abonado"] : 0;

            if($totalAbonado <= 0){
                $estadoPago = "pendiente";
                $estadoCredito = "pendiente";
                $comentario = "";
            }elseif($totalAbonado < $totalVenta){
                $estadoPago = "parcial";
                $estadoCredito = "pendiente";
                $comentario = "Venta con abonos parciales";
            }else{
                $estadoPago = "pagado";
                $estadoCredito = "pagado";
                $comentario = "Venta pagada en su totalidad";
            }
        }

        $stmtUpdate = $db->prepare("
    UPDATE $tabla
    SET estado_pago = :estado_pago,
        estado_credito = :estado_credito,
        comentario = :comentario
    WHERE id = :id
");

$stmtUpdate->bindParam(":estado_pago", $estadoPago, PDO::PARAM_STR);
$stmtUpdate->bindParam(":estado_credito", $estadoCredito, PDO::PARAM_STR);
$stmtUpdate->bindParam(":comentario", $comentario, PDO::PARAM_STR);
$stmtUpdate->bindParam(":id", $idVenta, PDO::PARAM_INT);

$respuesta = $stmtUpdate->execute() ? "ok" : "error";

if($respuesta != "ok"){
    $stmtUpdate = null;
    return "error";
}

$stmtUpdate = null;

$respCredito = self::mdlActualizarCreditoPorVenta($idVenta);

return $respCredito;


    }

    /*=============================================
    ELIMINAR VENTA
    =============================================*/
    static public function mdlEliminarVenta($tabla, $datos){

        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
        $stmt->bindParam(":id", $datos, PDO::PARAM_INT);

        $respuesta = $stmt->execute() ? "ok" : "error";

        $stmt = null;
        return $respuesta;
    }

    /*=============================================
    RANGO FECHAS
    =============================================*/
    static public function mdlRangoFechasVentas($tabla, $fechaInicial, $fechaFinal){

        if($fechaInicial == null){

            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id ASC");
            $stmt->execute();
            $respuesta = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = null;
            return $respuesta;

        }else if($fechaInicial == $fechaFinal){

            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha LIKE :fecha ORDER BY id ASC");
            $fecha = "%".$fechaFinal."%";
            $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
            $stmt->execute();
            $respuesta = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = null;
            return $respuesta;

        }else{

            $fechaActual = new DateTime();
            $fechaActual->add(new DateInterval("P1D"));
            $fechaActualMasUno = $fechaActual->format("Y-m-d");

            $fechaFinal2 = new DateTime($fechaFinal);
            $fechaFinal2->add(new DateInterval("P1D"));
            $fechaFinalMasUno = $fechaFinal2->format("Y-m-d");

            if($fechaFinalMasUno == $fechaActualMasUno){
                $fechaHasta = $fechaFinalMasUno;
            }else{
                $fechaHasta = $fechaFinal;
            }

            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha BETWEEN :fechaInicial AND :fechaFinal ORDER BY id ASC");
            $stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
            $stmt->bindParam(":fechaFinal", $fechaHasta, PDO::PARAM_STR);
            $stmt->execute();
            $respuesta = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = null;
            return $respuesta;
        }

        $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

if($idSucursal > 0){
  $stmt = Conexion::conectar()->prepare("
    SELECT * FROM $tabla 
    WHERE fecha BETWEEN :fechaInicial AND :fechaFinal 
    AND id_sucursal = :id_sucursal
    ORDER BY id ASC
  ");

  $stmt->bindParam(":id_sucursal", $idSucursal, PDO::PARAM_INT);
}
    }

    /*=============================================
    SUMAR EL TOTAL DE VENTAS
    =============================================*/
    static public function mdlSumaTotalVentas($tabla){

  $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

  if($idSucursal > 0){

    $stmt = Conexion::conectar()->prepare("
      SELECT SUM(total) as total 
      FROM $tabla 
      WHERE id_sucursal = :id_sucursal
      AND estado != 0
    ");

    $stmt->bindParam(":id_sucursal", $idSucursal, PDO::PARAM_INT);

  }else{

    $stmt = Conexion::conectar()->prepare("
      SELECT SUM(total) as total 
      FROM $tabla 
      WHERE estado != 0
    ");
  }

  $stmt->execute();

  return $stmt->fetch(PDO::FETCH_ASSOC);
}

    /*=============================================
    ACTUALIZAR VENTA PARA DEVOLUCIÓN
    =============================================*/
    static public function mdlActualizarVentaDevolucion($tabla, $datos){

        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET estado = :estado, comentario = :comentario WHERE id = :id");

        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_INT);
        $stmt->bindParam(":comentario", $datos["comentario"], PDO::PARAM_STR);
        $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

        $respuesta = $stmt->execute() ? "ok" : "error";

        $stmt = null;
        return $respuesta;
    }

    /*=============================================
    MARCAR VENTA COMO PAGADA
    =============================================*/
    static public function mdlMarcarVentaPagada($tabla, $idVenta){

        $stmt = Conexion::conectar()->prepare("
            UPDATE $tabla
            SET estado_pago = 'pagado',
                estado_credito = 'pagado',
                comentario = 'Venta pagada en su totalidad'
            WHERE id = :id
        ");

        $stmt->bindParam(":id", $idVenta, PDO::PARAM_INT);

        $respuesta = $stmt->execute() ? "ok" : "error";

        $stmt = null;
        return $respuesta;
    }

    /*=============================================
    SUMAR ABONOS DE UNA VENTA
    =============================================*/
    static public function mdlSumarAbonosVenta($tabla, $idVenta){

        $stmt = Conexion::conectar()->prepare("SELECT COALESCE(SUM(monto),0) AS total FROM $tabla WHERE id_venta = :id_venta");
        $stmt->bindParam(":id_venta", $idVenta, PDO::PARAM_INT);
        $stmt->execute();
        $respuesta = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = null;
        return $respuesta;
    }

    /*=============================================
    REGISTRAR NUEVO ABONO
    =============================================*/
    static public function mdlIngresarAbono($tabla, $datos){

        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(id_venta, monto, fecha) VALUES (:id_venta, :monto, :fecha)");

        $stmt->bindParam(":id_venta", $datos["id_venta"], PDO::PARAM_INT);
        $stmt->bindParam(":monto", $datos["monto"], PDO::PARAM_STR);
        $stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);

        $respuesta = $stmt->execute() ? "ok" : "error";

        $stmt = null;
        return $respuesta;
    }
/*=============================================
    AUDITORIA DE VENTAS
    =============================================*/
    static public function mdlAuditoriaVentas($tabla, $filtros = array()){

        $conexion = Conexion::conectar();

        $sql = "SELECT * FROM $tabla WHERE 1=1";
        $params = array();

        if(isset($filtros["id_sucursal"]) && (int)$filtros["id_sucursal"] > 0){
            $sql .= " AND id_sucursal = :id_sucursal";
            $params[":id_sucursal"] = (int)$filtros["id_sucursal"];
        }

        if(!empty($filtros["fecha_inicial"]) && !empty($filtros["fecha_final"])){
            $sql .= " AND DATE(fecha) BETWEEN :fecha_inicial AND :fecha_final";
            $params[":fecha_inicial"] = $filtros["fecha_inicial"];
            $params[":fecha_final"] = $filtros["fecha_final"];
        }elseif(!empty($filtros["fecha_inicial"])){
            $sql .= " AND DATE(fecha) >= :fecha_inicial";
            $params[":fecha_inicial"] = $filtros["fecha_inicial"];
        }elseif(!empty($filtros["fecha_final"])){
            $sql .= " AND DATE(fecha) <= :fecha_final";
            $params[":fecha_final"] = $filtros["fecha_final"];
        }

        if(isset($filtros["estado"]) && $filtros["estado"] !== "" && $filtros["estado"] !== null){
            $sql .= " AND estado = :estado";
            $params[":estado"] = (int)$filtros["estado"];
        }

        if(!empty($filtros["metodo_pago"])){
            $sql .= " AND metodo_pago = :metodo_pago";
            $params[":metodo_pago"] = $filtros["metodo_pago"];
        }

        if(!empty($filtros["estado_pago"])){
            $sql .= " AND estado_pago = :estado_pago";
            $params[":estado_pago"] = $filtros["estado_pago"];
        }

        if(!empty($filtros["estado_credito"])){
            $sql .= " AND estado_credito = :estado_credito";
            $params[":estado_credito"] = $filtros["estado_credito"];
        }

        if(isset($filtros["id_vendedor"]) && (int)$filtros["id_vendedor"] > 0){
            $sql .= " AND id_vendedor = :id_vendedor";
            $params[":id_vendedor"] = (int)$filtros["id_vendedor"];
        }

        if(isset($filtros["id_cliente"]) && (int)$filtros["id_cliente"] > 0){
            $sql .= " AND id_cliente = :id_cliente";
            $params[":id_cliente"] = (int)$filtros["id_cliente"];
        }

        if(isset($filtros["codigo"]) && (int)$filtros["codigo"] > 0){
            $sql .= " AND codigo = :codigo";
            $params[":codigo"] = (int)$filtros["codigo"];
        }

        $sql .= " ORDER BY id DESC";

        $stmt = $conexion->prepare($sql);

        foreach($params as $key => $value){
            if(is_int($value)){
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }else{
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}
