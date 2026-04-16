<div class="content-wrapper">

  <section class="content-header">
    <h1>Cotizaciones y Pedidos</h1>
  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
        <a href="crear-cotizacion" class="btn btn-primary">
          <i class="fa fa-plus"></i> Nuevo documento
        </a>
      </div>

      <div class="box-body">

        <table class="table table-bordered table-striped dt-responsive tablas" width="100%">

          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Código</th>
              <th>Tipo</th>
              <th>Cliente</th>
              <th>Fecha</th>
              <th>Total</th>
              <th>Estado</th>
              <th style="width:140px">Acciones</th>
            </tr>
          </thead>

          <tbody>

            <?php

              $db = Conexion::conectar();
              $stmt = $db->prepare("SELECT * FROM cotizaciones_pedidos ORDER BY id DESC");
              $stmt->execute();
              $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

              if(is_array($documentos)):

                foreach($documentos as $key => $doc):

                  $cliente = ControladorClientes::ctrMostrarClientes("id", $doc["id_cliente"]);

                  $nombreCliente = (is_array($cliente) && isset($cliente["nombre"]))
                                   ? $cliente["nombre"]
                                   : "Cliente no encontrado";
            ?>

              <tr>
                <td><?php echo ($key + 1); ?></td>

                <td><?php echo $doc["codigo_docto"]; ?></td>

                <td>
                  <?php
                    if($doc["tipo"] == "COTIZACION"){
                      echo '<span class="label label-info">Cotización</span>';
                    }else{
                      echo '<span class="label label-success">Pedido</span>';
                    }
                  ?>
                </td>

                <td><?php echo $nombreCliente; ?></td>

                <td>
                  <?php
                    if(!empty($doc["fecha"]) && $doc["fecha"] != "0000-00-00"){
                      echo date("d/m/Y", strtotime($doc["fecha"]));
                    }else{
                      echo '<span class="text-muted">Sin fecha</span>';
                    }
                  ?>
                </td>

                <td>Q <?php echo number_format((float)$doc["total"], 2); ?></td>

                <td>
                  <?php
                    if((int)$doc["estado"] === 1){
                      echo '<span class="label label-warning">Pendiente</span>';
                    }else{
                      echo '<span class="label label-success">Vendida</span>';
                    }
                  ?>
                </td>

                <td>
                  <div class="btn-group">
                    <a href="index.php?ruta=ver-cotizacion&idDocto=<?php echo $doc["id"]; ?>" class="btn btn-info btn-sm">
                      <i class="fa fa-eye"></i>
                    </a>

                    <?php if((int)$doc["estado"] === 1): ?>
                      <a href="index.php?ruta=crear-venta&idCotizacion=<?php echo $doc["id"]; ?>" class="btn btn-success btn-sm">
                        <i class="fa fa-exchange"></i>
                      </a>
                    <?php else: ?>
                      <button type="button" class="btn btn-default btn-sm" disabled title="Ya convertida en venta">
                        <i class="fa fa-check"></i>
                      </button>
                    <?php endif; ?>

                    <?php
                      if($doc["tipo"] == "COTIZACION"){
                        $rutaPdf = "extensiones/tcpdf/pdf/cotizacion.php?idDocto=".$doc["id"];
                        $rutaWord = "extensiones/word/cotizacion.php?idDocto=".$doc["id"];
                      }else{
                        $rutaPdf = "extensiones/tcpdf/pdf/pedido.php?idDocto=".$doc["id"];
                        $rutaWord = "extensiones/word/pedido.php?idDocto=".$doc["id"];
                      }
                    ?>

                    <div class="btn-group">
                      <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false" title="Exportar">
                        <i class="fa fa-print"></i> <span class="caret"></span>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-right">
                        <li>
                          <a href="<?php echo $rutaPdf; ?>" target="_blank">
                            <i class="fa fa-file-pdf-o text-danger"></i> Exportar PDF
                          </a>
                        </li>
                        <li>
                          <a href="<?php echo $rutaWord; ?>" target="_blank">
                            <i class="fa fa-file-word-o text-primary"></i> Exportar Word
                          </a>
                        </li>
                      </ul>
                    </div>

                  </div>
                </td>
              </tr>

            <?php
                endforeach;
              endif;
            ?>

          </tbody>

        </table>

      </div>

    </div>

  </section>

</div>