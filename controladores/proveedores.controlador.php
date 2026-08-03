<?php

require_once __DIR__ . "/../modelos/proveedores.modelo.php";

class ControladorProveedores {

    /*=============================================
    CREAR PROVEEDOR
    =============================================*/
    static public function ctrCrearProveedor() {

        if (isset($_POST["nuevoProveedor"])) {

            $nombre    = trim($_POST["nuevoProveedor"]);
            $empresa   = isset($_POST["nuevaEmpresa"]) ? trim($_POST["nuevaEmpresa"]) : "";
            $email     = isset($_POST["nuevoEmail"]) ? trim($_POST["nuevoEmail"]) : "";
            $telefono  = isset($_POST["nuevoTelefono"]) ? trim($_POST["nuevoTelefono"]) : "";
            $direccion = isset($_POST["nuevaDireccion"]) ? trim($_POST["nuevaDireccion"]) : "";

            // Validaciones
            $validNombre    = preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s\.\,\-]+$/', $nombre);
            $validEmpresa   = empty($empresa) || preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s\.\,\&\-]+$/', $empresa);
            $validEmail     = empty($email) || filter_var($email, FILTER_VALIDATE_EMAIL);
            $validTelefono  = empty($telefono) || preg_match('/^[()\-0-9\+\s]+$/', $telefono);
            $validDireccion = empty($direccion) || preg_match('/^[#\.\,\-a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s]+$/', $direccion);

            if ($validNombre && $validEmpresa && $validEmail && $validTelefono && $validDireccion) {

                $tabla = "proveedores";

                $datos = array(
                    "nombre"    => $nombre,
                    "empresa"   => $empresa,
                    "email"     => $email,
                    "telefono"  => $telefono,
                    "direccion" => $direccion
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
                        title: "¡El proveedor no puede ir vacío o llevar caracteres especiales no válidos!",
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
    static public function ctrMostrarProveedores($item = null, $valor = null) {

        $tabla = "proveedores";

        return ModeloProveedores::mdlMostrarProveedores($tabla, $item, $valor);
    }

    /*=============================================
    EDITAR PROVEEDOR
    =============================================*/
    static public function ctrEditarProveedor() {

        if (isset($_POST["editarProveedor"])) {

            $id        = (int)$_POST["idProveedor"];
            $nombre    = trim($_POST["editarProveedor"]);
            $empresa   = isset($_POST["editarEmpresa"]) ? trim($_POST["editarEmpresa"]) : "";
            $email     = isset($_POST["editarEmail"]) ? trim($_POST["editarEmail"]) : "";
            $telefono  = isset($_POST["editarTelefono"]) ? trim($_POST["editarTelefono"]) : "";
            $direccion = isset($_POST["editarDireccion"]) ? trim($_POST["editarDireccion"]) : "";

            // Validaciones
            $validNombre    = preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s\.\,\-]+$/', $nombre);
            $validEmpresa   = empty($empresa) || preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s\.\,\&\-]+$/', $empresa);
            $validEmail     = empty($email) || filter_var($email, FILTER_VALIDATE_EMAIL);
            $validTelefono  = empty($telefono) || preg_match('/^[()\-0-9\+\s]+$/', $telefono);
            $validDireccion = empty($direccion) || preg_match('/^[#\.\,\-a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s]+$/', $direccion);

            if ($id > 0 && $validNombre && $validEmpresa && $validEmail && $validTelefono && $validDireccion) {

                $tabla = "proveedores";

                $datos = array(
                    "id"        => $id,
                    "nombre"    => $nombre,
                    "empresa"   => $empresa,
                    "email"     => $email,
                    "telefono"  => $telefono,
                    "direccion" => $direccion
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
                        title: "¡El proveedor no puede ir vacío o llevar caracteres especiales no válidos!",
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
            $datos = (int)$_GET["idProveedor"];

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
