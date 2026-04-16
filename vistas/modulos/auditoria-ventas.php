<?php

if($_SESSION["perfil"] != "Administrador"){
  echo '<script>window.location = "inicio";</script>';
  return;
}

$stmtSuc = Conexion::conectar()->prepare("SELECT id, nombre, codigo FROM sucursales WHERE estado = 1 ORDER BY nombre ASC");
$stmtSuc->execute();
$sucursales = $stmtSuc->fetchAll(PDO::FETCH_ASSOC);

$vendedores = ControladorUsuarios::ctrMostrarUsuarios(null, null);
$clientes = ControladorClientes::ctrMostrarClientes(null, null);

$ventas = array();

if(isset($_GET["ruta"]) && $_GET["ruta"] == "auditoria-ventas"){
    $ventas = ControladorVentas::ctrAuditoriaVentas();
}

$totalVentas = 0;
$totalNeto = 0;
$totalRegistros = 0;
$totalAnuladas = 0;

if(is_array($ventas)){
  foreach($ventas as $venta){
    if(!is_array($venta)) continue;

    $totalRegistros++;

    if(isset($venta["estado"]) && (int)$venta["estado"] === 0){
      $totalAnuladas++;
    }

    $totalVentas += isset($venta["total"]) ? (float)$venta["total"] : 0;
    $totalNeto += isset($venta["neto"]) ? (float)$venta["neto"] : 0;
  }
}
?>

<div class="content-wrapper">

  <section class="content-header">
    <h1>Auditoría de ventas</h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Auditoría de ventas</li>
    </ol>
  </section>

  <section class="content">

    <div class="row">

      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-aqua">
          <div class="inner">
            <h3><?php echo number_format($totalRegistros); ?></h3>
            <p>Registros</p>
          </div>
          <div class="icon"><i class="fa fa-list"></i></div>
        </div>
      </div>

      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-green">
          <div class="inner">
            <h3>Q<?php echo number_format($totalVentas, 2); ?></h3>
            <p>Total ventas</p>
          </div>
          <div class="icon"><i class="fa fa-money"></i></div>
        </div>
      </div>

      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-yellow">
          <div class="inner">
            <h3>Q<?php echo number_format($totalNeto, 2); ?></h3>
            <p>Total neto</p>
          </div>
          <div class="icon"><i class="fa fa-line-chart"></i></div>
        </div>
      </div>

      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-red">
          <div class="inner">
            <h3><?php echo number_format($totalAnuladas); ?></h3>
            <p>Anuladas/devueltas</p>
          </div>
          <div class="icon"><i class="fa fa-times-circle"></i></div>
        </div>
      </div>

    </div>

    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title">Filtros</h3>
      </div>

      <form method="get" action="index.php">
<input type="hidden" name="ruta" value="auditoria-ventas">

        <div class="box-body">
          <div class="row">

            <div class="col-md-2">
              <label>Sucursal</label>
              <select class="form-control" name="aud_sucursal">
                <option value="">Todas</option>
                <?php
                if(is_array($sucursales)){
                  foreach($sucursales as $s){
                    $selected = (isset($_GET["aud_sucursal"]) && $_GET["aud_sucursal"] == $s["id"]) ? "selected" : "";
                    echo '<option value="'.$s["id"].'" '.$selected.'>'.$s["nombre"].' - '.$s["codigo"].'</option>';
                  }
                }
                ?>
              </select>
            </div>

            <div class="col-md-2">
              <label>Fecha inicial</label>
              <input type="date" class="form-control" name="aud_fecha_inicial" value="<?php echo isset($_GET["aud_fecha_inicial"]) ? $_GET["aud_fecha_inicial"] : ""; ?>">
            </div>

            <div class="col-md-2">
              <label>Fecha final</label>
              <input type="date" class="form-control" name="aud_fecha_final" value="<?php echo isset($_GET["aud_fecha_final"]) ? $_GET["aud_fecha_final"] : ""; ?>">
            </div>

            <div class="col-md-2">
              <label>Estado</label>
              <select class="form-control" name="aud_estado">
                <option value="">Todos</option>
                <option value="1" <?php echo (isset($_GET["aud_estado"]) && $_GET["aud_estado"] === "1") ? "selected" : ""; ?>>Activas</option>
                <option value="0" <?php echo (isset($_GET["aud_estado"]) && $_GET["aud_estado"] === "0") ? "selected" : ""; ?>>Devueltas</option>
              </select>
            </div>

            <div class="col-md-2">
              <label>Método pago</label>
              <select class="form-control" name="aud_metodo_pago">
                <option value="">Todos</option>
                <option value="Efectivo" <?php echo (isset($_GET["aud_metodo_pago"]) && $_GET["aud_metodo_pago"] === "Efectivo") ? "selected" : ""; ?>>Efectivo</option>
                <option value="Transferencia" <?php echo (isset($_GET["aud_metodo_pago"]) && $_GET["aud_metodo_pago"] === "Transferencia") ? "selected" : ""; ?>>Transferencia</option>
                <option value="Credito" <?php echo (isset($_GET["aud_metodo_pago"]) && $_GET["aud_metodo_pago"] === "Credito") ? "selected" : ""; ?>>Crédito</option>
              </select>
            </div>

            <div class="col-md-2">
              <label>Vendedor</label>
              <select class="form-control" name="aud_vendedor">
                <option value="">Todos</option>
                <?php
                if(is_array($vendedores)){
                  foreach($vendedores as $v){
                    $selected = (isset($_GET["aud_vendedor"]) && $_GET["aud_vendedor"] == $v["id"]) ? "selected" : "";
                    echo '<option value="'.$v["id"].'" '.$selected.'>'.$v["nombre"].'</option>';
                  }
                }
                ?>
              </select>
            </div>

          </div>

          <div class="row" style="margin-top:15px;">

            <div class="col-md-4">
              <label>Cliente</label>
              <select class="form-control" name="aud_cliente">
                <option value="">Todos</option>
                <?php
                if(is_array($clientes)){
                  foreach($clientes as $c){
                    $selected = (isset($_GET["aud_cliente"]) && $_GET["aud_cliente"] == $c["id"]) ? "selected" : "";
                    echo '<option value="'.$c["id"].'" '.$selected.'>'.$c["nombre"].'</option>';
                  }
                }
                ?>
              </select>
            </div>
            

            <div class="col-md-8" style="padding-top:25px;">
              <button type="submit" class="btn btn-primary">Filtrar</button>
              <a href="auditoria-ventas" class="btn btn-default">Limpiar</a>
            </div>

          </div>
        </div>
      </form>
    </div>

    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title">Resultado</h3>
      </div>

      <div class="box-body">
        <table class="table table-bordered table-striped dt-responsive tablas" width="100%">
          <thead>
            <tr>
              <th>#</th>
              <th>Sucursal</th>
              <th>Código</th>
              <th>Cliente</th>
              <th>Vendedor</th>
              <th>Pago</th>
              <th>Estado</th>
              <th>Neto</th>
              <th>Total</th>
              <th>Fecha</th>
            </tr>
          </thead>
          <tbody>

            <?php
            if(is_array($ventas)){
              foreach($ventas as $key => $value){

                $cliente = ControladorClientes::ctrMostrarClientes("id", $value["id_cliente"]);
                $vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $value["id_vendedor"]);

                $nombreSucursal = "N/A";
                if(isset($value["id_sucursal"]) && (int)$value["id_sucursal"] > 0){
                  $stmtNom = Conexion::conectar()->prepare("SELECT nombre FROM sucursales WHERE id = :id LIMIT 1");
                  $stmtNom->bindParam(":id", $value["id_sucursal"], PDO::PARAM_INT);
                  $stmtNom->execute();
                  $suc = $stmtNom->fetch(PDO::FETCH_ASSOC);
                  if($suc && isset($suc["nombre"])){
                    $nombreSucursal = $suc["nombre"];
                  }
                }

                $estadoLabel = ((int)$value["estado"] === 1)
                  ? '<span class="label label-success">Activa</span>'
                  : '<span class="label label-danger">Devuelta</span>';

                echo '<tr>
                        <td>'.($key+1).'</td>
                        <td>'.$nombreSucursal.'</td>
                        <td>'.$value["codigo"].'</td>
                        <td>'.($cliente["nombre"] ?? "N/A").'</td>
                        <td>'.($vendedor["nombre"] ?? "N/A").'</td>
                        <td>'.$value["metodo_pago"].'</td>
                        <td>'.$estadoLabel.'</td>
                        <td>Q '.number_format((float)$value["neto"], 2).'</td>
                        <td>Q '.number_format((float)$value["total"], 2).'</td>
                        <td>'.$value["fecha"].'</td>
                      </tr>';
              }
            }
            ?>
<a href="index.php?ruta=auditoria-ventas&reporte=auditoria_ventas&aud_sucursal=<?php echo $_GET['aud_sucursal'] ?? ''; ?>&aud_fecha_inicial=<?php echo $_GET['aud_fecha_inicial'] ?? ''; ?>&aud_fecha_final=<?php echo $_GET['aud_fecha_final'] ?? ''; ?>&aud_estado=<?php echo $_GET['aud_estado'] ?? ''; ?>&aud_metodo_pago=<?php echo $_GET['aud_metodo_pago'] ?? ''; ?>&aud_vendedor=<?php echo $_GET['aud_vendedor'] ?? ''; ?>&aud_cliente=<?php echo $_GET['aud_cliente'] ?? ''; ?>" class="btn btn-success">
  Exportar Excel
</a>
          </tbody>
        </table>
      </div>
    </div>

  </section>

</div>