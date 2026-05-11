<?php
require_once __DIR__ . "/../modelos/proveedores.modelo.php";


class ControladorProveedores {

  /*=============================================
  CREAR PROVEEDOR
  =============================================*/
  static public function ctrCrearProveedor() {

    if (isset($_POST["nuevoProveedor"])) {

      if (
        preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoProveedor"])
&& (empty($_POST["nuevaEmpresa"]) || preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevaEmpresa"]))
&& (empty($_POST["nuevoEmail"]) || preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $_POST["nuevoEmail"]))
&& (empty($_POST["nuevoTelefono"]) || preg_match('/^[()\-0-9 ]+$/', $_POST["nuevoTelefono"]))
&& (empty($_POST["nuevaDireccion"]) || preg_match('/^[#\.\-a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevaDireccion"]))

      ) {

        $tabla = "proveedores";

        $datos = array(
          "nombre"    => $_POST["nuevoProveedor"],
          "empresa" => $_POST["nuevaEmpresa"],
          "email"     => $_POST["nuevoEmail"],
          "telefono"  => $_POST["nuevoTelefono"],
          "direccion" => $_POST["nuevaDireccion"]
        );

        $respuesta = ModeloProveedores::mdlIngresarProveedor($tabla, $datos);

        if ($respuesta == "ok") {
          echo '<script>
            swal({
              type: "success",
              title: "El proveedor ha sido guardado correctamente",
              showConfirmButton: true,
              confirmButtonText: "Cerrar"
            }).then(function(result){
              if (result.value) {
                window.location = "proveedores";
              }
            });
          </script>';
        }

      } else {
        echo '<script>
          swal({
            type: "error",
            title: "¡El proveedor no puede ir vacío o llevar caracteres especiales!",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
          }).then(function(result){
            if (result.value) {
              window.location = "proveedores";
            }
          });
        </script>';
      }
    }
  }

  /*=============================================
  MOSTRAR PROVEEDORES
  =============================================*/
  static public function ctrMostrarProveedores($item, $valor) {

    $tabla = "proveedores";

    $respuesta = ModeloProveedores::mdlMostrarProveedores($tabla, $item, $valor);

    return $respuesta;
  }

  /*=============================================
  EDITAR PROVEEDOR
  =============================================*/
  static public function ctrEditarProveedor() {

if (isset($_POST["editarProveedor"])) {

if (
  preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarProveedor"]) &&
  (empty($_POST["editarEmpresa"]) || preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarEmpresa"])) &&
  (empty($_POST["editarEmail"]) || preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $_POST["editarEmail"])) &&
  (empty($_POST["editarTelefono"]) || preg_match('/^[()\-0-9 ]+$/', $_POST["editarTelefono"])) &&
  (empty($_POST["editarDireccion"]) || preg_match('/^[#\.\-a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarDireccion"]))
) {

  $tabla = "proveedores";

  $datos = array(
    "id"        => $_POST["idProveedor"],
    "nombre"    => $_POST["editarProveedor"],
    "empresa"   => $_POST["editarEmpresa"],
    "email"     => $_POST["editarEmail"],
    "telefono"  => $_POST["editarTelefono"],
    "direccion" => $_POST["editarDireccion"]
  );

  $respuesta = ModeloProveedores::mdlEditarProveedor($tabla, $datos);

  if ($respuesta == "ok") {
    echo '<script>
      swal({
        type: "success",
        title: "El proveedor ha sido modificado correctamente",
        showConfirmButton: true,
        confirmButtonText: "Cerrar"
      }).then(function(result){
        if (result.value) {
          window.location = "proveedores";
        }
      });
    </script>';
  }

} else {
  echo '<script>
    swal({
      type: "error",
      title: "¡El proveedor no puede ir vacío o llevar caracteres especiales!",
      showConfirmButton: true,
      confirmButtonText: "Cerrar"
    }).then(function(result){
      if (result.value) {
        window.location = "proveedores";
      }
    });
  </script>';
}
}
}

  /*=============================================
  ELIMINAR PROVEEDOR
  =============================================*/
  static public function ctrEliminarProveedor() {

    if (isset($_GET["idProveedor"])) {

      $tabla = "proveedores";
      $datos = $_GET["idProveedor"];

      $respuesta = ModeloProveedores::mdlEliminarProveedor($tabla, $datos);

      if ($respuesta == "ok") {
        echo '<script>
          swal({
            type: "success",
            title: "El proveedor ha sido eliminado correctamente",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
          }).then(function(result){
            if (result.value) {
              window.location = "proveedores";
            }
          });
        </script>';
      }
    }
  }
}

?>
