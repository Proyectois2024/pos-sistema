<div class="content-wrapper">
  <section class="content-header">
    <h1>Cuentas por Cobrar</h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Cuentas por Cobrar</li>
    </ol>
  </section>

  <section class="content">
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title">Seguimiento de Ventas a Crédito</h3>
      </div>

      <div class="box-body">
        <table class="table table-bordered table-striped dt-responsive tablas" width="100%">
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Código</th>
              <th>Cliente</th>
              <th>Total</th>
              <th>Saldo</th>
              <th>Vencimiento</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php
            $item = "estado_credito";
            $valor = "pendiente";
            $respuestaVentas = ControladorVentas::ctrMostrarVentas($item, $valor);
            $ventas = (is_array($respuestaVentas)) ? $respuestaVentas : array();
            $contador = 1;

            foreach ($ventas as $key => $value) {

              if(!is_array($value)){ 
                continue; 
              }

              if(!isset($value["metodo_pago"]) || $value["metodo_pago"] !== "Credito"){ 
                continue; 
              }

              if(!isset($value["estado"]) || (int)$value["estado"] !== 1){ 
                continue; 
              }

              if(!isset($value["id"])){ 
                continue; 
              }

              // Obtener cliente
              $respuestaCliente = ControladorClientes::ctrMostrarClientes("id", $value["id_cliente"]);
              $nombreCliente = (is_array($respuestaCliente) && isset($respuestaCliente["nombre"]) && !empty($respuestaCliente["nombre"]))
                ? $respuestaCliente["nombre"]
                : "Cliente General";

              // Obtener abonos
              $resAbonos = ControladorVentas::ctrSumarAbonosVenta($value["id"]);
              $pagado = 0;

              if(is_array($resAbonos) && isset($resAbonos["total"]) && is_numeric($resAbonos["total"])){
                $pagado = (float)$resAbonos["total"];
              }

              // Totales
              $vTotal = isset($value["total"]) ? (float)$value["total"] : 0;
              $vPagado = (float)$pagado;
              $saldo = $vTotal - $vPagado;

              if($saldo < 0){
                $saldo = 0;
              }

              // Estado visual
              $estadoMostrar = isset($value["estado_pago"]) && !empty($value["estado_pago"])
                ? $value["estado_pago"]
                : "pendiente";
          ?>
            <tr>
              <td><?php echo $contador; ?></td>
              <td><?php echo isset($value["codigo"]) ? htmlspecialchars($value["codigo"], ENT_QUOTES, 'UTF-8') : ''; ?></td>
              <td><?php echo htmlspecialchars($nombreCliente, ENT_QUOTES, 'UTF-8'); ?></td>
              <td>Q <?php echo number_format($vTotal, 2); ?></td>

              <td class="<?php echo ($saldo > 0) ? 'text-red' : 'text-green'; ?>">
                <strong>Q <?php echo number_format($saldo, 2); ?></strong>
              </td>

              <td>
                <?php
                  if(
                    !empty($value["fecha_vencimiento"]) &&
                    $value["fecha_vencimiento"] != "0000-00-00" &&
                    $value["fecha_vencimiento"] != "0000-00-00 00:00:00"
                  ){
                    $timestampVencimiento = strtotime($value["fecha_vencimiento"]);
                    if($timestampVencimiento){
                      echo date("d/m/Y H:i", $timestampVencimiento);
                    }else{
                      echo '<span class="text-muted">Fecha inválida</span>';
                    }
                  }else{
                    echo '<span class="text-muted">Sin fecha</span>';
                  }
                ?>
              </td>

              <td>
                <?php
                  if($estadoMostrar === "pendiente"){
                    echo '<span class="label label-danger">pendiente</span>';
                  }elseif($estadoMostrar === "parcial"){
                    echo '<span class="label label-warning">parcial</span>';
                  }elseif($estadoMostrar === "pagado"){
                    echo '<span class="label label-success">pagado</span>';
                  }else{
                    echo '<span class="label label-default">'.htmlspecialchars($estadoMostrar, ENT_QUOTES, 'UTF-8').'</span>';
                  }
                ?>
              </td>

              <td>
                <div class="btn-group">
                  <button class="btn btn-info btnImprimirFactura" codigoVenta="<?php echo isset($value["codigo"]) ? htmlspecialchars($value["codigo"], ENT_QUOTES, 'UTF-8') : ''; ?>">
                    <i class="fa fa-print"></i>
                  </button>

                  <?php if($saldo > 0): ?>
                    <button class="btn btn-success btnAbonar"
                            idVenta="<?php echo (int)$value["id"]; ?>"
                            saldoPendiente="<?php echo number_format($saldo, 2, '.', ''); ?>"
                            data-toggle="modal"
                            data-target="#modalAbonar">
                      <i class="fa fa-money"></i> Abonar
                    </button>
                  <?php else: ?>
                    <button class="btn btn-default" disabled>
                      <i class="fa fa-check"></i> Cancelada
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php
              $contador++;
            }
          ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>

<div id="modalAbonar" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post" autocomplete="off">
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Registrar Abono</h4>
        </div>

        <div class="modal-body">
          <div class="box-body">

            <input type="hidden" name="idVentaAbono" id="idVentaAbono">

            <div class="form-group">
              <label>Saldo Pendiente:</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calculator"></i></span>
                <input type="text" class="form-control input-lg" id="mostrarSaldo" readonly>
              </div>
            </div>

            <div class="form-group">
              <label>Monto a Abonar:</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-money"></i></span>
                <input type="number"
                       step="0.01"
                       min="0.01"
                       class="form-control input-lg"
                       name="nuevoAbono"
                       id="nuevoAbono"
                       placeholder="Ingresar monto"
                       required>
              </div>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar Abono</button>
        </div>

        <?php
          $crearAbono = new ControladorVentas();
          $crearAbono->ctrCrearAbono();
        ?>
      </form>
    </div>
  </div>
</div>

<script>
$(document).on("click", ".btnAbonar", function(){

    var idVenta = $(this).attr("idVenta");
    var saldoPendiente = parseFloat($(this).attr("saldoPendiente")) || 0;

    $("#idVentaAbono").val(idVenta);
    $("#mostrarSaldo").val("Q " + saldoPendiente.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }));
    $("#nuevoAbono").attr("max", saldoPendiente.toFixed(2));
    $("#nuevoAbono").val("");
});

$(document).on("submit", "form", function(){

    var monto = parseFloat($("#nuevoAbono").val()) || 0;
    var maximo = parseFloat($("#nuevoAbono").attr("max")) || 0;

    if(monto <= 0){
        alert("El monto del abono debe ser mayor a cero");
        return false;
    }

    if(maximo > 0 && monto > maximo){
        alert("El abono no puede ser mayor al saldo pendiente");
        return false;
    }

    return true;
});
</script>

<?php if(isset($_GET["idVenta"])): ?>
<script>
$(document).ready(function(){

   var idVenta = "<?php echo (int)$_GET["idVenta"]; ?>";
   var boton = $('.btnAbonar[idVenta="' + idVenta + '"]');

   if(boton.length){
      boton.trigger("click");
   }

});
</script>
<?php endif; ?>