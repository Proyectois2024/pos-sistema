<?php

class ControladorVentas{

	/*=============================================
	MOSTRAR VENTAS
	=============================================*/
	static public function ctrMostrarVentas($item, $valor){

		$tabla = "ventas";
		$respuesta = ModeloVentas::mdlMostrarVentas($tabla, $item, $valor);

		return $respuesta;
	}

	/*=============================================
	OBTENER ID SUCURSAL ACTUAL
	=============================================*/
	static private function ctrObtenerIdSucursalActual(){

		return isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;
	}

	/*=============================================
	OBTENER ID SUCURSAL DE LA VENTA
	=============================================*/
	static private function ctrObtenerIdSucursalVenta($venta){

		if(is_array($venta) && isset($venta["id_sucursal"]) && (int)$venta["id_sucursal"] > 0){
			return (int)$venta["id_sucursal"];
		}

		return self::ctrObtenerIdSucursalActual();
	}

	/*=============================================
	CREAR VENTA
	=============================================*/
	static public function ctrCrearVenta(){

		if(isset($_POST["nuevaVenta"])){

			if(!isset($_POST["listaProductos"]) || trim($_POST["listaProductos"]) == ""){

				echo '<script>
					swal({
						type: "error",
						title: "La venta no se ejecuta si no hay productos",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {
							window.location = "ventas";
						}
					});
				</script>';

				return;
			}

			$idSucursal = self::ctrObtenerIdSucursalActual();

			if($idSucursal <= 0){
				echo '<script>
					swal({
						type: "error",
						title: "El usuario no tiene sucursal asignada",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {
							window.location = "ventas";
						}
					});
				</script>';
				return;
			}

			$caja = ControladorCaja::ctrObtenerCajaAbierta();

			if(!$caja){

				echo '<script>
					swal({
						type: "error",
						title: "No hay caja abierta en esta sucursal",
						text: "Debes abrir caja antes de realizar ventas",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {
							window.location = "caja";
						}
					});
				</script>';

				return;
			}

			$db = Conexion::conectar();

			try{

				$db->beginTransaction();

				$listaProductos = json_decode($_POST["listaProductos"], true);

				if(!is_array($listaProductos) || count($listaProductos) <= 0){
					throw new Exception("La lista de productos es inválida");
				}

				$totalProductosComprados = array();

				foreach ($listaProductos as $value) {

					if(!is_array($value)){
						throw new Exception("Hay productos inválidos en la venta");
					}

					if(!isset($value["id"]) || !isset($value["cantidad"]) || !isset($value["stock"])){
						throw new Exception("Hay productos inválidos en la venta");
					}

					$idProducto = (int)$value["id"];
					$cantidad = (float)$value["cantidad"];
					$stockNuevo = (float)$value["stock"];

					if($idProducto <= 0 || $cantidad <= 0){
						throw new Exception("Hay productos inválidos en la venta");
					}

					$totalProductosComprados[] = $cantidad;

					$tablaProductos = "productos";
					$item = "id";
					$valor = $idProducto;
					$orden = "id";

					$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, $item, $valor, $orden);

					if(!is_array($traerProducto)){
						throw new Exception("Uno de los productos no existe");
					}

					$stockSucursal = ModeloProductos::mdlObtenerStockSucursal($idProducto, $idSucursal);

					if(!$stockSucursal){
						throw new Exception("El producto ".$traerProducto["descripcion"]." no tiene stock configurado en esta sucursal");
					}

					$stockActualSucursal = isset($stockSucursal["stock"]) ? (float)$stockSucursal["stock"] : 0;

					if($cantidad > $stockActualSucursal){
						throw new Exception("Stock insuficiente para el producto: ".$traerProducto["descripcion"]);
					}

					$ventasActuales = isset($traerProducto["ventas"]) ? (float)$traerProducto["ventas"] : 0;

					$item1a = "ventas";
					$valor1a = $cantidad + $ventasActuales;
					$nuevasVentas = ModeloProductos::mdlActualizarProducto($tablaProductos, $item1a, $valor1a, $valor);

					if($nuevasVentas != "ok"){
						throw new Exception("No se pudo actualizar ventas del producto");
					}

					$nuevoStock = ModeloProductos::mdlActualizarStockSucursal($idProducto, $idSucursal, $stockNuevo);

					if($nuevoStock != "ok"){
						throw new Exception("No se pudo actualizar stock del producto en la sucursal");
					}
				}

				$tablaClientes = "clientes";
				$item = "id";
				$valor = isset($_POST["seleccionarCliente"]) ? (int)$_POST["seleccionarCliente"] : 0;

				if($valor > 0){

					$traerCliente = ModeloClientes::mdlMostrarClientes($tablaClientes, $item, $valor);

					if(is_array($traerCliente)){

						$comprasActualesCliente = isset($traerCliente["compras"]) ? (float)$traerCliente["compras"] : 0;

						$item1a = "compras";
						$valor1a = array_sum($totalProductosComprados) + $comprasActualesCliente;
						$comprasCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, $item1a, $valor1a, $valor);

						if($comprasCliente != "ok"){
							throw new Exception("No se pudo actualizar compras del cliente");
						}

						$item1b = "ultima_compra";
						$valor1b = app_now();
						$fechaCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, $item1b, $valor1b, $valor);

						if($fechaCliente != "ok"){
							throw new Exception("No se pudo actualizar última compra del cliente");
						}
					}
				}

				$tabla = "ventas";
				$metodoPagoRaw = isset($_POST["listaMetodoPago"]) ? trim($_POST["listaMetodoPago"]) : "";

				if(stripos($metodoPagoRaw, "Efectivo") !== false){

					$metodoPago = "Efectivo";
					$estadoPago = "pagado";
					$estadoCredito = "pagado";

				}elseif(stripos($metodoPagoRaw, "Transferencia") !== false){

					$metodoPago = "Transferencia";
					$estadoPago = "pagado";
					$estadoCredito = "pagado";

				}elseif(stripos($metodoPagoRaw, "Credito") !== false || stripos($metodoPagoRaw, "Crédito") !== false){

					$metodoPago = "Credito";
					$estadoPago = "pendiente";
					$estadoCredito = "pendiente";

				}else{

					throw new Exception("Método de pago inválido");
				}

				if($metodoPago === "Credito" && (!isset($_POST["seleccionarCliente"]) || (int)$_POST["seleccionarCliente"] <= 0)){
					throw new Exception("Debes seleccionar un cliente para ventas a crédito");
				}

				if($metodoPago === "Credito" && (!isset($_POST["nuevaFechaVencimiento"]) || trim($_POST["nuevaFechaVencimiento"]) == "")){
					throw new Exception("Debes seleccionar una fecha de vencimiento para ventas a crédito");
				}

				$fechaVencimiento = null;
				if(
					$metodoPago === "Credito" &&
					isset($_POST["nuevaFechaVencimiento"]) &&
					trim($_POST["nuevaFechaVencimiento"]) !== ""
				){
					$fechaVencimiento = trim($_POST["nuevaFechaVencimiento"]);
				}

				$datos = array(
					"id_vendedor" => isset($_POST["idVendedor"]) ? (int)$_POST["idVendedor"] : 0,
					"id_cliente" => isset($_POST["seleccionarCliente"]) ? (int)$_POST["seleccionarCliente"] : 0,
					"id_sucursal" => $idSucursal,
					"codigo" => isset($_POST["nuevaVenta"]) ? (int)$_POST["nuevaVenta"] : 0,
					"productos" => $_POST["listaProductos"],
					"impuesto" => isset($_POST["nuevoPrecioImpuesto"]) ? $_POST["nuevoPrecioImpuesto"] : 0,
					"neto" => isset($_POST["nuevoPrecioNeto"]) ? $_POST["nuevoPrecioNeto"] : 0,
					"total" => isset($_POST["totalVenta"]) ? $_POST["totalVenta"] : 0,
					"metodo_pago" => $metodoPago,
					"estado_pago" => $estadoPago,
					"estado_credito" => $estadoCredito,
					"fecha_vencimiento" => $fechaVencimiento
				);

				$respuesta = ModeloVentas::mdlIngresarVenta($tabla, $datos);

				if(!$respuesta || $respuesta == "error"){
					throw new Exception("No se pudo guardar la venta");
				}

				if($metodoPago === "Credito"){

					$datosCredito = array(
						"id_venta" => (int)$respuesta,
						"id_cliente" => isset($_POST["seleccionarCliente"]) ? (int)$_POST["seleccionarCliente"] : 0,
						"total_venta" => isset($_POST["totalVenta"]) ? $_POST["totalVenta"] : 0,
						"saldo_pendiente" => isset($_POST["totalVenta"]) ? $_POST["totalVenta"] : 0,
						"estado" => 1
					);

					$respCredito = ModeloVentas::mdlCrearCredito($datosCredito);

					if($respCredito != "ok"){
						throw new Exception("No se pudo crear el registro de crédito");
					}
				}

				/*=============================================
				ACTUALIZAR ESTADO DE COTIZACIÓN
				=============================================*/
				if(isset($_POST["idCotizacion"]) && (int)$_POST["idCotizacion"] > 0){

					$idCotizacion = (int)$_POST["idCotizacion"];

					$respEstado = ModeloCotizaciones::mdlActualizarEstadoCotizacion(
						"cotizaciones_pedidos",
						$idCotizacion,
						0
					);

					if($respEstado != "ok"){
						throw new Exception("No se pudo actualizar el estado de la cotización");
					}
				}

				/*=============================================
				REGISTRAR VENTA EN CAJA SOLO SI ES EFECTIVO
				=============================================*/
				if($metodoPago === "Efectivo"){

					$respCaja = ControladorCaja::ctrRegistrarMovimientoAutomatico(
						"venta",
						"Venta #".(isset($_POST["nuevaVenta"]) ? $_POST["nuevaVenta"] : ""),
						isset($_POST["totalVenta"]) ? $_POST["totalVenta"] : 0,
						"venta",
						(int)$respuesta
					);

					if($respCaja === "sin_caja"){
						throw new Exception("No hay una caja abierta para registrar la venta en efectivo");
					}

					if($respCaja !== "ok"){
						throw new Exception("No se pudo registrar el movimiento de caja");
					}
				}

				/*=============================================
				CREAR CONTROL SANITARIO AUTOMÁTICO
				=============================================*/
				foreach ($listaProductos as $value) {

					if(!is_array($value) || !isset($value["id"])){
						continue;
					}

					$tablaProductos = "productos";
					$item = "id";
					$valor = (int)$value["id"];
					$orden = "id";

					$productoDB = ModeloProductos::mdlMostrarProductos($tablaProductos, $item, $valor, $orden);

					if(is_array($productoDB) && !empty($productoDB["tipo_sanitario"])){

						$tablaSanitario = "control_sanitario";

						$datosSanitario = array(
							"id_animal" => 0,
							"id_venta" => $respuesta,
							"producto" => isset($productoDB["descripcion"]) ? $productoDB["descripcion"] : "",
							"tipo" => $productoDB["tipo_sanitario"],
							"dosis" => "1",
							"fecha_aplicacion" => app_now("Y-m-d"),
							"proxima_aplicacion" => null,
							"observaciones" => "Generado automáticamente por venta",
							"fecha_registro" => app_now()
						);

						$respSanitario = ModeloControlSanitario::mdlIngresarSanitario($tablaSanitario, $datosSanitario);

						if($respSanitario != "ok"){
							throw new Exception("No se pudo guardar control sanitario");
						}
					}
				}

				$db->commit();

				echo '<script>
					localStorage.removeItem("rango");
					swal({
						type: "success",
						title: "La venta ha sido guardada correctamente",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {
							window.location = "ventas";
						}
					});
				</script>';

			}catch(Exception $e){

				if($db->inTransaction()){
					$db->rollBack();
				}

				$mensajeError = addslashes($e->getMessage());

				echo '<script>
					swal({
						type: "error",
						title: "Error al guardar la venta",
						text: "'.$mensajeError.'",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {
							window.location = "ventas";
						}
					});
				</script>';
			}
		}
	}

	/*=============================================
	EDITAR VENTA
	=============================================*/
	static public function ctrEditarVenta(){

		if(isset($_POST["editarVenta"])){

			$tabla = "ventas";
			$item = "id";
			$valor = (int)$_POST["editarVenta"];

			$traerVenta = ModeloVentas::mdlMostrarVentas($tabla, $item, $valor);

			if(!$traerVenta || !is_array($traerVenta)){

				echo '<script>
					swal({
						type: "error",
						title: "La venta no existe o no pudo cargarse",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if(result.value){
							window.location = "ventas";
						}
					});
				</script>';

				return;
			}

			$idSucursalVenta = self::ctrObtenerIdSucursalVenta($traerVenta);

			if(!isset($_POST["listaProductos"]) || empty($_POST["listaProductos"])){

				$listaProductos = $traerVenta["productos"];
				$cambioProducto = false;

			}else{

				$listaProductos = $_POST["listaProductos"];
				$cambioProducto = true;
			}

			if($cambioProducto){

				$productosAntiguos = json_decode($traerVenta["productos"], true);

				if(is_array($productosAntiguos)){

					foreach ($productosAntiguos as $value) {

						if(!is_array($value) || !isset($value["id"]) || !isset($value["cantidad"])){
							continue;
						}

						$idProducto = (int)$value["id"];
						$cantidad = (float)$value["cantidad"];

						$producto = ModeloProductos::mdlMostrarProductos("productos", "id", $idProducto, "id");

						if(is_array($producto)){

							$stockSucursal = ModeloProductos::mdlObtenerStockSucursal($idProducto, $idSucursalVenta);

							if($stockSucursal){
								$stockActual = isset($stockSucursal["stock"]) ? (float)$stockSucursal["stock"] : 0;
								$nuevoStock = $stockActual + $cantidad;
								ModeloProductos::mdlActualizarStockSucursal($idProducto, $idSucursalVenta, $nuevoStock);
							}

							$ventasActuales = isset($producto["ventas"]) ? (float)$producto["ventas"] : 0;
							$nuevasVentas = $ventasActuales - $cantidad;

							if($nuevasVentas < 0){
								$nuevasVentas = 0;
							}

							ModeloProductos::mdlActualizarProducto("productos", "ventas", $nuevasVentas, $idProducto);
						}
					}

					$totalAntiguo = 0;

					foreach($productosAntiguos as $p){
						if(is_array($p) && isset($p["cantidad"])){
							$totalAntiguo += (float)$p["cantidad"];
						}
					}

					$clienteAnterior = ModeloClientes::mdlMostrarClientes("clientes", "id", $traerVenta["id_cliente"]);

					if(is_array($clienteAnterior)){

						$comprasActualesAnterior = isset($clienteAnterior["compras"]) ? (float)$clienteAnterior["compras"] : 0;
						$nuevasCompras = $comprasActualesAnterior - $totalAntiguo;

						if($nuevasCompras < 0){
							$nuevasCompras = 0;
						}

						ModeloClientes::mdlActualizarCliente("clientes", "compras", $nuevasCompras, $traerVenta["id_cliente"]);
					}
				}

				$productosNuevos = json_decode($listaProductos, true);

				if(is_array($productosNuevos)){

					foreach ($productosNuevos as $value) {

						if(!is_array($value) || !isset($value["id"]) || !isset($value["cantidad"])){
							continue;
						}

						$idProducto = (int)$value["id"];
						$cantidad = (float)$value["cantidad"];

						$producto = ModeloProductos::mdlMostrarProductos("productos", "id", $idProducto, "id");

						if(is_array($producto)){

							$stockSucursal = ModeloProductos::mdlObtenerStockSucursal($idProducto, $idSucursalVenta);

							if(!$stockSucursal){
								continue;
							}

							$stockActual = isset($stockSucursal["stock"]) ? (float)$stockSucursal["stock"] : 0;
							$nuevoStock = $stockActual - $cantidad;

							if($nuevoStock < 0){
								$nuevoStock = 0;
							}

							ModeloProductos::mdlActualizarStockSucursal($idProducto, $idSucursalVenta, $nuevoStock);

							$ventasActuales = isset($producto["ventas"]) ? (float)$producto["ventas"] : 0;
							$nuevasVentas = $ventasActuales + $cantidad;
							ModeloProductos::mdlActualizarProducto("productos", "ventas", $nuevasVentas, $idProducto);
						}
					}

					$totalNuevo = 0;

					foreach($productosNuevos as $p){
						if(is_array($p) && isset($p["cantidad"])){
							$totalNuevo += (float)$p["cantidad"];
						}
					}

					$idClienteNuevo = isset($_POST["seleccionarCliente"]) ? (int)$_POST["seleccionarCliente"] : 0;
					$clienteNuevo = ModeloClientes::mdlMostrarClientes("clientes", "id", $idClienteNuevo);

					if(is_array($clienteNuevo)){

						$comprasActualesNuevo = isset($clienteNuevo["compras"]) ? (float)$clienteNuevo["compras"] : 0;
						$nuevasCompras = $comprasActualesNuevo + $totalNuevo;
						ModeloClientes::mdlActualizarCliente("clientes", "compras", $nuevasCompras, $idClienteNuevo);
					}
				}
			}

			$metodoPagoRaw = isset($_POST["listaMetodoPago"]) ? trim($_POST["listaMetodoPago"]) : "";

			if(stripos($metodoPagoRaw, "Efectivo") !== false){

				$metodoPago = "Efectivo";
				$estadoPago = "pagado";
				$estadoCredito = "pagado";
				$fechaVencimiento = null;

			}elseif(stripos($metodoPagoRaw, "Transferencia") !== false){

				$metodoPago = "Transferencia";
				$estadoPago = "pagado";
				$estadoCredito = "pagado";
				$fechaVencimiento = null;

			}elseif(stripos($metodoPagoRaw, "Credito") !== false || stripos($metodoPagoRaw, "Crédito") !== false){

				$metodoPago = "Credito";

				$abonos = ModeloVentas::mdlSumarAbonosVenta("abonos", (int)$_POST["editarVenta"]);
				$totalAbonado = (is_array($abonos) && isset($abonos["total"])) ? (float)$abonos["total"] : 0;
				$totalVenta = isset($_POST["totalVenta"]) ? (float)$_POST["totalVenta"] : 0;

				if($totalAbonado <= 0){
					$estadoPago = "pendiente";
					$estadoCredito = "pendiente";
				}elseif($totalAbonado < $totalVenta){
					$estadoPago = "parcial";
					$estadoCredito = "pendiente";
				}else{
					$estadoPago = "pagado";
					$estadoCredito = "pagado";
				}

				$fechaVencimiento = (isset($_POST["nuevaFechaVencimiento"]) && !empty($_POST["nuevaFechaVencimiento"]))
					? $_POST["nuevaFechaVencimiento"]
					: (isset($traerVenta["fecha_vencimiento"]) ? $traerVenta["fecha_vencimiento"] : null);

			}else{

				$metodoPago = "Efectivo";
				$estadoPago = "pagado";
				$estadoCredito = "pagado";
				$fechaVencimiento = null;
			}

			$datos = array(
				"id" => (int)$_POST["editarVenta"],
				"id_vendedor" => isset($_POST["idVendedor"]) ? (int)$_POST["idVendedor"] : 0,
				"id_cliente" => isset($_POST["seleccionarCliente"]) ? (int)$_POST["seleccionarCliente"] : 0,
				"id_sucursal" => $idSucursalVenta,
				"productos" => $listaProductos,
				"impuesto" => isset($_POST["nuevoPrecioImpuesto"]) ? $_POST["nuevoPrecioImpuesto"] : 0,
				"neto" => isset($_POST["nuevoPrecioNeto"]) ? $_POST["nuevoPrecioNeto"] : 0,
				"total" => isset($_POST["totalVenta"]) ? $_POST["totalVenta"] : 0,
				"metodo_pago" => $metodoPago,
				"estado_pago" => $estadoPago,
				"estado_credito" => $estadoCredito,
				"fecha_vencimiento" => $fechaVencimiento
			);

			$respuesta = ModeloVentas::mdlEditarVenta($tabla, $datos);

			if($respuesta == "ok"){

				echo '<script>
					localStorage.removeItem("rango");
					swal({
						type: "success",
						title: "La venta ha sido editada correctamente",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {
							window.location = "ventas";
						}
					});
				</script>';
			}
		}
	}

	/*=============================================
	ELIMINAR VENTA
	=============================================*/
	static public function ctrEliminarVenta(){

		if(isset($_GET["idVenta"])){

			$tabla = "ventas";
			$idVenta = (int) $_GET["idVenta"];

			$traerVenta = ModeloVentas::mdlMostrarVentas($tabla, "id", $idVenta);

			if(!$traerVenta || !is_array($traerVenta)){

				echo '<script>
					swal({
						type: "error",
						title: "La venta no existe o ya fue eliminada",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if(result.value){
							window.location = "ventas";
						}
					});
				</script>';

				return;
			}

			$productos = json_decode($traerVenta["productos"], true);

			if(is_array($productos)){

				foreach($productos as $value){

					if(!isset($value["id"]) || !isset($value["cantidad"])){
						continue;
					}

					$item = "id";
					$valor = $value["id"];
					$orden = "id";

					$traerProducto = ModeloProductos::mdlMostrarProductos("productos", $item, $valor, $orden);

					if(is_array($traerProducto)){

						$item1a = "ventas";
						$valor1a = max(0, $traerProducto["ventas"] - $value["cantidad"]);

						ModeloProductos::mdlActualizarProducto("productos", $item1a, $valor1a, $valor);

						$item1b = "stock";
						$valor1b = $traerProducto["stock"] + $value["cantidad"];

						ModeloProductos::mdlActualizarProducto("productos", $item1b, $valor1b, $valor);
					}
				}
			}

			if(isset($traerVenta["id_cliente"])){

				$itemCliente = "id";
				$valorCliente = $traerVenta["id_cliente"];

				$traerCliente = ModeloClientes::mdlMostrarClientes("clientes", $itemCliente, $valorCliente);

				if(is_array($traerCliente)){

					$item1 = "compras";
					$valor1 = max(0, $traerCliente["compras"] - 1);

					ModeloClientes::mdlActualizarCliente("clientes", $item1, $valor1, $valorCliente);
				}
			}

			$respuesta = ModeloVentas::mdlEliminarVenta($tabla, $idVenta);

			if($respuesta == "ok"){

				echo '<script>
					swal({
						type: "success",
						title: "La venta ha sido borrada correctamente",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if(result.value){
							window.location = "ventas";
						}
					});
				</script>';

			}else{

				echo '<script>
					swal({
						type: "error",
						title: "No se pudo borrar la venta",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if(result.value){
							window.location = "ventas";
						}
					});
				</script>';
			}
		}
	}

	/*=============================================
	RANGO FECHAS
	=============================================*/
	static public function ctrRangoFechasVentas($fechaInicial, $fechaFinal){

		$tabla = "ventas";
		$respuesta = ModeloVentas::mdlRangoFechasVentas($tabla, $fechaInicial, $fechaFinal);

		$idSucursalSesion = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;
		$ventasFiltradas = array();

		if(is_array($respuesta)){
			foreach($respuesta as $venta){

				if(!is_array($venta)){
					continue;
				}

				if(isset($venta["estado"]) && (int)$venta["estado"] === 0){
					continue;
				}

				if($idSucursalSesion > 0){
					if(!isset($venta["id_sucursal"]) || (int)$venta["id_sucursal"] !== $idSucursalSesion){
						continue;
					}
				}

				$ventasFiltradas[] = $venta;
			}
		}

		return $ventasFiltradas;
	}

	/*=============================================
	DEVOLVER VENTA
	=============================================*/
	static public function ctrDevolverVenta(){

		if(isset($_GET["idDevolucion"])){

			$tabla = "ventas";
			$item = "id";
			$valor = (int)$_GET["idDevolucion"];
			$comentario = isset($_GET["comentario"]) ? $_GET["comentario"] : "";

			$traerVenta = ModeloVentas::mdlMostrarVentas($tabla, $item, $valor);

			if(!$traerVenta || !is_array($traerVenta)){
				return;
			}

			if(isset($traerVenta["estado"]) && (int)$traerVenta["estado"] === 0){
				return;
			}

			$idSucursalVenta = self::ctrObtenerIdSucursalVenta($traerVenta);
			$idSucursalSesion = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

			if($idSucursalSesion > 0 && $idSucursalVenta > 0 && $idSucursalSesion !== $idSucursalVenta){
				echo '<script>
					swal({
						type: "error",
						title: "No puedes devolver una venta de otra sucursal",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {
							window.location = "ventas";
						}
					});
				</script>';
				return;
			}

			$productos = json_decode($traerVenta["productos"], true);
			$totalProductosComprados = array();

			if(is_array($productos)){
				foreach ($productos as $value) {

					if(!is_array($value) || !isset($value["cantidad"]) || !isset($value["id"])){
						continue;
					}

					$totalProductosComprados[] = (float)$value["cantidad"];

					$tablaProductos = "productos";
					$itemProd = "id";
					$valorProd = (int)$value["id"];
					$orden = "id";

					$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, $itemProd, $valorProd, $orden);

					if(is_array($traerProducto)){

						$stockSucursal = ModeloProductos::mdlObtenerStockSucursal($valorProd, $idSucursalVenta);

						if($stockSucursal){
							$stockActual = isset($stockSucursal["stock"]) ? (float)$stockSucursal["stock"] : 0;
							$valor1b = (float)$value["cantidad"] + $stockActual;
							ModeloProductos::mdlActualizarStockSucursal($valorProd, $idSucursalVenta, $valor1b);
						}

						$ventasActuales = isset($traerProducto["ventas"]) ? (float)$traerProducto["ventas"] : 0;

						$valor1c = $ventasActuales - (float)$value["cantidad"];
						if($valor1c < 0){
							$valor1c = 0;
						}

						ModeloProductos::mdlActualizarProducto($tablaProductos, "ventas", $valor1c, $valorProd);
					}
				}
			}

			$tablaClientes = "clientes";
			$valorCliente = $traerVenta["id_cliente"];
			$traerCliente = ModeloClientes::mdlMostrarClientes($tablaClientes, "id", $valorCliente);

			if(is_array($traerCliente)){

				$comprasActuales = isset($traerCliente["compras"]) ? (float)$traerCliente["compras"] : 0;
				$valor1a = $comprasActuales - array_sum($totalProductosComprados);

				if($valor1a < 0){
					$valor1a = 0;
				}

				ModeloClientes::mdlActualizarCliente($tablaClientes, "compras", $valor1a, $valorCliente);
			}

			$datos = array(
				"id" => $valor,
				"comentario" => $comentario,
				"estado" => 0
			);

			$respuesta = ModeloVentas::mdlActualizarVentaDevolucion($tabla, $datos);

			if($respuesta == "ok"){

				$comentarioSeguro = addslashes($comentario);

				echo '<script>
					swal({
						type: "success",
						title: "Devolución procesada correctamente",
						text: "Motivo: '.$comentarioSeguro.'",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {
							window.location = "ventas";
						}
					});
				</script>';
			}
		}
	}

	/*=============================================
	DESCARGAR EXCEL
	=============================================*/
	public function ctrDescargarReporte(){

		if(isset($_GET["reporte"])){

			$ventasFiltradas = self::ctrRangoFechasVentas(null, null);

			$Name = $_GET["reporte"].'.xls';

			header('Expires: 0');
			header('Cache-control: private');
			header("Content-type: application/vnd.ms-excel");
			header("Cache-Control: cache, must-revalidate");
			header('Content-Description: File Transfer');
			header('Last-Modified: '.date('D, d M Y H:i:s'));
			header("Pragma: public");
			header('Content-Disposition: attachment; filename="'.$Name.'"');
			header("Content-Transfer-Encoding: binary");

			echo "<table border='1'>
				<tr>
					<td style='font-weight:bold; border:1px solid #eee;'>SUCURSAL</td>
					<td style='font-weight:bold; border:1px solid #eee;'>CÓDIGO</td>
					<td style='font-weight:bold; border:1px solid #eee;'>CLIENTE</td>
					<td style='font-weight:bold; border:1px solid #eee;'>VENDEDOR</td>
					<td style='font-weight:bold; border:1px solid #eee;'>PAGO</td>
					<td style='font-weight:bold; border:1px solid #eee;'>ESTADO</td>
					<td style='font-weight:bold; border:1px solid #eee;'>NETO</td>
					<td style='font-weight:bold; border:1px solid #eee;'>TOTAL</td>
					<td style='font-weight:bold; border:1px solid #eee;'>FECHA</td>
				</tr>";

			if(is_array($ventasFiltradas)){

				foreach ($ventasFiltradas as $item){

					if(!is_array($item)){
						continue;
					}

					$cliente = ControladorClientes::ctrMostrarClientes("id", $item["id_cliente"]);
					$vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $item["id_vendedor"]);

					$nombreSucursal = "N/A";
					if(isset($item["id_sucursal"]) && (int)$item["id_sucursal"] > 0){
						$stmt = Conexion::conectar()->prepare("SELECT nombre FROM sucursales WHERE id = :id LIMIT 1");
						$stmt->bindParam(":id", $item["id_sucursal"], PDO::PARAM_INT);
						$stmt->execute();
						$suc = $stmt->fetch(PDO::FETCH_ASSOC);

						if($suc && isset($suc["nombre"])){
							$nombreSucursal = $suc["nombre"];
						}
					}

					$estado = ((int)$item["estado"] === 1) ? "Activa" : "Devuelta";

					echo "<tr>
						<td style='border:1px solid #eee;'>".$nombreSucursal."</td>
						<td style='border:1px solid #eee;'>".$item["codigo"]."</td>
						<td style='border:1px solid #eee;'>".(isset($cliente["nombre"]) ? $cliente["nombre"] : "")."</td>
						<td style='border:1px solid #eee;'>".(isset($vendedor["nombre"]) ? $vendedor["nombre"] : "")."</td>
						<td style='border:1px solid #eee;'>".$item["metodo_pago"]."</td>
						<td style='border:1px solid #eee;'>".$estado."</td>
						<td style='border:1px solid #eee;'>Q ".number_format((float)$item["neto"], 2)."</td>
						<td style='border:1px solid #eee;'>Q ".number_format((float)$item["total"], 2)."</td>
						<td style='border:1px solid #eee;'>".$item["fecha"]."</td>
					</tr>";
				}
			}

			echo "</table>";
		}
	}

	/*=============================================
	SUMA TOTAL VENTAS
	=============================================*/
	static public function ctrSumaTotalVentas(){

		$tabla = "ventas";
		return ModeloVentas::mdlSumaTotalVentas($tabla);
	}

	/*=============================================
	SUMAR ABONOS
	=============================================*/
	static public function ctrSumarAbonosVenta($idVenta){

		$tabla = "abonos";
		$respuesta = ModeloVentas::mdlSumarAbonosVenta($tabla, $idVenta);

		return $respuesta;
	}

	/*=============================================
	CREAR ABONO
	=============================================*/
	public function ctrCrearAbono(){

		if(isset($_POST["nuevoAbono"])){

			$db = Conexion::conectar();

			try{

				$db->beginTransaction();

				$tabla = "abonos";
				$idVenta = isset($_POST["idVentaAbono"]) ? (int)$_POST["idVentaAbono"] : 0;
				$montoAbono = isset($_POST["nuevoAbono"]) ? (float)$_POST["nuevoAbono"] : 0;

				if($idVenta <= 0){
					throw new Exception("Venta inválida");
				}

				if($montoAbono <= 0){
					throw new Exception("El monto del abono debe ser mayor a cero");
				}

				$venta = ModeloVentas::mdlMostrarVentas("ventas", "id", $idVenta);

				if(!$venta || !is_array($venta)){
					throw new Exception("La venta no existe");
				}

				if(!isset($venta["estado"]) || (int)$venta["estado"] !== 1){
					throw new Exception("No se puede abonar una venta anulada");
				}

				if(!isset($venta["metodo_pago"]) || $venta["metodo_pago"] !== "Credito"){
					throw new Exception("Solo se pueden registrar abonos a ventas a crédito");
				}

				$abonos = ModeloVentas::mdlSumarAbonosVenta("abonos", $idVenta);
				$totalAbonadoActual = (is_array($abonos) && isset($abonos["total"])) ? (float)$abonos["total"] : 0;
				$totalVenta = isset($venta["total"]) ? (float)$venta["total"] : 0;
				$saldoPendiente = $totalVenta - $totalAbonadoActual;

				if($saldoPendiente <= 0){
					throw new Exception("Esta venta ya está totalmente pagada");
				}

				if($montoAbono > $saldoPendiente){
					throw new Exception("El abono no puede ser mayor al saldo pendiente");
				}

				$datos = array(
					"id_venta" => $idVenta,
					"monto" => $montoAbono,
					"fecha" => app_now()
				);

				$respuesta = ModeloVentas::mdlIngresarAbono($tabla, $datos);

				if($respuesta != "ok"){
					throw new Exception("No se pudo guardar el abono");
				}

				$respEstado = ModeloVentas::mdlActualizarEstadoCredito("ventas", $idVenta);

				if($respEstado != "ok"){
					throw new Exception("No se pudo actualizar el estado de la venta");
				}

				/*=============================================
				REGISTRAR ABONO EN CAJA
				=============================================*/
				$respCaja = ControladorCaja::ctrRegistrarMovimientoAutomatico(
					"abono",
					"Abono a venta ID ".$idVenta,
					$montoAbono,
					"abono",
					$idVenta
				);

				if($respCaja === "sin_caja"){
					throw new Exception("No hay una caja abierta para registrar el abono");
				}

				if($respCaja !== "ok"){
					throw new Exception("No se pudo registrar el movimiento de caja");
				}

				$db->commit();

				echo '<script>
					swal({
						type: "success",
						title: "El abono ha sido registrado correctamente",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {
							window.location = "ventas";
						}
					});
				</script>';

			}catch(Exception $e){

				if($db->inTransaction()){
					$db->rollBack();
				}

				$mensajeError = addslashes($e->getMessage());

				echo '<script>
					swal({
						type: "error",
						title: "Error al registrar abono",
						text: "'.$mensajeError.'",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {
							window.location = "ventas";
						}
					});
				</script>';
			}

			
		}
	}
	/*=============================================
	AUDITORIA DE VENTAS (FILTRADO COMPLETO)
	=============================================*/
	static public function ctrAuditoriaVentas(){

		$tabla = "ventas";

		$filtros = array(
			"id_sucursal"   => isset($_GET["aud_sucursal"]) ? $_GET["aud_sucursal"] : "",
			"fecha_inicial" => isset($_GET["aud_fecha_inicial"]) ? $_GET["aud_fecha_inicial"] : "",
			"fecha_final"   => isset($_GET["aud_fecha_final"]) ? $_GET["aud_fecha_final"] : "",
			"estado"        => isset($_GET["aud_estado"]) ? $_GET["aud_estado"] : "",
			"metodo_pago"   => isset($_GET["aud_metodo_pago"]) ? $_GET["aud_metodo_pago"] : "",
			"id_vendedor"   => isset($_GET["aud_vendedor"]) ? $_GET["aud_vendedor"] : "",
			"id_cliente"    => isset($_GET["aud_cliente"]) ? $_GET["aud_cliente"] : ""
		);

		$respuesta = ModeloVentas::mdlAuditoriaVentas($tabla, $filtros);

		return $respuesta;
	}
}
