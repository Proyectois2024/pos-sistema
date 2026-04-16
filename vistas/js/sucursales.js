$(document).on("change", ".selectSucursal", function(){

  var idSucursal = $(this).val();

  $.ajax({
    url: "ajax/sucursales.ajax.php",
    method: "POST",
    data: { idSucursal: idSucursal },
    success: function(respuesta){

      if($.trim(respuesta) === "ok"){
        window.location = "inicio";
      }else{
        alert("No se pudo cambiar la sucursal");
      }

    },
    error: function(){
      alert("Error al cambiar la sucursal");
    }
  });

});