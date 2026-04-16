<?php

if($_SESSION["perfil"] != "Administrador"){
  echo '<script>window.location = "inicio";</script>';
  return;
}

$productos = ControladorProductos::ctrMostrarProductos(null, null, "id");

$stmtSuc = Conexion::conectar()->prepare("SELECT id, nombre, codigo FROM sucursales WHERE estado = 1 ORDER BY nombre ASC");
$stmtSuc->execute();
$sucursales = $stmtSuc->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="content-wrapper">

  <section class="content-header">
    <h1>Transferencias entre sucursales</h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Transferencias</li>
    </ol>
  </section>

  <section class="content">

    <div class="box">
      <div class="box-header with-border">
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalNuevaTransferencia">
          Nueva transferencia
        </button>
      </div>

      <div class="box-body">
        <table class="table table-bordered table-striped dt-responsive tablas" width="100%">
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Producto</th>
              <th>Origen</th>
              <th>Destino</th>
              <th>Cantidad</th>
              <th>Observación</th>
              <th>Usuario</th>
              <th>Fecha</th>
            </tr>
          </thead>
          <tbody>
            <?php

            $transferencias = ControladorTransferencias::ctrMostrarTransferencias(null, null);

            if(is_array($transferencias)){
              foreach($transferencias as $key => $value){

                echo '<tr>
                        <td>'.($key+1).'</td>
                        <td>'.(isset($value["producto"]) ? $value["producto"] : "").'</td>
                        <td>'.(isset($value["sucursal_origen"]) ? $value["sucursal_origen"] : "").'</td>
                        <td>'.(isset($value["sucursal_destino"]) ? $value["sucursal_destino"] : "").'</td>
                        <td>'.number_format((float)$value["cantidad"], 2).'</td>
                        <td>'.(!empty($value["observacion"]) ? $value["observacion"] : "").'</td>
                        <td>'.(isset($value["usuario_nombre"]) ? $value["usuario_nombre"] : "").'</td>
                        <td>'.(isset($value["fecha"]) ? $value["fecha"] : "").'</td>
                      </tr>';
              }
            }

            ?>
          </tbody>
        </table>
      </div>
    </div>

  </section>

</div>

<div id="modalNuevaTransferencia" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">

      <form role="form" method="post">

        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Nueva transferencia</h4>
        </div>

        <div class="modal-body">
          <div class="box-body">

            <div class="form-group">
  <label>Producto</label>
  <select class="form-control" name="idProductoTransferencia" required>
    <option value="">Seleccionar producto</option>
    <?php
    if(is_array($productos)){
      foreach($productos as $producto){
        echo '<option value="'.$producto["id"].'">'
            .$producto["descripcion"].' - '.$producto["codigo"]
            .' | Stock actual: '.(isset($producto["stock"]) ? $producto["stock"] : 0)
            .'</option>';
      }
    }
    ?>
  </select>
</div>

            <div class="form-group">
              <label>Sucursal origen</label>
              <select class="form-control" name="idSucursalOrigen" required>
                <option value="">Seleccionar sucursal origen</option>
                <?php
                if(is_array($sucursales)){
                  foreach($sucursales as $sucursal){
                    echo '<option value="'.$sucursal["id"].'">'.$sucursal["nombre"].' - '.$sucursal["codigo"].'</option>';
                  }
                }
                ?>
              </select>
            </div>

            <div class="form-group">
              <label>Sucursal destino</label>
              <select class="form-control" name="idSucursalDestino" required>
                <option value="">Seleccionar sucursal destino</option>
                <?php
                if(is_array($sucursales)){
                  foreach($sucursales as $sucursal){
                    echo '<option value="'.$sucursal["id"].'">'.$sucursal["nombre"].' - '.$sucursal["codigo"].'</option>';
                  }
                }
                ?>
              </select>
            </div>

            <div class="form-group">
              <label>Cantidad</label>
              <input type="number" step="any" min="0.01" class="form-control" name="cantidadTransferencia" required>
            </div>

            <div class="form-group">
              <label>Observación</label>
              <textarea class="form-control" name="observacionTransferencia" rows="3"></textarea>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar transferencia</button>
        </div>

        <?php
          ControladorTransferencias::ctrRegistrarTransferencia();
        ?>

      </form>

    </div>
  </div>
</div>