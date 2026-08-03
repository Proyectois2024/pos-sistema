<?php

class ControladorClientes {

    /*=============================================
    CREAR CLIENTES
    =============================================*/
    static public function ctrCrearCliente() {

        if (isset($_POST["nuevoCliente"])) {

            $nombre     = trim($_POST["nuevoCliente"]);
            $documento  = isset($_POST["nuevoDocumentoId"]) ? trim($_POST["nuevoDocumentoId"]) : "";
            $email      = isset($_POST["nuevoEmail"]) ? trim($_POST["nuevoEmail"]) : "";
            $telefono   = isset($_POST["nuevoTelefono"]) ? trim($_POST["nuevoTelefono"]) : "";
            $direccion  = isset($_POST["nuevaDireccion"]) ? trim($_POST["nuevaDireccion"]) : "";
            $fechaNac   = !empty($_POST["nuevaFechaNacimiento"]) ? trim($_POST["nuevaFechaNacimiento"]) : null;

            // Validaciones
            $validNombre    = preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s\.\,\'-]+$/', $nombre);
            $validDocumento = empty($documento) || preg_match('/^[a-zA-Z0-9\-]+$/', $documento);
            $validEmail     = empty($email) || filter_var($email, FILTER_VALIDATE_EMAIL);
            $validTelefono  = empty($telefono) || preg_match('/^[()\-0-9\+\s]+$/', $telefono);

            if ($validNombre && $validDocumento && $validEmail && $validTelefono) {

                $tabla = "clientes";

                $datos = array(
                    "nombre"           => $nombre,
                    "documento"        => $documento,
                    "email"            => $email,
                    "telefono"         => $telefono,
                    "direccion"        => $direccion,
                    "fecha_nacimiento" => $fechaNac
                );

                $respuesta = ModeloClientes::mdlIngresarCliente($tabla, $datos);

                if ($respuesta == "ok") {
                    echo '<script>
                        swal({
                            type: "success",
                            title: "El cliente ha sido guardado correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location = "clientes";
                            }
                        });
                    </script>';
                } else {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "Error al guardar el cliente",
                            text: "Ocurrió un problema en la base de datos.",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                }

            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡El cliente no puede ir vacío o lleva un formato inválido!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    MOSTRAR CLIENTES
    =============================================*/
    static public function ctrMostrarClientes($item = null, $valor = null) {

        $tabla = "clientes";
        return ModeloClientes::mdlMostrarClientes($tabla, $item, $valor);

    }

    /*=============================================
    EDITAR CLIENTE
    =============================================*/
    static public function ctrEditarCliente() {

        if (isset($_POST["editarCliente"])) {

            $idCliente  = (int)$_POST["idCliente"];
            $nombre     = trim($_POST["editarCliente"]);
            $documento  = isset($_POST["editarDocumentoId"]) ? trim($_POST["editarDocumentoId"]) : "";
            $email      = isset($_POST["editarEmail"]) ? trim($_POST["editarEmail"]) : "";
            $telefono   = isset($_POST["editarTelefono"]) ? trim($_POST["editarTelefono"]) : "";
            $direccion  = isset($_POST["editarDireccion"]) ? trim($_POST["editarDireccion"]) : "";
            $fechaNac   = !empty($_POST["editarFechaNacimiento"]) ? trim($_POST["editarFechaNacimiento"]) : null;

            // Validaciones
            $validNombre    = preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s\.\,\'-]+$/', $nombre);
            $validDocumento = empty($documento) || preg_match('/^[a-zA-Z0-9\-]+$/', $documento);
            $validEmail     = empty($email) || filter_var($email, FILTER_VALIDATE_EMAIL);
            $validTelefono  = empty($telefono) || preg_match('/^[()\-0-9\+\s]+$/', $telefono);

            if ($idCliente > 0 && $validNombre && $validDocumento && $validEmail && $validTelefono) {

                $tabla = "clientes";

                $datos = array(
                    "id"               => $idCliente,
                    "nombre"           => $nombre,
                    "documento"        => $documento,
                    "email"            => $email,
                    "telefono"         => $telefono,
                    "direccion"        => $direccion,
                    "fecha_nacimiento" => $fechaNac
                );

                $respuesta = ModeloClientes::mdlEditarCliente($tabla, $datos);

                if ($respuesta == "ok") {
                    echo '<script>
                        swal({
                            type: "success",
                            title: "El cliente ha sido cambiado correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location = "clientes";
                            }
                        });
                    </script>';
                } else {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "Error al actualizar el cliente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                }

            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡El cliente no puede ir vacío o lleva un formato inválido!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    ELIMINAR CLIENTE
    =============================================*/
    static public function ctrEliminarCliente() {

        if (isset($_GET["idCliente"])) {

            $tabla = "clientes";
            $datos = (int)$_GET["idCliente"];

            if ($datos > 0) {
                $respuesta = ModeloClientes::mdlEliminarCliente($tabla, $datos);

                if ($respuesta == "ok") {
                    echo '<script>
                        swal({
                            type: "success",
                            title: "El cliente ha sido borrado correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location = "clientes";
                            }
                        });
                    </script>';
                }
            }
        }
    }
}
