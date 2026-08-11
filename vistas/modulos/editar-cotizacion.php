<div class="content-wrapper">
  <section class="content-header">
    <h1>Editar Cotización / Pedido</h1>
  </section>

  <section class="content">
    <div class="row">

      <div class="col-lg-12 col-xs-12">
        <div class="box box-warning">
          <form role="form" method="post" id="formularioEditarCotizacion" class="formularioCotizacion">
            <div class="box-body">

              <?php
                if(!isset($_GET["idCotizacion"])){
                    echo '<script>window.location = "cotizaciones";</script>';
                    exit();
                }

                $item = "id";
                $valor = $_GET["idCotizacion"];
                
                // Método en singular para consultar el cabezal
                $doc = ControladorCotizaciones::ctrMostrarCotizacion($item, $valor);

                if(!$doc){
                    echo '<script>window.location = "cotizaciones";</script>';
                    exit();
                }

                // Obtenemos el detalle de productos desde la base de datos
                $detalles = ControladorCotizaciones::ctrMostrarDetalleCotizacion($doc["id"]);

                $clientes = ControladorClientes::ctrMostrarClientes(null, null);

                // Mapeamos los detalles al formato que lee JavaScript
                $productosJson = array();
                if(is_array($detalles)){
                    foreach($detalles as $det){
                        $productosJson[] = array(
                            "id_producto" => $det["id_producto"],
                            "descripcion" => $det["descripcion_item"],
                            "cantidad"    => $det["cantidad"],
                            "unidad"      => $det["unidad_medida"],
                            "precio"      => $det["precio_unitario"],
                            "subtotal"    => $det["subtotal"]
                        );
                    }
                }
              ?>

              <!-- Guardamos el ID del documento -->
              <input type="hidden" name="idCotizacion" value="<?php echo $doc["id"]; ?>">

              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Código</label>
                    <input type="text" class="form-control" name="codigo" value="<?php echo $doc["codigo_docto"]; ?>" readonly>
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label>Tipo de documento</label>
                    <select class="form-control" name="tipo_docto" required>
                      <option value="COTIZACION" <?php echo ($doc["tipo"] == "COTIZACION") ? "selected" : ""; ?>>Cotización</option>
                      <option value="PEDIDO" <?php echo ($doc["tipo"] == "PEDIDO") ? "selected" : ""; ?>>Pedido</option>
                    </select>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Cliente</label>
                    <select class="form-control" name="id_cliente" required>
                      <option value="">Seleccionar cliente</option>
                      <?php if(is_array($clientes)): ?>
                        <?php foreach($clientes as $cliente): ?>
                          <option value="<?php echo $cliente["id"]; ?>" <?php echo ($cliente["id"] == $doc["id_cliente"]) ? "selected" : ""; ?>>
                            <?php echo $cliente["nombre"]; ?>
                          </option>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </select>
                  </div>
                </div>
              </div>

              <hr>

              <div class="row">
                <div class="col-md-12">
                  <h4>Buscar productos</h4>
                  <table class="table table-bordered table-striped dt-responsive tablaProductosCotizacion" width="100%">
                    <thead>
                      <tr>
                        <th style="width:10px">#</th>
                        <th>Imagen</th>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Categoría</th>
                        <th>Stock</th>
                        <th>Precio Compra</th>
                        <th>Precio Venta</th>
                        <th>Vencimiento</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                      </tr>
                    </thead>
                  </table>
                </div>
              </div>

              <hr>

              <h4>Detalle del documento</h4>

              <table class="table table-bordered" id="tablaProductos">
                <thead>
                  <tr>
                    <th>Descripción</th>
                    <th style="width:120px">Cantidad</th>
                    <th>Unidad</th>
                    <th style="width:150px">Precio Unitario</th>
                    <th style="width:150px">Subtotal</th>
                    <th style="width:80px">Acción</th>
                  </tr>
                </thead>
                <tbody class="listaProductosCotizacion">
                  <?php if(is_array($detalles)): ?>
                    <?php foreach($detalles as $det): ?>
                      <tr class="filaProductoCotizacion" idProducto="<?php echo $det["id_producto"]; ?>">
                        <td><input type="text" class="form-control descripcionCotizacion" name="descripcion[]" value="<?php echo $det["descripcion_item"]; ?>" readonly required></td>
                        <td><input type="number" class="form-control cantidadCotizacion" name="cantidad[]" value="<?php echo $det["cantidad"]; ?>" min="1" step="any" required></td>
                        <td><input type="text" class="form-control unidadCotizacion" name="unidad[]" value="<?php echo $det["unidad_medida"]; ?>" placeholder="Ej: Unidad"></td>
                        <td><input type="number" class="form-control precioCotizacion" name="precio[]" value="<?php echo number_format((float)$det["precio_unitario"], 2, '.', ''); ?>" min="0" step="any" required></td>
                        <td><input type="number" class="form-control subtotalCotizacion" name="subtotal[]" value="<?php echo number_format((float)$det["subtotal"], 2, '.', ''); ?>" readonly></td>
                        <td><button type="button" class="btn btn-danger btnQuitarProductoCotizacion"><i class="fa fa-times"></i></button></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>

              <!-- Se envía la lista de productos serializada en JSON -->
              <input type="hidden" name="productosJsonCotizacion" id="productosJsonCotizacion" value='<?php echo json_encode($productosJson); ?>'>

              <div class="row">
                <div class="col-md-4 col-md-offset-8">
                  <div class="form-group">
                    <label>Total del documento</label>
                    <input type="text" class="form-control" id="totalDocumento" name="totalDocumento" readonly value="<?php echo number_format((float)$doc["total"], 2, '.', ''); ?>">
                  </div>
                </div>
              </div>

            </div>

            <div class="box-footer">
              <button type="submit" class="btn btn-warning pull-right">Actualizar Documento</button>
            </div>

            <?php
              $editarCotizacion = new ControladorCotizaciones();
              $editarCotizacion->ctrEditarCotizacion();
            ?>
          </form>
        </div>
      </div>

    </div>
  </section>
</div>
