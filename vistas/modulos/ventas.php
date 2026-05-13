<?php

if($_SESSION["perfil"] == "Especial"){
  echo '<script>
    window.location = "inicio";
  </script>';
  return;
}

?>
<div class="content-wrapper">

  <section class="content-header">
    <h1>Administrar ventas</h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar ventas</li>
    </ol>
  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
        <a href="crear-venta">
          <button class="btn btn-primary">Agregar venta</button>
        </a>

         <button type="button" class="btn btn-default pull-right" id="daterange-btn">
            <span>
              <i class="fa fa-calendar"></i> Rango de fecha
            </span>
            <i class="fa fa-caret-down"></i>
         </button>
      </div>

      <div class="box-body">
        
       <table class="table table-bordered table-striped dt-responsive tablas" width="100%">
         
        <thead>
         <tr>
           <th style="width:10px">#</th>
           <th>Código factura</th>
           <th>Cliente</th>
           <th>Vendedor</th>
           <th>Forma de pago</th>
           <th>Neto</th>
           <th>Total</th> 
           <th>Fecha</th>
           <th>Acciones</th>
         </tr> 
        </thead>

        <tbody>

        <?php

          if (isset($_GET["fechaInicial"])) {
            $fechaInicial = $_GET["fechaInicial"];
            $fechaFinal = $_GET["fechaFinal"];
          } else {
            $fechaInicial = null;
            $fechaFinal = null;
          }

          $respuesta = ControladorVentas::ctrRangoFechasVentas($fechaInicial, $fechaFinal);

          $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

$contador = 0;

$contador = 0;

foreach ($respuesta as $value) {

  // 🔒 FILTRO POR SUCURSAL
  if($idSucursal > 0 && isset($value["id_sucursal"]) && $value["id_sucursal"] != $idSucursal){
    continue;
  }

  $contador++;

  $esDevolucion = (isset($value["estado"]) && $value["estado"] == 0);
  $claseFila = $esDevolucion ? 'style="background-color: #f9f9f9; color: #999;"' : '';

  echo '<tr '.$claseFila.'>
          <td>' . $contador . '</td>
          <td>' . $value["codigo"] . '</td>';

  // Cliente
  $cliente = ControladorClientes::ctrMostrarClientes("id", $value["id_cliente"]);
  echo '<td>' . ($cliente["nombre"] ?? "N/A") . '</td>';

  // Vendedor
  $vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $value["id_vendedor"]);
  echo '<td>' . ($vendedor["nombre"] ?? "N/A") . '</td>';

  // Método de pago
 if($value["metodo_pago"] == "Credito"){

  if($value["estado_credito"] == "pagado"){

    echo '<td>
            <button class="btn btn-xs btn-success">
              Crédito Pagado
            </button>
          </td>';

  }elseif($value["estado_credito"] == "parcial"){

    echo '<td>
            <button class="btn btn-xs btn-warning">
              Crédito Parcial
            </button>
          </td>';

  }else{

    echo '<td>
            <button class="btn btn-xs btn-danger">
              Crédito Pendiente
            </button>
          </td>';
  }

}else{

  echo '<td>'.$value["metodo_pago"].'</td>';
}

  echo '<td>Q '.number_format($value["neto"],2).'</td>
        <td>Q '.number_format($value["total"],2).'</td>
        <td>'.$value["fecha"].'</td>
        <td><div class="btn-group">';

  echo '<button class="btn btn-info btnImprimirFactura" codigoVenta="'.$value["codigo"].'">
          <i class="fa fa-print"></i>
        </button>';

  if($_SESSION["perfil"] == "Administrador"){

    if(!$esDevolucion){

      echo '<button class="btn btn-warning btnEditarVenta" idVenta="'.$value["id"].'">
              <i class="fa fa-pencil"></i>
            </button>';

      echo '<button class="btn btn-default btnDevolucionVenta" idVenta="'.$value["id"].'">
              <i class="fa fa-reply"></i>
            </button>';

      if($value["metodo_pago"] == "Credito"){
        echo '<button class="btn btn-success btnGestionarCredito" idVenta="'.$value["id"].'">
                <i class="fa fa-money"></i>
              </button>';
      }

      echo '<button class="btn btn-danger btnEliminarVenta" idVenta="'.$value["id"].'">
              <i class="fa fa-times"></i>
            </button>';

    } else {

      echo '<span class="label label-danger">Devuelta</span>';

      if(!empty($value["comentario"])){
        echo '<br><small><b>Motivo:</b> '.$value["comentario"].'</small>';
      }
    }
  }

  echo '</div></td></tr>';
}
        ?>
               
        </tbody>

       </table>

       <?php
        $eliminarVenta = new ControladorVentas();
        $eliminarVenta -> ctrEliminarVenta();
        $devolverVenta = new ControladorVentas();
        $devolverVenta -> ctrDevolverVenta();
      ?>
       
      </div>

    </div>

  </section>

  <?php
if(isset($_SESSION["productosSanitarios"])){

    echo '<script>
        var productosSanitarios = '.json_encode($_SESSION["productosSanitarios"]).';
        var idVentaSanitaria = '.$_SESSION["idVentaSanitaria"].';
    </script>';

    unset($_SESSION["productosSanitarios"]);
    unset($_SESSION["idVentaSanitaria"]);
}
?>

</div>
