<?php

$cotizacionConvertida = null;
$detalleCotizacion = array();
$clienteCotizacion = "";
$productosCotizacionHtml = "";
$productosCotizacionJson = "[]";
$totalCotizacion = 0;
$netoCotizacion = 0;

if(isset($_GET["idCotizacion"]) && !empty($_GET["idCotizacion"])){

    $idCotizacion = intval($_GET["idCotizacion"]);

    $cotizacionConvertida = ControladorCotizaciones::ctrMostrarCotizacion("id", $idCotizacion);

    if(is_array($cotizacionConvertida)){

        $detalleCotizacion = ControladorCotizaciones::ctrMostrarDetalleCotizacion($idCotizacion);
        $clienteCotizacion = $cotizacionConvertida["id_cliente"];
        $totalCotizacion = floatval($cotizacionConvertida["total"]);
        $netoCotizacion = $totalCotizacion;

        $productosJsonArray = array();

        if(is_array($detalleCotizacion)){

            foreach($detalleCotizacion as $detalle){

                $idProducto = isset($detalle["id_producto"]) ? intval($detalle["id_producto"]) : 0;
                $descripcion = isset($detalle["descripcion_item"]) ? $detalle["descripcion_item"] : "";
                $cantidad = isset($detalle["cantidad"]) ? floatval($detalle["cantidad"]) : 0;
                $precioUnitario = isset($detalle["precio_unitario"]) ? floatval($detalle["precio_unitario"]) : 0;
                $subtotal = isset($detalle["subtotal"]) ? floatval($detalle["subtotal"]) : 0;

                $stockActual = 0;

                if($idProducto > 0){
                    $productoDB = ControladorProductos::ctrMostrarProductos("id", $idProducto, "id");

                    if(is_array($productoDB) && isset($productoDB["stock"])){
                        $stockActual = floatval($productoDB["stock"]);
                    }
                }

                $nuevoStock = $stockActual - $cantidad;
                if($nuevoStock < 0){
                    $nuevoStock = 0;
                }

                $productosCotizacionHtml .= '
                <div class="row" style="padding:5px 15px">

                  <div class="col-xs-6" style="padding-right:0px">
                    <div class="input-group">
                      <span class="input-group-addon">
                        <button type="button" class="btn btn-danger btn-xs quitarProducto" idProducto="'.$idProducto.'">
                          <i class="fa fa-times"></i>
                        </button>
                      </span>

                      <input type="text" class="form-control nuevaDescripcionProducto" idProducto="'.$idProducto.'" name="agregarProducto" value="'.$descripcion.'" readonly required>
                    </div>
                  </div>

                  <div class="col-xs-3">
                    <input type="number" class="form-control nuevaCantidadProducto" name="nuevaCantidadProducto" min="1" value="'.$cantidad.'" stock="'.$stockActual.'" nuevoStock="'.$nuevoStock.'" required>
                  </div>

                  <div class="col-xs-3 ingresoPrecio" style="padding-left:0px">
                    <div class="input-group">
                      <span class="input-group-addon"><strong>Q</strong></span>
                      <input type="text" class="form-control nuevoPrecioProducto" precioReal="'.$precioUnitario.'" name="nuevoPrecioProducto" value="'.$subtotal.'" readonly required>
                    </div>
                  </div>

                </div>';

                $productosJsonArray[] = array(
                    "id" => (string)$idProducto,
                    "descripcion" => $descripcion,
                    "cantidad" => (string)$cantidad,
                    "stock" => (string)$nuevoStock,
                    "precio" => (string)$precioUnitario,
                    "total" => (string)$subtotal
                );
            }
        }

        $productosCotizacionJson = json_encode($productosJsonArray);
    }
}

if($_SESSION["perfil"] == "Especial"){
  echo '<script>
    window.location = "inicio";
  </script>';
  return;
}

$caja = ControladorCaja::ctrObtenerCajaAbierta($_SESSION["id"]);

?>
<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      Crear venta
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Crear venta</li>
    </ol>

  </section>

  <section class="content">

    <div class="row">

      <div class="col-lg-5 col-xs-12">
        
        <div class="box box-success">
          
          <div class="box-header with-border"></div>

          <form role="form" method="post" class="formularioVenta">

            <div class="box-body">
  
              <div class="box">

                <div class="form-group">
                
                  <div class="input-group">
                    
                    <span class="input-group-addon"><i class="fa fa-user"></i></span> 

                    <input type="text" class="form-control" id="nuevoVendedor" value="<?php echo $_SESSION["nombre"]; ?>" readonly>

                    <input type="hidden" name="idVendedor" value="<?php echo $_SESSION["id"]; ?>">

                    <input type="hidden" name="idCotizacion" value="<?php echo isset($idCotizacion) ? (int)$idCotizacion : 0; ?>">

                  </div>

                </div>

                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-building"></i></span>
                    <input type="text" class="form-control" value="Sucursal activa: <?php echo isset($_SESSION['id_sucursal']) ? $_SESSION['id_sucursal'] : 'Sin asignar'; ?>" readonly>
                  </div>
                </div>

                <div class="form-group">
                  
                  <div class="input-group">
                    
                    <span class="input-group-addon"><i class="fa fa-key"></i></span>

                    <?php

                    $item = null;
                    $valor = null;
                    $ventas = ControladorVentas::ctrMostrarVentas($item, $valor);

                    $codigo = 10001;

                    if(is_array($ventas) && count($ventas) > 0){
                      $ultimaVenta = end($ventas);
                      if(isset($ultimaVenta["codigo"])){
                        $codigo = (int)$ultimaVenta["codigo"] + 1;
                      }
                    }

                    echo '<input type="text" class="form-control" id="nuevaVenta" name="nuevaVenta" value="'.$codigo.'" readonly>';

                    ?>
                    
                  </div>
                
                </div>

                <div class="form-group">
                  
                  <div class="input-group">
                    
                    <span class="input-group-addon"><i class="fa fa-users"></i></span>
                    
                    <select class="form-control" id="seleccionarCliente" name="seleccionarCliente">

                      <option value="">Seleccionar cliente</option>

                      <?php

                        $item = null;
                        $valor = null;

                        $clientes = ControladorClientes::ctrMostrarClientes($item, $valor);

                        if(is_array($clientes)){

                          foreach ($clientes as $key => $value) {

                            echo '<option value="'.$value["id"].'" '.($clienteCotizacion == $value["id"] ? "selected" : "").'>'.$value["nombre"].'</option>';

                          }

                        }

                      ?>

                    </select>
                    
                    <span class="input-group-addon">
                      <button type="button" class="btn btn-default btn-xs" data-toggle="modal" data-target="#modalAgregarCliente" data-dismiss="modal">
                        Agregar cliente
                      </button>
                    </span>
                  
                  </div>
                
                </div>

                <div class="form-group row nuevoProducto">

                  <?php echo $productosCotizacionHtml; ?>

                </div>

                <input type="hidden" id="listaProductos" name="listaProductos" value='<?php echo $productosCotizacionJson; ?>'>

                <button type="button" class="btn btn-default hidden-lg btnAgregarProducto">Agregar producto</button>

                <hr>

                <div class="row">

                  <div class="col-xs-8 pull-right">
                    
                    <table class="table">

                      <thead>

                        <tr>
                          <th>Impuesto</th>
                          <th>Total</th>      
                        </tr>

                      </thead>

                      <tbody>
                      
                        <tr>
                          
                          <td style="width: 50%">
                            
                            <div class="input-group">
                           
                              <input type="number" class="form-control input-lg" min="0" id="nuevoImpuestoVenta" name="nuevoImpuestoVenta" placeholder="0" value="0" required>

                              <input type="hidden" name="nuevoPrecioImpuesto" id="nuevoPrecioImpuesto" value="0" required>

                              <input type="hidden" name="nuevoPrecioNeto" id="nuevoPrecioNeto" value="<?php echo number_format($netoCotizacion, 2, '.', ''); ?>" required>

                              <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                        
                            </div>

                          </td>

                          <td style="width: 50%">
                            
                            <div class="input-group">
                           
                              <span class="input-group-addon"><strong style="font-size: 16px;">Q</strong></span>

                              <input type="text" class="form-control input-lg" id="nuevoTotalVenta" name="nuevoTotalVenta" total="<?php echo number_format($netoCotizacion, 2, '.', ''); ?>" value="<?php echo number_format($totalCotizacion, 2, '.', ''); ?>" placeholder="00000" readonly required>

                              <input type="hidden" name="totalVenta" id="totalVenta" value="<?php echo number_format($totalCotizacion, 2, '.', ''); ?>">
                              
                            </div>

                          </td>

                        </tr>

                      </tbody>

                    </table>

                  </div>

                </div>

                <hr>

               <div class="form-group row">
  
  <div class="col-xs-6" style="padding-right:0px">
    
    <div class="input-group">
  
      <select class="form-control" id="nuevoMetodoPago" name="nuevoMetodoPago" required>
        <option value="">Seleccione método de pago</option>
        <option value="Efectivo">Efectivo</option>
        <option value="Transferencia">Transferencia</option>
        <option value="Credito">Credito</option>
      </select>              
      
    </div>

  </div>

  <div class="cajasMetodoPago"></div>

  <input type="hidden" id="listaMetodoPago" name="listaMetodoPago">

</div>

<div class="form-group" id="grupoFechaVencimiento" style="display:none;">
  <div class="input-group">
    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
    <input type="date" class="form-control" name="nuevaFechaVencimiento" id="nuevaFechaVencimiento">
  </div>
</div>

                <br>
      
              </div>

            </div>

            <div class="box-footer">

              <button type="submit" class="btn btn-primary pull-right">Guardar venta</button>

            </div>

          </form>

          <?php

            $guardarVenta = new ControladorVentas();
            $guardarVenta->ctrCrearVenta();
          
          ?>

        </div>
            
      </div>

      <div class="col-lg-7 hidden-md hidden-sm hidden-xs">
        
        <div class="box box-warning">

          <div class="box-header with-border"></div>

          <div class="box-body">
            
            <table class="table table-bordered table-striped dt-responsive tablaProductosVenta">
              
              <thead>
                <tr>
                  <th style="width: 10px">#</th>
                  <th>Imagen</th>
                  <th>Código</th>
                  <th>Descripcion</th>
                  <th>Stock</th>
                  <th>Acciones</th>
                </tr>
              </thead>

            </table>

          </div>

        </div>

      </div>

    </div>
   
  </section>

</div>

<div id="modalAgregarCliente" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Agregar cliente</h4>

        </div>

        <div class="modal-body">

          <div class="box-body">

            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-user"></i></span> 

                <input type="text" class="form-control input-lg" name="nuevoCliente" placeholder="Ingresar nombre" required>

              </div>

            </div>

            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-key"></i></span> 

                <input type="number" min="0" class="form-control input-lg" name="nuevoDocumentoId" placeholder="Ingresar documento">

              </div>

            </div>

            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-envelope"></i></span> 

                <input type="email" class="form-control input-lg" name="nuevoEmail" placeholder="Ingresar email">

              </div>

            </div>

            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-phone"></i></span> 

                <input type="text" class="form-control input-lg" name="nuevoTelefono" placeholder="Ingresar teléfono" data-inputmask="'mask':'(999) 999-9999'" data-mask>

              </div>

            </div>

            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span> 

                <input type="text" class="form-control input-lg" name="nuevaDireccion" placeholder="Ingresar dirección">

              </div>

            </div>

            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span> 

                <input type="text" class="form-control input-lg" name="nuevaFechaNacimiento" placeholder="Ingresar fecha nacimiento" data-inputmask="'alias': 'yyyy/mm/dd'" data-mask>

              </div>

            </div>
  
          </div>

        </div>

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Guardar cliente</button>

        </div>

      </form>

      <?php

        $crearCliente = new ControladorClientes();
        $crearCliente->ctrCrearCliente();

      ?>

    </div>

  </div>

</div>

<?php if(!$caja): ?>

<div id="modalAperturaCaja" class="modal fade in" role="dialog" style="display:block; background: rgba(0,0,0,0.8); overflow-y: auto;">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <div class="modal-header" style="background:#dd4b39; color:white">
          <h4 class="modal-title">🚫 LA CAJA ESTÁ CERRADA</h4>
        </div>

        <div class="modal-body">

          <div class="box-body">

            <div class="form-group">
              
              <p class="text-center" style="font-size: 16px;">Para poder realizar ventas, primero debes ingresar el monto de efectivo inicial en caja.</p>
              
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-money"></i></span>
                <input type="number" class="form-control input-lg" name="nuevoMontoApertura" placeholder="Ingresa el monto de apertura (Ej. 100.00)" required>
              </div>

            </div>

          </div>

        </div>

        <div class="modal-footer">
          <a href="inicio" class="btn btn-default pull-left">Cancelar y Volver</a>
          <button type="submit" class="btn btn-danger">Abrir Caja</button>
        </div>

        <?php
          $aperturaCaja = new ControladorCaja();
          $aperturaCaja->ctrCrearApertura();
        ?>

      </form>

    </div>

  </div>

</div>

<?php endif; ?>