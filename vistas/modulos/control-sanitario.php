<?php

if($_SESSION["perfil"] != "Administrador"){
  echo '<script>
    window.location = "inicio";
  </script>';
  return;
}

?>

<div class="content-wrapper">

  <section class="content-header">
    <h1>
      Control Sanitario
      <small>Vacunas y Medicamentos</small>
    </h1>
  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">

        <button class="btn btn-primary" data-toggle="modal" data-target="#modalRegistrarSanitario">
          Registrar Aplicación
        </button>

      </div>

      <div class="box-body">

        <table class="table table-bordered table-striped dt-responsive tablaSanitario" width="100%">

          <thead>
            <tr>
              <th>#</th>
              <th>Venta</th>
              <th>Producto</th>
              <th>Tipo</th>
              <th>Fecha Aplicación</th>
              <th>Próxima Dosis</th>
              <th>Acciones</th>
            </tr>
          </thead>

          <tbody>

<?php

$sanitario = ControladorControlSanitario::ctrMostrarSanitario();

foreach ($sanitario as $key => $value){

    echo '<tr>
        <td>'.($key+1).'</td>
        <td>'.$value["id_venta"].'</td>
        <td>'.$value["producto"].'</td>
        <td>'.$value["tipo"].'</td>
        <td>'.$value["fecha_aplicacion"].'</td>
        <td>'.$value["proxima_aplicacion"].'</td>
        <td>
            <button class="btn btn-danger btn-sm">Eliminar</button>
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

<!--=====================================
MODAL REGISTRAR SANITARIO
======================================-->

<div id="modalRegistrarSanitario" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form method="post">

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Registrar Aplicación</h4>

        </div>

        <div class="modal-body">

          <div class="box-body">

            <!-- ID Venta -->
            <div class="form-group">
              <label>ID Venta</label>
              <input type="number" class="form-control" name="idVentaSanitaria" required>
            </div>

            <!-- Producto -->
            <div class="form-group">
              <label>Producto</label>
              <input type="text" class="form-control" name="productoSanitario" required>
            </div>

            <!-- Tipo -->
            <div class="form-group">
              <label>Tipo</label>
              <select class="form-control" name="tipoSanitario" required>
                <option value="">Seleccionar</option>
                <option value="Vacuna">Vacuna</option>
                <option value="Medicamento">Medicamento</option>
              </select>
            </div>

            <!-- Fecha Aplicación -->
            <div class="form-group">
              <label>Fecha Aplicación</label>
              <input type="date" class="form-control" name="fechaAplicacion" required>
            </div>

            <!-- Próxima Dosis -->
            <div class="form-group">
              <label>Próxima Dosis</label>
              <input type="date" class="form-control" name="proximaDosis">
            </div>

          </div>

        </div>

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Guardar</button>

        </div>

      </form>

      <?php
  $crearSanitario = new ControladorControlSanitario();
  $crearSanitario -> ctrCrearSanitario();
?>

    </div>

  </div>

</div>