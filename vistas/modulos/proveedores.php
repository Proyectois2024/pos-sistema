<?php
require_once "controladores/proveedores.controlador.php";
require_once "modelos/proveedores.modelo.php";
?>

<?php
if ($_SESSION["perfil"] == "Especial") {
  echo '<script>window.location = "inicio";</script>';
  return;
}
?>

<div class="content-wrapper">

  <section class="content-header">
    <h1>Administrar proveedores</h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar proveedores</li>
    </ol>
  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarProveedor">
          Agregar proveedor
        </button>
      </div>

      <div class="box-body">

        <table class="table table-bordered table-striped dt-responsive tablas" width="100%">
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Nombre</th>
              <th>Empresa ID</th>
              <th>Email</th>
              <th>Teléfono</th>
              <th>Dirección</th>
              <th>Acciones</th>
            </tr>
          </thead>

          <tbody>
            <?php
            $item = null;
            $valor = null;
            $proveedores = ControladorProveedores::ctrMostrarProveedores($item, $valor);

            foreach ($proveedores as $key => $value) {
              echo '<tr>
                      <td>' . ($key + 1) . '</td>
                      <td>' . $value["nombre"] . '</td>
                      <td>' . $value["empresa"] . '</td>
                      <td>' . $value["email"] . '</td>
                      <td>' . $value["telefono"] . '</td>
                      <td>' . $value["direccion"] . '</td>
                      <td>
                        <div class="btn-group">
                          <a href="index.php?ruta=registrar-compra&idProveedor=' . $value["id"] . '" class="btn btn-success" title="Registrar Compra">
                            <i class="fa fa-plus"></i>
                          </a>
                          <a href="index.php?ruta=historial-compras&idProveedor=' . $value["id"] . '" class="btn btn-info" title="Historial de Compras">
                            <i class="fa fa-list"></i>
                          </a>
                          <button class="btn btn-warning btnEditarProveedor" data-toggle="modal" data-target="#modalEditarProveedor" idProveedor="' . $value["id"] . '">
                            <i class="fa fa-pencil"></i>
                          </button>';

              if ($_SESSION["perfil"] == "Administrador") {
                echo '<button class="btn btn-danger btnEliminarProveedor" idProveedor="' . $value["id"] . '">
                        <i class="fa fa-times"></i>
                      </button>';
              }

              echo '  </div>
                      </td>
                    </tr>';
            }
            ?>
          </tbody>

        </table>

      </div>

    </div>

  </section>

</div>

<!-- MODAL AGREGAR PROVEEDOR -->

<div id="modalAgregarProveedor" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar proveedor</h4>
        </div>

        <div class="modal-body">
          <div class="box-body">

            <!-- NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                <input type="text" class="form-control input-lg" name="nuevoProveedor" placeholder="Ingresar nombre" required>
              </div>
            </div>

            <!-- EMPRESA -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-building"></i></span>
                <input type="text" class="form-control input-lg" name="nuevaEmpresa" placeholder="Ingresar empresa" required>
              </div>
            </div>

            <!-- EMAIL -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                <input type="email" class="form-control input-lg" name="nuevoEmail" placeholder="Ingresar email">
              </div>
            </div>

            <!-- TELÉFONO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                <input type="text" class="form-control input-lg" name="nuevoTelefono" placeholder="Ingresar teléfono" data-inputmask="'mask':'(999) 9999-9999'" data-mask>
              </div>
            </div>

            <!-- DIRECCIÓN -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                <input type="text" class="form-control input-lg" name="nuevaDireccion" placeholder="Ingresar dirección">
              </div>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar proveedor</button>
        </div>
      </form>

      <?php
      $crearProveedor = new ControladorProveedores();
      $crearProveedor->ctrCrearProveedor();
      ?>
    </div>
  </div>
</div>

<!-- MODAL EDITAR PROVEEDOR -->

<div id="modalEditarProveedor" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar proveedor</h4>
        </div>

        <div class="modal-body">
          <div class="box-body">

            <!-- NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                <input type="text" class="form-control input-lg" name="editarProveedor" id="editarProveedor" required>
                <input type="hidden" id="idProveedor" name="idProveedor">
              </div>
            </div>

            <!-- EMPRESA -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-building"></i></span>
                <input type="text" class="form-control input-lg" name="editarEmpresa" id="editarEmpresa" required>
              </div>
            </div>

            <!-- EMAIL -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                <input type="email" class="form-control input-lg" name="editarEmail" id="editarEmail">
              </div>
            </div>

            <!-- TELÉFONO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                <input type="text" class="form-control input-lg" name="editarTelefono" id="editarTelefono" data-inputmask="'mask':'(999) 9999-9999'" data-mask>
              </div>
            </div>

            <!-- DIRECCIÓN -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                <input type="text" class="form-control input-lg" name="editarDireccion" id="editarDireccion">
              </div>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
      </form>

      <?php
      $editarProveedor = new ControladorProveedores();
      $editarProveedor->ctrEditarProveedor();
      ?>
    </div>
  </div>
</div>

<?php
$eliminarProveedor = new ControladorProveedores();
$eliminarProveedor->ctrEliminarProveedor();
?>
<script> $(document).ready(function() { $(".btnEliminarProveedor").click(function() { var idProveedor = $(this).attr("idProveedor"); swal({ title: "¿Está seguro de borrar el proveedor?", text: "¡Si no lo está puede cancelar la acción!", icon: "warning", buttons: { cancel: { text: "Cancelar", visible: true, className: "btn btn-default" }, confirm: { text: "Sí, borrar proveedor!", visible: true, className: "btn btn-danger" } }, dangerMode: true }).then((willDelete) => { if (willDelete) { window.location = "index.php?ruta=proveedores&idProveedor=" + idProveedor; } }); }); }); </script>

<script> $(document).on("click", ".btnEditarProveedor", function () { var idProveedor = $(this).attr("idProveedor"); var datos = new FormData(); datos.append("idProveedor", idProveedor); $.ajax({ url: "ajax/proveedores.ajax.php", method: "POST", data: datos, cache: false, contentType: false, processData: false, dataType: "json", success: function (respuesta) { $("#idProveedor").val(respuesta["id"]); $("#editarProveedor").val(respuesta["nombre"]); $("#editarEmpresa").val(respuesta["empresa"]); $("#editarEmail").val(respuesta["email"]); $("#editarTelefono").val(respuesta["telefono"]); $("#editarDireccion").val(respuesta["direccion"]); } }); }); </script>