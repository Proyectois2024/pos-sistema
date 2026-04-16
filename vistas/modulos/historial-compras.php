<?php
require_once "controladores/proveedores.controlador.php";
require_once "controladores/productos.controlador.php";
require_once "controladores/compras.controlador.php";
require_once "modelos/compras.modelo.php";
require_once "modelos/proveedores.modelo.php";
require_once "modelos/productos.modelo.php";

if (!isset($_GET["idProveedor"])) {
  echo '<script>window.location = "proveedores";</script>';
  return;
}

$idProveedor = $_GET["idProveedor"];
$proveedor = ControladorProveedores::ctrMostrarProveedores("id", $idProveedor);
$compras = ModeloCompras::mdlObtenerComprasPorProveedor($idProveedor);
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Historial de Compras - <?php echo $proveedor["nombre"]; ?></h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li><a href="proveedores">Proveedores</a></li>
      <li class="active">Historial de Compras</li>
    </ol>
  </section>

  <section class="content">
    <div class="box">
      <div class="box-body table-responsive">
        <?php if (count($compras) > 0): ?>
          <?php foreach ($compras as $compra): ?>
            <h4>Compra del <?php echo $compra["fecha_compra"]; ?></h4>
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th>Cantidad</th>
                  <th>Unidad</th>
                  <th>Precio Compra</th>
                  <th>Precio Venta</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $detalles = ModeloCompras::mdlObtenerDetallesCompra($compra["id"]);
                  foreach ($detalles as $detalle):
                    $producto = ControladorProductos::ctrMostrarProductos("id", $detalle["id_producto"], "id");
                    if ($producto): // Validar que el producto exista
                ?>
                  <tr>
                    <td><?php echo $producto["descripcion"]; ?></td>
                    <td><?php echo $detalle["cantidad"]; ?></td>
                    <td><?php echo ucfirst($detalle["unidad"]); ?></td>
                    <td>Q<?php echo number_format($detalle["precio_compra"], 2); ?></td>
                    <td>Q<?php echo number_format($detalle["precio_venta"], 2); ?></td>
                  </tr>
                <?php 
                    endif;
                  endforeach; 
                ?>
              </tbody>
            </table>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No se han registrado compras para este proveedor.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>
</div>
