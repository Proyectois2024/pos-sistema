<?php

session_start();

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

class TablaProductosVentas{

	public function mostrarTablaProductosVentas(){

		$item = null;
    	$valor = null;
    	$orden = "id";

  		$productos = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

  		if(!is_array($productos) || count($productos) == 0){
  			echo json_encode(["data" => []]);
		  	return;
  		}

  		$data = [];

  		foreach ($productos as $i => $producto){

  			$stockActual = isset($producto["stock"]) ? (float)$producto["stock"] : 0;

		  	$imagen = !empty($producto["imagen"])
				? "<img src='".$producto["imagen"]."' width='40px'>"
				: "";

  			if($stockActual <= 0){
  				$stock = "<button class='btn btn-default'>0</button>";
  			}else if($stockActual <= 10){
  				$stock = "<button class='btn btn-danger'>".$stockActual."</button>";
  			}else if($stockActual <= 15){
  				$stock = "<button class='btn btn-warning'>".$stockActual."</button>";
  			}else{
  				$stock = "<button class='btn btn-success'>".$stockActual."</button>";
  			}

  			if($stockActual <= 0){
		  		$botones = "<button class='btn btn-default' disabled><i class='fa fa-ban'></i></button>";
  			}else{
		  		$botones = "<button class='btn btn-primary agregarProducto recuperarBoton' idProducto='".$producto["id"]."'>
		  		<i class='fa fa-cart-plus'></i>
		  		</button>";
  			}

			$data[] = [
				($i+1),
				$imagen,
				isset($producto["codigo"]) ? $producto["codigo"] : "",
				isset($producto["descripcion"]) ? $producto["descripcion"] : "",
				$stock,
				$botones
			];
  		}

		echo json_encode(["data" => $data]);
	}
}

$activarProductosVentas = new TablaProductosVentas();
$activarProductosVentas->mostrarTablaProductosVentas();