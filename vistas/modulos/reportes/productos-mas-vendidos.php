<?php

$item = null;
$valor = null;
$orden = "ventas";

$productos = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

if(!is_array($productos)){
  $productos = array();
}

$colores = array("red","green","yellow","aqua","purple","blue","cyan","magenta","orange","gold");

/*=============================================
FILTRAR SOLO PRODUCTOS CON VENTAS
=============================================*/
$productosConVentas = array();

foreach($productos as $producto){
  if(isset($producto["ventas"]) && (float)$producto["ventas"] > 0){
    $productosConVentas[] = $producto;
  }
}

/*=============================================
TOMAR SOLO LOS PRIMEROS 10
=============================================*/
$productosTop = array_slice($productosConVentas, 0, 10);

/*=============================================
SUMA TOTAL DE VENTAS DE ESOS PRODUCTOS
=============================================*/
$totalVentasTop = 0;

foreach($productosTop as $productoTop){
  $totalVentasTop += isset($productoTop["ventas"]) ? (float)$productoTop["ventas"] : 0;
}

?>

<div class="box box-default">
  
  <div class="box-header with-border">
      <h3 class="box-title">Productos más vendidos</h3>
    </div>

  <div class="box-body">
    
        <div class="row">

          <div class="col-md-7">

        <div class="chart-responsive">
                <canvas id="pieChart" height="150"></canvas>
              </div>

          </div>

        <div class="col-md-5">
            
          <ul class="chart-legend clearfix">

          <?php if(count($productosTop) > 0): ?>

          <?php foreach($productosTop as $i => $producto): ?>
            <li>
              <i class="fa fa-circle-o text-<?php echo $colores[$i % count($colores)]; ?>"></i>
              <?php echo $producto["descripcion"]; ?>
            </li>
          <?php endforeach; ?>

        <?php else: ?>

          <li><i class="fa fa-circle-o text-muted"></i> Sin datos de ventas</li>

        <?php endif; ?>

          </ul>

        </div>

    </div>

    </div>

    <div class="box-footer no-padding">
      
    <ul class="nav nav-pills nav-stacked">
      
      <?php if(count($productosTop) > 0): ?>

        <?php foreach(array_slice($productosTop, 0, 5) as $i => $producto): ?>

          <?php
            $imagen = !empty($producto["imagen"]) ? $producto["imagen"] : "vistas/img/productos/default/anonymous.png";
            $ventasProducto = isset($producto["ventas"]) ? (float)$producto["ventas"] : 0;
            $porcentaje = ($totalVentasTop > 0) ? ceil(($ventasProducto * 100) / $totalVentasTop) : 0;
          ?>

          <li>
            <a>
              <img src="<?php echo $imagen; ?>" class="img-thumbnail" width="60px" style="margin-right:10px"> 
              <?php echo $producto["descripcion"]; ?>

              <span class="pull-right text-<?php echo $colores[$i % count($colores)]; ?>">
                <?php echo $porcentaje; ?>%
              </span>
            </a>
          </li>

        <?php endforeach; ?>

      <?php else: ?>

        <li>
          <a>No hay productos con ventas registradas</a>
        </li>

      <?php endif; ?>

    </ul>

    </div>

</div>

<script>
$(function(){

  var pieCanvas = $('#pieChart');

  if(pieCanvas.length === 0){
    return;
  }

  var pieChartCanvas = pieCanvas.get(0).getContext('2d');
  var pieChart = new Chart(pieChartCanvas);

  var PieData = [
    <?php if(count($productosTop) > 0): ?>
      <?php foreach($productosTop as $i => $producto): ?>
      {
        value    : <?php echo (float)$producto["ventas"]; ?>,
        color    : '<?php echo $colores[$i % count($colores)]; ?>',
        highlight: '<?php echo $colores[$i % count($colores)]; ?>',
        label    : '<?php echo addslashes($producto["descripcion"]); ?>'
      },
      <?php endforeach; ?>
    <?php else: ?>
      {
        value    : 1,
        color    : '#d2d6de',
        highlight: '#d2d6de',
        label    : 'Sin datos'
      }
    <?php endif; ?>
  ];

  var pieOptions = {
    segmentShowStroke    : true,
    segmentStrokeColor   : '#fff',
    segmentStrokeWidth   : 1,
    percentageInnerCutout: 50,
    animationSteps       : 100,
    animationEasing      : 'easeOutBounce',
    animateRotate        : true,
    animateScale         : false,
    responsive           : true,
    maintainAspectRatio  : false,
    legendTemplate       : '<ul class=\'<%=name.toLowerCase()%>-legend\'><% for (var i=0; i<segments.length; i++){%><li><span style=\'background-color:<%=segments[i].fillColor%>\'></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>',
    tooltipTemplate      : '<%=value %> <%=label%>'
  };

  pieChart.Doughnut(PieData, pieOptions);

});
</script>