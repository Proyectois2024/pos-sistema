<?php
require_once "modelos/conexion.php";

try {
    $db = Conexion::conectar();

    echo "Conexión OK<br>";

    $stmt = $db->query("SELECT DATABASE() AS db_actual, COUNT(*) AS total FROM usuarios");
    $row = $stmt->fetch();

    echo "Base actual: " . $row["db_actual"] . "<br>";
    echo "Usuarios: " . $row["total"] . "<br>";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
