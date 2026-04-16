<?php

error_reporting(0);

if(isset($_GET["fechaInicial"])){

    $fechaInicial = $_GET["fechaInicial"];
    $fechaFinal = $_GET["fechaFinal"];

}else{

    $fechaInicial = null;
    $fechaFinal = null;
}

$respuesta = ControladorVentas::ctrRangoFechasVentas($fechaInicial, $fechaFinal);

if(!is_array($respuesta)){
	$respuesta = array();
}

$idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

$arrayFechas = array();
$sumaPagosMes = array();

foreach ($respuesta as $value) {

	if(!is_array($value)){
		continue;
	}

	/*=============================================
	FILTRAR POR SUCURSAL
	=============================================*/
	if($idSucursal > 0){
		if(!isset($value["id_sucursal"]) || (int)$value["id_sucursal"] !== $idSucursal){
			continue;
		}
	}

	/*=============================================
	OMITIR VENTAS ANULADAS / DEVUELTAS
	=============================================*/
	if(isset($value["estado"]) && (int)$value["estado"] === 0){
		continue;
	}

	if(!isset($value["fecha"]) || !isset($value["total"])){
		continue;
	}

	$fecha = substr($value["fecha"], 0, 7);

	if($fecha == ""){
		continue;
	}

	$arrayFechas[] = $fecha;

	if(!isset($sumaPagosMes[$fecha])){
		$sumaPagosMes[$fecha] = 0;
	}

	$sumaPagosMes[$fecha] += (float)$value["total"];
}

$noRepetirFechas = array_unique($arrayFechas);
sort($noRepetirFechas);

?>

<div class="box box-solid bg-teal-gradient">
	
	<div class="box-header">
		
 		<i class="fa fa-th"></i>

  		<h3 class="box-title">Gráfico de Ventas</h3>

	</div>

	<div class="box-body border-radius-none nuevoGraficoVentas">

		<div class="chart" id="line-chart-ventas" style="height: 250px;"></div>

	</div>

</div>

<script>
$(function(){

	var datosGraficaVentas = [
	<?php

	if(!empty($noRepetirFechas)){

		$rows = array();

		foreach($noRepetirFechas as $fechaItem){
			$totalMes = isset($sumaPagosMes[$fechaItem]) ? (float)$sumaPagosMes[$fechaItem] : 0;
			$rows[] = "{ y: '".$fechaItem."', ventas: ".$totalMes." }";
		}

		echo implode(",", $rows);

	}else{

		echo "{ y: '0', ventas: 0 }";
	}

	?>
	];

	if($('#line-chart-ventas').length){
		new Morris.Line({
			element          : 'line-chart-ventas',
			resize           : true,
			data             : datosGraficaVentas,
			xkey             : 'y',
			ykeys            : ['ventas'],
			labels           : ['ventas'],
			lineColors       : ['#efefef'],
			lineWidth        : 2,
			hideHover        : 'auto',
			gridTextColor    : '#fff',
			gridStrokeWidth  : 0.4,
			pointSize        : 4,
			pointStrokeColors: ['#efefef'],
			gridLineColor    : '#efefef',
			gridTextFamily   : 'Open Sans',
			preUnits         : 'Q',
			gridTextSize     : 10
		});
	}

});
</script>