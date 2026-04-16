<?php

$item = null;
$valor = null;
$orden = "id";

$productos = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

if(!is_array($productos)){
  $productos = array();
}

$productosRecientes = array_slice($productos, 0, 10);

?>

<div class="box box-primary">

  <div class="box-header with-border">

    <h3 class="box-title">Productos Agregados Recientemente</h3>

    <div class="box-tools pull-right">

      <button type="button" class="btn btn-box-tool" data-widget="collapse">
        <i class="fa fa-minus"></i>
      </button>

      <button type="button" class="btn btn-box-tool" data-widget="remove">
        <i class="fa fa-times"></i>
      </button>

    </div>

  </div>
  
  <div class="box-body">

    <ul class="products-list product-list-in-box">

    <?php if(count($productosRecientes) > 0): ?>

      <?php foreach($productosRecientes as $producto): ?>

        <?php
          $imagen = !empty($producto["imagen"])
            ? $producto["imagen"]
            : "vistas/img/productos/default/anonymous.png";
        ?>

        <li class="item">

          <div class="product-img">
            <img src="<?php echo $imagen; ?>" alt="Product Image">
          </div>

          <div class="product-info">

            <a href="productos" class="product-title">
              <?php echo $producto["descripcion"]; ?>
              <span class="label label-warning pull-right">
                Q<?php echo number_format((float)$producto["precio_venta"], 2); ?>
              </span>
            </a>
    
          </div>

        </li>

      <?php endforeach; ?>

    <?php else: ?>

      <li class="item">
        <div class="product-info">
          <a href="productos" class="product-title">
            No hay productos registrados
          </a>
        </div>
      </li>

    <?php endif; ?>

    </ul>

  </div>

  <div class="box-footer text-center">

    <a href="productos" class="uppercase">Ver todos los productos</a>
  
  </div>

</div>