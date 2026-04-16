<?php
require_once "controladores/proveedores.controlador.php";

$item = null;
$valor = null;
$orden = "id";

$idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

/*=============================================
VENTAS (FILTRADAS POR SUCURSAL O GLOBAL)
=============================================*/
$ventas = ControladorVentas::ctrSumaTotalVentas();
$totalVentasSucursal = isset($ventas["total"]) ? (float)$ventas["total"] : 0;

/*=============================================
CATEGORIAS (GLOBAL)
=============================================*/
$categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);
$totalCategorias = is_array($categorias) ? count($categorias) : 0;

/*=============================================
CLIENTES (GLOBAL)
=============================================*/
$clientes = ControladorClientes::ctrMostrarClientes($item, $valor);
$totalClientes = is_array($clientes) ? count($clientes) : 0;

/*=============================================
PRODUCTOS (YA FILTRADOS POR SUCURSAL)
=============================================*/
$productos = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);
$totalProductos = is_array($productos) ? count($productos) : 0;

/*=============================================
PROVEEDORES (GLOBAL)
=============================================*/
$proveedores = ControladorProveedores::ctrMostrarProveedores(null, null);
$totalProveedores = is_array($proveedores) ? count($proveedores) : 0;

/*=============================================
GASTOS (FILTRADOS POR SUCURSAL O GLOBAL)
=============================================*/
$totalGastosSucursal = 0;
$gastos = ControladorGastos::ctrMostrarGastos(null, null);

if(is_array($gastos)){
  foreach($gastos as $gasto){

    if(!is_array($gasto)) continue;

    if($idSucursal > 0 && (int)$gasto["id_sucursal"] !== $idSucursal){
      continue;
    }

    $totalGastosSucursal += isset($gasto["monto"]) ? (float)$gasto["monto"] : 0;
  }
}
?>

<div class="col-lg-3 col-xs-6">

  <div class="small-box bg-aqua">
    
    <div class="inner">
      
      <h3>Q<?php echo number_format($totalVentasSucursal, 2); ?></h3>

      <p>Ventas</p>
    
    </div>
    
    <div class="icon">
      <i class="ion ion-cash"></i><span class="moneda">Q</span>
    </div>

    <a href="ventas" class="small-box-footer">
      Más info <i class="fa fa-arrow-circle-right"></i>
    </a>

  </div>

</div>

<div class="col-lg-3 col-xs-6">

  <div class="small-box bg-green">
    
    <div class="inner">
    
      <h3><?php echo number_format($totalCategorias); ?></h3>

      <p>Categorías</p>
    
    </div>
    
    <div class="icon">
      <i class="ion ion-clipboard"></i>
    </div>
    
    <a href="categorias" class="small-box-footer">
      Más info <i class="fa fa-arrow-circle-right"></i>
    </a>

  </div>

</div>

<div class="col-lg-3 col-xs-6">

  <div class="small-box bg-yellow">
    
    <div class="inner">
    
      <h3><?php echo number_format($totalClientes); ?></h3>

      <p>Clientes</p>
  
    </div>
    
    <div class="icon">
      <i class="ion ion-person-add"></i>
    </div>
    
    <a href="clientes" class="small-box-footer">
      Más info <i class="fa fa-arrow-circle-right"></i>
    </a>

  </div>

</div>

<div class="col-lg-3 col-xs-6">

  <div class="small-box bg-red">
  
    <div class="inner">
    
      <h3><?php echo number_format($totalProductos); ?></h3>

      <p>Productos</p>
    
    </div>
    
    <div class="icon">
      <i class="ion ion-ios-cart"></i>
    </div>
    
    <a href="productos" class="small-box-footer">
      Más info <i class="fa fa-arrow-circle-right"></i>
    </a>

  </div>

</div>

<div class="col-lg-3 col-xs-6">

  <div class="small-box bg-purple">
    <div class="inner">
      <h3><?php echo number_format($totalProveedores); ?></h3>
      <p>Proveedores</p>
    </div>
    <div class="icon">
      <i class="fa fa-truck"></i>
    </div>
    <a href="proveedores" class="small-box-footer">
      Más info <i class="fa fa-arrow-circle-right"></i>
    </a>
  </div>

</div>

<div class="col-lg-3 col-xs-6">

  <div class="small-box bg-brown">
    <div class="inner">
      <h3>Q<?php echo number_format($totalGastosSucursal, 2); ?></h3>
      <p>Gastos</p>
    </div>
    <div class="icon">
      <i class="fa fa-credit-card"></i>
    </div>
    <a href="gastos" class="small-box-footer">
      Más info <i class="fa fa-arrow-circle-right"></i>
    </a>
  </div>

</div>