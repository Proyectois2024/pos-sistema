<div class="content-wrapper">

  <section class="content-header">
    <h1>Ver Cotización / Pedido</h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li><a href="cotizaciones">Cotizaciones</a></li>
      <li class="active">Ver documento</li>
    </ol>
  </section>

  <section class="content">

    <?php

      if(!isset($_GET["idDocto"]) || empty($_GET["idDocto"])){

        echo '<div class="alert alert-danger">Documento no válido.</div>';
        return;
      }

      $idDocto = intval($_GET["idDocto"]);

      $db = Conexion::conectar();

      $stmt = $db->prepare("SELECT * FROM cotizaciones_pedidos WHERE id = :id LIMIT 1");
      $stmt->bindParam(":id", $idDocto, PDO::PARAM_INT);
      $stmt->execute();

      $documento = $stmt->fetch(PDO::FETCH_ASSOC);

      if(!$documento){

        echo '<div class="alert alert-danger">El documento no existe.</div>';
        return;
      }

      $cliente = ControladorClientes::ctrMostrarClientes("id", $documento["id_cliente"]);
      $nombreCliente = (is_array($cliente) && isset($cliente["nombre"])) ? $cliente["nombre"] : "Cliente no encontrado";

      $stmtDetalle = $db->prepare("SELECT * FROM detalles_docto WHERE id_docto = :id_docto ORDER BY id ASC");
      $stmtDetalle->bindParam(":id_docto", $idDocto, PDO::PARAM_INT);
      $stmtDetalle->execute();

      $detalles = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="row">

      <div class="col-md-12">

        <div class="box box-primary">

          <div class="box-header with-border">
            <a href="cotizaciones" class="btn btn-default">
              <i class="fa fa-arrow-left"></i> Volver
            </a>
          </div>

          <div class="box-body">

            <div class="row">

              <div class="col-md-3">
                <div class="form-group">
                  <label>Código</label>
                  <input type="text" class="form-control" value="<?php echo $documento["codigo_docto"]; ?>" readonly>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Tipo</label>
                  <input type="text" class="form-control" value="<?php echo $documento["tipo"]; ?>" readonly>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Fecha</label>
                  <input type="text" class="form-control" value="<?php echo $documento["fecha"]; ?>" readonly>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Cliente</label>
                  <input type="text" class="form-control" value="<?php echo $nombreCliente; ?>" readonly>
                </div>
              </div>

            </div>

            <hr>

            <div class="table-responsive">
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th style="width:10px">#</th>
                    <th>Descripción</th>
                    <th>Cantidad</th>
                    <th>Unidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                  </tr>
                </thead>
                <tbody>

                  <?php if(is_array($detalles) && count($detalles) > 0): ?>
                    
                    <?php foreach($detalles as $key => $detalle): ?>
                      <tr>
                        <td><?php echo ($key + 1); ?></td>
                        <td><?php echo $detalle["descripcion_item"]; ?></td>
                        <td><?php echo number_format((float)$detalle["cantidad"], 2); ?></td>
                        <td><?php echo $detalle["unidad_medida"]; ?></td>
                        <td>Q <?php echo number_format((float)$detalle["precio_unitario"], 2); ?></td>
                        <td>Q <?php echo number_format((float)$detalle["subtotal"], 2); ?></td>
                      </tr>
                    <?php endforeach; ?>

                  <?php else: ?>

                    <tr>
                      <td colspan="6" class="text-center text-muted">No hay detalles para este documento.</td>
                    </tr>

                  <?php endif; ?>

                </tbody>
                <tfoot>
                  <tr>
                    <th colspan="5" class="text-right">Total</th>
                    <th>Q <?php echo number_format((float)$documento["total"], 2); ?></th>
                  </tr>
                </tfoot>
              </table>
            </div>

          </div>

        </div>

      </div>

    </div>

  </section>

</div>