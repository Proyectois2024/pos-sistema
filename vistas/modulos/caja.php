<div class="content-wrapper">
  <section class="content-header">
    <h1>Administrar Caja</h1>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Estado de Caja Actual</h3>
          </div>

          <?php
            $usuarioId = isset($_SESSION["id"]) ? (int)$_SESSION["id"] : 0;
            $caja = ControladorCaja::ctrObtenerCajaAbierta($usuarioId);

            if($caja && is_array($caja)):

              $ingresos = ModeloCaja::mdlSumarIngresosCaja("movimientos_caja", (int)$caja["id"]);
              $egresos = ModeloCaja::mdlSumarEgresosCaja("movimientos_caja", (int)$caja["id"]);
              $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;
$creditos = ModeloCaja::mdlSumarCreditosPendientes($idSucursal);

              $montoApertura = isset($caja["monto_apertura"]) ? (float)$caja["monto_apertura"] : 0;
              $totalIngresos = (is_array($ingresos) && isset($ingresos["total"])) ? (float)$ingresos["total"] : 0;
              $totalEgresos = (is_array($egresos) && isset($egresos["total"])) ? (float)$egresos["total"] : 0;
              $totalCreditos = (is_array($creditos) && isset($creditos["total"])) ? (float)$creditos["total"] : 0;

              $totalEfectivo = $montoApertura + $totalIngresos - $totalEgresos;

              if($totalEfectivo < 0){
                $totalEfectivo = 0;
              }
          ?>

          <div class="box-body">
            <div class="row">

              <div class="col-md-3">
                <div class="alert alert-info">
                  <h4><i class="icon fa fa-play"></i> Apertura</h4>
                  <h2>Q <?php echo number_format($montoApertura, 2); ?></h2>
                  <p>
                    <?php
                      if(!empty($caja["fecha_apertura"])){
                        echo htmlspecialchars($caja["fecha_apertura"], ENT_QUOTES, 'UTF-8');
                      }else{
                        echo 'Sin fecha';
                      }
                    ?>
                  </p>
                </div>
              </div>

              <div class="col-md-3">
                <div class="alert alert-success">
                  <h4><i class="icon fa fa-plus-circle"></i> Ingresos</h4>
                  <h2>Q <?php echo number_format($totalIngresos, 2); ?></h2>
                  <small>Ventas en efectivo, abonos y otros ingresos</small>
                </div>
              </div>

              <div class="col-md-3">
                <div class="alert alert-danger">
                  <h4><i class="icon fa fa-minus-circle"></i> Egresos</h4>
                  <h2>Q <?php echo number_format($totalEgresos, 2); ?></h2>
                  <small>Gastos, retiros y otros egresos</small>
                </div>
              </div>

              <div class="col-md-3">
                <div class="alert alert-warning">
                  <h4><i class="icon fa fa-calculator"></i> Esperado en Caja</h4>
                  <h2>Q <?php echo number_format($totalEfectivo, 2); ?></h2>
                  <small>Lo que debería haber físicamente</small>
                </div>
              </div>

              <div class="col-md-3">
                <div class="alert alert-primary" style="background:#337ab7; color:white;">
                  <h4><i class="icon fa fa-credit-card"></i> Créditos Pendientes</h4>
                  <h2>Q <?php echo number_format($totalCreditos, 2); ?></h2>
                  <small>Ventas a crédito aún no canceladas</small>
                </div>
              </div>

            </div>

            <hr>

            <div class="col-md-6 col-md-offset-3">
              <form method="post" class="bg-gray disabled color-palette" style="padding: 20px; border-radius: 10px;" autocomplete="off">
                <h3 class="text-center">Realizar Corte de Caja</h3>

                <div class="form-group">
                  <label>Efectivo Esperado por el Sistema:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calculator"></i></span>
                    <input type="text"
                           class="form-control input-lg"
                           value="Q <?php echo number_format($totalEfectivo, 2); ?>"
                           readonly>
                  </div>
                </div>

                <div class="form-group">
                  <label>Efectivo Real (Lo que contaste):</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-money"></i></span>
                    <input type="number"
                           step="0.01"
                           min="0"
                           class="form-control input-lg"
                           name="montoCierre"
                           id="montoCierre"
                           placeholder="¿Cuánto dinero hay físicamente?"
                           required>
                  </div>
                </div>

                <div class="form-group">
                  <label>Diferencia estimada:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-exchange"></i></span>
                    <input type="text"
                           class="form-control input-lg"
                           id="diferenciaPreview"
                           value="Q 0.00"
                           readonly>
                  </div>
                </div>

                <input type="hidden" name="idCaja" value="<?php echo (int)$caja["id"]; ?>">

                <button type="submit" class="btn btn-danger btn-block btn-lg">CERRAR CAJA</button>

                <?php
                  $cerrarCaja = new ControladorCaja();
                  $cerrarCaja->ctrCerrarCaja();
                ?>
              </form>
            </div>

          </div>

          <script>
            (function(){
              var esperado = <?php echo json_encode((float)$totalEfectivo); ?>;
              var inputMonto = document.getElementById("montoCierre");
              var inputDiferencia = document.getElementById("diferenciaPreview");

              if(inputMonto && inputDiferencia){
                inputMonto.addEventListener("input", function(){
                  var contado = parseFloat(this.value || 0);
                  var diferencia = contado - esperado;

                  inputDiferencia.value = "Q " + diferencia.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                  });
                });
              }
            })();
          </script>

          <?php else: ?>
            <div class="box-body">
              <div class="alert alert-danger text-center">
                <h4><i class="icon fa fa-ban"></i> Caja Cerrada</h4>
                <p>No hay ninguna caja abierta en este momento. Ve a "Crear Venta" para abrir una nueva.</p>
              </div>
            </div>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </section>
</div>