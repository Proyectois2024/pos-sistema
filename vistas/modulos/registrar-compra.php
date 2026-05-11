<?php

require_once "controladores/proveedores.controlador.php";
require_once "controladores/productos.controlador.php";
require_once "controladores/compras.controlador.php";
require_once "modelos/proveedores.modelo.php";
require_once "modelos/productos.modelo.php";
require_once "modelos/compras.modelo.php";

if (!isset($_GET["idProveedor"]) || empty($_GET["idProveedor"])) {
  echo '<script>window.location = "proveedores";</script>';
  return;
}

$idProveedor = (int) $_GET["idProveedor"];

$proveedor = ControladorProveedores::ctrMostrarProveedores("id", $idProveedor);

if (!$proveedor || !isset($proveedor["id"])) {
  echo "<pre>";
  echo "NO ENCONTRO PROVEEDOR\n";
  var_dump($_GET);
  var_dump($idProveedor);
  var_dump($proveedor);
  echo "</pre>";
  exit();
}

?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Registrar Compra a: <?php echo $proveedor["nombre"]; ?></h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li><a href="proveedores">Proveedores</a></li>
      <li class="active">Registrar Compra</li>
    </ol>
  </section>

  <section class="content">
    <div class="box">
      <form method="post" role="form">
        <div class="box-body">
          <input type="hidden" name="idProveedor" value="<?php echo $idProveedor; ?>">

          <div class="form-group">
            <label>Fecha de Compra</label>
            <input type="date" name="fechaCompra" class="form-control" value="<?php echo date("Y-m-d"); ?>" required>
          </div>

          <hr>
          <h4>Productos</h4>

          <div id="productosCompra">
            <div class="producto-compra form-inline">
              <select name="productos[]" class="form-control" required>
                <option value="">Seleccione un producto</option>
                <?php
                $productos = ControladorProductos::ctrMostrarProductos(null, null, "id");
                foreach ($productos as $producto) {
                  echo '<option value="' . $producto["id"] . '">' . $producto["descripcion"] . '</option>';
                }
                ?>
              </select>

              <input type="number" name="cantidades[]" class="form-control" placeholder="Cantidad" step="0.01" required>

              <select name="unidades[]" class="form-control">
                <option value="unidad">Unidad</option>
                <option value="caja">Caja</option>
                <option value="frasco">Frasco</option>
              </select>

              <input type="number" name="preciosCompra[]" class="form-control" placeholder="Precio compra" step="0.01" required>
              <input type="number" name="preciosVenta[]" class="form-control" placeholder="Precio venta" step="0.01" required>
            </div>
          </div>

          <br>
          <button type="button" class="btn btn-default" id="agregarProducto">Agregar otro producto</button>
        </div>

        <div class="box-footer">
          <button type="submit" class="btn btn-primary">Guardar Compra</button>
        </div>

        <?php
       // $guardar = new ControladorCompras();
        //$guardar->ctrCrearCompra();
        ?>
      </form>
    </div>
  </section>
</div>

<script>
document.getElementById("agregarProducto").addEventListener("click", function() {
  var contenedor = document.getElementById("productosCompra");
  var clon = contenedor.children[0].cloneNode(true);
  clon.querySelectorAll("input").forEach(input => input.value = "");
  clon.querySelector("select").selectedIndex = 0;
  contenedor.appendChild(clon);
});
</script>
