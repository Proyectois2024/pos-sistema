<?php

$item = null;
$valor = null;

$ventas = ControladorVentas::ctrMostrarVentas($item, $valor);
$clientes = ControladorClientes::ctrMostrarClientes($item, $valor);

if(!is_array($ventas)){
  $ventas = array();
}

if(!is_array($clientes)){
  $clientes = array();
}

$idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

$arrayClientes = array();
$sumaTotalClientes = array();

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

  foreach ($clientes as $valueClientes) {
    
    if(!is_array($valueClientes)){
      continue;
    }

    if(
      isset($valueClientes["id"]) &&
      isset($valueVentas["id_cliente"]) &&
      (int)$valueClientes["id"] === (int)$valueVentas["id_cliente"]
    ){

      $nombreCliente = isset($valueClientes["nombre"]) ? $valueClientes["nombre"] : "Sin nombre";
      $netoVenta = isset($valueVentas["neto"]) ? (float)$valueVentas["neto"] : 0;

      $arrayClientes[] = $nombreCliente;

      if(!isset($sumaTotalClientes[$nombreCliente])){
        $sumaTotalClientes[$nombreCliente] = 0;
      }

      $sumaTotalClientes[$nombreCliente] += $netoVenta;
    }
  }
}

$noRepetirNombres = array_unique($arrayClientes);
sort($noRepetirNombres);

?>

<div class="box box-primary">
  
  <div class="box-header with-border">
    
      <h3 class="box-title">Compradores</h3>
  
    </div>

    <div class="box-body">
      
    <div class="chart-responsive">
      
      <div class="chart" id="bar-chart2" style="height: 300px;"></div>

    </div>

    </div>

</div>

<script>
$(function(){

  var dataCompradores = [
    <?php

    if(!empty($noRepetirNombres)){

      $rows = array();

      foreach($noRepetirNombres as $nombre){
        $total = isset($sumaTotalClientes[$nombre]) ? (float)$sumaTotalClientes[$nombre] : 0;
        $rows[] = "{y: '".addslashes($nombre)."', a: ".$total."}";
      }

      echo implode(",", $rows);

    }else{

      echo "{y: 'Sin datos', a: 0}";
    }

    ?>
  ];

  if($('#bar-chart2').length){
    new Morris.Bar({
      element: 'bar-chart2',
      resize: true,
      data: dataCompradores,
      barColors: ['#f6a'],
      xkey: 'y',
      ykeys: ['a'],
      labels: ['ventas'],
      preUnits: 'Q',
      hideHover: 'auto'
    });
  }

});
</script>