<?php

$item = null;
$valor = null;

$ventas = ControladorVentas::ctrMostrarVentas($item, $valor);
$usuarios = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);

if(!is_array($ventas)){
  $ventas = array();
}

if(!is_array($usuarios)){
  $usuarios = array();
}

$idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

$arrayVendedores = array();
$sumaTotalVendedores = array();

foreach ($ventas as $valueVentas) {

  if(!is_array($valueVentas)){
    continue;
  }

  if(isset($valueVentas["estado"]) && (int)$valueVentas["estado"] === 0){
    continue;
  }

  if($idSucursal > 0){
    if(!isset($valueVentas["id_sucursal"]) || (int)$valueVentas["id_sucursal"] !== $idSucursal){
      continue;
    }
  }

  foreach ($usuarios as $valueUsuarios) {

    if(!is_array($valueUsuarios)){
      continue;
    }

    if(
      isset($valueUsuarios["id"]) &&
      isset($valueVentas["id_vendedor"]) &&
      (int)$valueUsuarios["id"] === (int)$valueVentas["id_vendedor"]
    ){

      $nombreVendedor = isset($valueUsuarios["nombre"]) ? $valueUsuarios["nombre"] : "Sin nombre";
      $netoVenta = isset($valueVentas["neto"]) ? (float)$valueVentas["neto"] : 0;

      $arrayVendedores[] = $nombreVendedor;

      if(!isset($sumaTotalVendedores[$nombreVendedor])){
        $sumaTotalVendedores[$nombreVendedor] = 0;
      }

      $sumaTotalVendedores[$nombreVendedor] += $netoVenta;
    }
  }
}

$noRepetirNombres = array_unique($arrayVendedores);
sort($noRepetirNombres);

?>

<div class="box box-success">
  
  <div class="box-header with-border">
    
      <h3 class="box-title">Vendedores</h3>
  
    </div>

    <div class="box-body">
      
    <div class="chart-responsive">
      
      <div class="chart" id="bar-chart1" style="height: 300px;"></div>

    </div>

    </div>

</div>

<script>
$(function(){

  var dataVendedores = [
    <?php

    if(!empty($noRepetirNombres)){

      $rows = array();

      foreach($noRepetirNombres as $nombre){
        $total = isset($sumaTotalVendedores[$nombre]) ? (float)$sumaTotalVendedores[$nombre] : 0;
        $rows[] = "{y: '".addslashes($nombre)."', a: ".$total."}";
      }

      echo implode(",", $rows);

    }else{

      echo "{y: 'Sin datos', a: 0}";
    }

    ?>
  ];

  if($('#bar-chart1').length){
    new Morris.Bar({
      element: 'bar-chart1',
      resize: true,
      data: dataVendedores,
      barColors: ['#0af'],
      xkey: 'y',
      ykeys: ['a'],
      labels: ['ventas'],
      preUnits: 'Q',
      hideHover: 'auto'
    });
  }

});
</script>