<?php
if ($_SESSION["perfil"] != "Administrador") {
  echo '<script>window.location = "inicio";</script>';
  return;
}

$proveedores = ControladorProveedores::ctrMostrarProveedores(null, null);
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Gestión de Gastos</h1>
  </section>

  <section class="content">
    <div class="box">
      <div class="box-header with-border">
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarGasto">Agregar Gasto</button>
      </div>

      <div class="box-body">
        <div class="row">
          <div class="col-md-4">
            <label>Filtrar por proveedor:</label>
            <select id="filtroProveedor" class="form-control">
              <option value="">Todos</option>
              <?php foreach ($proveedores as $p) {
                echo '<option value="'.$p["id"].'">'.$p["nombre"].'</option>';
              } ?>
            </select>
          </div>
          <div class="col-md-4">
            <label>Filtrar por mes:</label>
            <input type="month" id="filtroMes" class="form-control">
          </div>
        </div>
        <br>
        <table class="table table-bordered table-striped dt-responsive tablaGastos" width="100%">
          <thead>
            <tr>
              <th>#</th>
              <th>Descripción</th>
              <th>Monto</th>
              <th>Proveedor</th>
              <th>Tipo</th>
              <th>Fecha</th>
              <th>Acciones</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </section>
</div>

<!-- Modal Agregar Gasto -->
<div id="modalAgregarGasto" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <h4 class="modal-title">Agregar Gasto</h4>
        </div>

        <div class="modal-body">
          <div class="box-body">

            <div class="form-group">
              <label>Tipo de gasto</label>
              <select name="tipo" id="tipoGasto" class="form-control" required>
                <option value="proveedor">Proveedor</option>
                <option value="empleado">Empleado</option>
                <option value="otro">Otro</option>
              </select>
            </div>

            <div class="form-group" id="grupoProveedor">
              <label>Proveedor</label>
              <select name="idProveedor" class="form-control">
                <option value="">Seleccionar proveedor</option>
                <?php foreach ($proveedores as $p) {
                  echo '<option value="'.$p["id"].'">'.$p["nombre"].'</option>';
                } ?>
              </select>
            </div>

            <div class="form-group">
              <label>Descripción</label>
              <input type="text" name="descripcionGasto" class="form-control" required>
            </div>

            <div class="form-group">
              <label>Monto</label>
              <input type="number" name="monto" class="form-control" step="any" required>
            </div>

            <div class="form-group">
              <label>Fecha</label>
              <input type="date" name="fechaGasto" class="form-control" required>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar Gasto</button>
        </div>

        <?php
          $crearGasto = new ControladorGastos();
          $crearGasto->ctrCrearGasto();
        ?>
      </form>
    </div>
  </div>
</div>

<!-- Modal Editar Gasto -->
<div id="modalEditarGasto" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header" style="background:#f39c12; color:white">
          <h4 class="modal-title">Editar Gasto</h4>
        </div>

        <div class="modal-body">
          <div class="box-body">

            <input type="hidden" name="idGastoEditar" id="idGastoEditar">

            <div class="form-group">
              <label>Descripción</label>
              <input type="text" name="descripcionGastoEditar" id="descripcionGastoEditar" class="form-control" required>
            </div>

            <div class="form-group">
              <label>Monto</label>
              <input type="number" name="montoEditar" id="montoEditar" class="form-control" step="any" required>
            </div>

            <div class="form-group">
              <label>Fecha</label>
              <input type="date" name="fechaGastoEditar" id="fechaGastoEditar" class="form-control" required>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Actualizar Gasto</button>
        </div>

        <?php
          $editarGasto = new ControladorGastos();
          $editarGasto->ctrEditarGasto();
        ?>
      </form>
    </div>
  </div>
</div>

<script>
  let tablaGastos = $('.tablaGastos').DataTable({
    "ajax": {
      "url": "ajax/gastos.ajax.php",
      "data": function(d) {
        let mesCompleto = $('#filtroMes').val();
        if (mesCompleto) {
          let partes = mesCompleto.split("-");
          d.anio = partes[0];
          d.mes = partes[1];
        }
        d.proveedor = $('#filtroProveedor').val();
      }
    },
    "columns": [
      { "title": "#", "data": null }, // El número se puede calcular en render
      { "title": "Descripción" },
      { "title": "Monto" },
      { "title": "Proveedor" },
      { "title": "Tipo" },
      { "title": "Fecha" },
      { "title": "Acciones" }
    ],
    "columnDefs": [{
      "targets": 0,
      "render": function (data, type, row, meta) {
        return meta.row + 1;
      }
    }],
    "deferRender": true,
    "retrieve": true,
    "processing": true,
    "language": {
      "sProcessing": "Procesando...",
      "sLengthMenu": "Mostrar _MENU_ registros",
      "sZeroRecords": "No se encontraron resultados",
      "sEmptyTable": "Ningún dato disponible",
      "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
      "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
      "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
      "sSearch": "Buscar:",
      "oPaginate": {
        "sFirst": "Primero",
        "sLast": "Último",
        "sNext": "Siguiente",
        "sPrevious": "Anterior"
      }
    }
  });

  $('#filtroProveedor, #filtroMes').change(function(){
    tablaGastos.ajax.reload();
  });

  $('#tipoGasto').change(function () {
    const tipo = $(this).val();
    if (tipo === 'proveedor') {
      $('#grupoProveedor').show();
    } else {
      $('#grupoProveedor').hide();
      $('#grupoProveedor select').val('');
    }
  });

  $(document).ready(function () {
    if ($('#tipoGasto').val() !== 'proveedor') {
      $('#grupoProveedor').hide();
    }
  });

  $(document).on("click", ".btnEditarGasto", function() {
    let idGasto = $(this).attr("idGasto");

    $.ajax({
      url: "ajax/gastos.ajax.php",
      method: "POST",
      data: { idGasto: idGasto },
      dataType: "json",
      success: function(respuesta) {
        $("#idGastoEditar").val(respuesta.id);
        $("#descripcionGastoEditar").val(respuesta.descripcion);
        $("#montoEditar").val(respuesta.monto);
        $("#fechaGastoEditar").val(respuesta.fecha);
      }
    });
  });

  // Eliminar gasto
 $(document).on("click", ".btnEliminarGasto", function () {
  let idGasto = $(this).attr("idGasto");

  let confirmar = confirm("¿Estás seguro de que deseas eliminar este gasto? Esta acción no se puede deshacer.");

  if (confirmar) {
    $.ajax({
      url: "ajax/gastos.ajax.php",
      method: "POST",
      data: { idGastoEliminar: idGasto },
      dataType: "json",
      success: function (respuesta) {
        if (respuesta.status === "ok") {
          alert("Gasto eliminado correctamente.");
          tablaGastos.ajax.reload();
        } else {
          alert("Error al eliminar el gasto.");
        }
      },
      error: function (xhr, status, error) {
        console.error("Error en AJAX:", status, error);
        alert("Ocurrió un error al intentar eliminar el gasto.");

      }
          
        });
      }
    });
</script>

