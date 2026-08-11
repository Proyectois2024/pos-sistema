$(document).ready(function(){

    if($(".tablaProductosCotizacion").length){

        $(".tablaProductosCotizacion").DataTable({
            "ajax": {
                "url": "ajax/datatable-productos.ajax.php?modo=cotizacion",
                "type": "GET"
            },
            "deferRender": true,
            "retrieve": true,
            "processing": true,
            "language": {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningún dato disponible",
                "sInfo": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando 0 a 0 de 0 registros",
                "sSearch": "Buscar:"
            }
        });

    }

});

/*=============================================
RECALCULAR TOTAL
=============================================*/
function recalcularTotalCotizacion(){

    var total = 0;

    $(".subtotalCotizacion").each(function(){
        total += parseFloat($(this).val()) || 0;
    });

    $("#totalDocumento").val(total.toFixed(2));
}

/*=============================================
LISTAR PRODUCTOS JSON
=============================================*/
function listarProductosCotizacion(){

    var productos = [];

    $(".filaProductoCotizacion").each(function(){

        var fila = $(this);

        productos.push({
            id_producto: fila.attr("idProducto"),
            descripcion: fila.find(".descripcionCotizacion").val(),
            cantidad: fila.find(".cantidadCotizacion").val(),
            unidad: fila.find(".unidadCotizacion").val(),
            precio: fila.find(".precioCotizacion").val(),
            subtotal: fila.find(".subtotalCotizacion").val()
        });

    });

    $("#productosJsonCotizacion").val(JSON.stringify(productos));
}

/*=============================================
AGREGAR PRODUCTO DESDE TABLA
=============================================*/
$(document).on("click", ".agregarProductoCotizacion", function(){

    var idProducto = $(this).attr("idProducto");

    if($('.filaProductoCotizacion[idProducto="'+idProducto+'"]').length){
        return;
    }

    var datos = new FormData();
    datos.append("idProducto", idProducto);

    $.ajax({
        url: "ajax/productos.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta){

            var descripcion = respuesta["descripcion"];
            var precio = parseFloat(respuesta["precio_venta"]) || 0;

            var fila = '<tr class="filaProductoCotizacion" idProducto="'+idProducto+'">'+
                '<td><input type="text" class="form-control descripcionCotizacion" name="descripcion[]" value="'+descripcion+'" readonly required></td>'+
                '<td><input type="number" class="form-control cantidadCotizacion" name="cantidad[]" value="1" min="1" step="any" required></td>'+
                '<td><input type="text" class="form-control unidadCotizacion" name="unidad[]" placeholder="Ej: Unidad, Caja, Quintal"></td>'+
                '<td><input type="number" class="form-control precioCotizacion" name="precio[]" value="'+precio.toFixed(2)+'" min="0" step="any" required></td>'+
                '<td><input type="number" class="form-control subtotalCotizacion" name="subtotal[]" value="'+precio.toFixed(2)+'" readonly></td>'+
                '<td><button type="button" class="btn btn-danger btnQuitarProductoCotizacion"><i class="fa fa-times"></i></button></td>'+
            '</tr>';

            $(".listaProductosCotizacion").append(fila);

            recalcularTotalCotizacion();
            listarProductosCotizacion();
        },
        error: function(xhr){
            console.log("Error AJAX productos:", xhr.responseText);
        }
    });

});

/*=============================================
CAMBIAR CANTIDAD O PRECIO
=============================================*/
$(document).on("input", ".cantidadCotizacion, .precioCotizacion", function(){

    var fila = $(this).closest(".filaProductoCotizacion");
    var cantidad = parseFloat(fila.find(".cantidadCotizacion").val()) || 0;
    var precio = parseFloat(fila.find(".precioCotizacion").val()) || 0;
    var subtotal = cantidad * precio;

    fila.find(".subtotalCotizacion").val(subtotal.toFixed(2));

    recalcularTotalCotizacion();
    listarProductosCotizacion();
});

/*=============================================
QUITAR PRODUCTO
=============================================*/
$(document).on("click", ".btnQuitarProductoCotizacion", function(){

    $(this).closest(".filaProductoCotizacion").remove();

    recalcularTotalCotizacion();
    listarProductosCotizacion();
});

/*=============================================
ELIMINAR COTIZACION / PEDIDO
=============================================*/
$(document).on("click", ".btnEliminarCotizacion", function(){

  var idCotizacion = $(this).attr("idCotizacion");

  swal({
    title: '¿Está seguro de borrar el documento?',
    text: "¡Si no lo está puede cancelar la acción!",
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    cancelButtonText: 'Cancelar',
    confirmButtonText: 'Sí, borrar!'
  }).then(function(result){

    if(result.value){
      window.location = "index.php?ruta=cotizaciones&idCotizacion=" + idCotizacion;
    }

  });

});
