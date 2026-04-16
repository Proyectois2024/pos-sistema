<div class="content-wrapper">
  <section class="content-header">
    <h1>Crear Nueva Cotización / Pedido</h1>
  </section>

  <section class="content">
    <div class="row">

      <div class="col-lg-12 col-xs-12">
        <div class="box box-primary">
          <form role="form" method="post" id="formularioCotizacion" class="formularioCotizacion">
            <div class="box-body">

              <?php
                $item = null;
                $valor = null;
                $clientes = ControladorClientes::ctrMostrarClientes($item, $valor);
                $codigo = "DOC-".date("YmdHis");
              ?>

              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Código</label>
                    <input type="text" class="form-control" name="codigo" value="<?php echo $codigo; ?>" readonly>
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label>Tipo de documento</label>
                    <select class="form-control" name="tipo_docto" required>
                      <option value="">Seleccionar</option>
                      <option value="COTIZACION">Cotización</option>
                      <option value="PEDIDO">Pedido</option>
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
                          <option value="<?php echo $cliente["id"]; ?>">
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
                <tbody class="listaProductosCotizacion"></tbody>
              </table>

              <input type="hidden" name="productosJsonCotizacion" id="productosJsonCotizacion">

              <div class="row">
                <div class="col-md-4 col-md-offset-8">
                  <div class="form-group">
                    <label>Total del documento</label>
                    <input type="text" class="form-control" id="totalDocumento" name="totalDocumento" readonly value="0.00">
                  </div>
                </div>
              </div>

            </div>

            <div class="box-footer">
              <button type="submit" class="btn btn-primary pull-right">Guardar Documento</button>
            </div>

            <?php
              $crearCotizacion = new ControladorCotizaciones();
              $crearCotizacion->ctrCrearCotizacion();
            ?>
          </form>
        </div>
      </div>

    </div>
  </section>
</div>