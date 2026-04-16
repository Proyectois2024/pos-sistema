<?php
session_start();
require_once "../modelos/conexion.php";

if(isset($_POST["idSucursal"])){

    $idSucursal = (int)$_POST["idSucursal"];

    if($idSucursal === 0){

        $_SESSION["id_sucursal"] = 0;
        $_SESSION["nombre_sucursal"] = "Todas las sucursales";
        $_SESSION["codigo_sucursal"] = "GLOBAL";

        echo "ok";
        exit;
    }

    $stmt = Conexion::conectar()->prepare("
        SELECT id, nombre, codigo
        FROM sucursales
        WHERE id = :id
          AND estado = 1
        LIMIT 1
    ");
    $stmt->bindParam(":id", $idSucursal, PDO::PARAM_INT);
    $stmt->execute();

    $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);

    if($sucursal){

        $_SESSION["id_sucursal"] = (int)$sucursal["id"];
        $_SESSION["nombre_sucursal"] = $sucursal["nombre"];
        $_SESSION["codigo_sucursal"] = $sucursal["codigo"];

        echo "ok";

    }else{

        echo "error";
    }
}