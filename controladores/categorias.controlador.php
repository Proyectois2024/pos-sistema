<?php

class ControladorCategorias {

    /*=============================================
    CREAR CATEGORIA
    =============================================*/
    static public function ctrCrearCategoria() {

        if (isset($_POST["nuevaCategoria"])) {

            $categoria = trim($_POST["nuevaCategoria"]);

            // Permitir letras, números, espacios y caracteres comunes (&, -, /, ., ,)
            if (!empty($categoria) && preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s\&\-\/\.\,]+$/', $categoria)) {

                $tabla = "categorias";

                $respuesta = ModeloCategorias::mdlIngresarCategoria($tabla, $categoria);

                if ($respuesta == "ok") {

                    echo '<script>
                        swal({
                            type: "success",
                            title: "La categoría ha sido guardada correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location = "categorias";
                            }
                        });
                    </script>';

                } else {

                    echo '<script>
                        swal({
                            type: "error",
                            title: "Error al guardar la categoría",
                            text: "Ocurrió un error en la base de datos.",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';

                }

            } else {

                echo '<script>
                    swal({
                        type: "error",
                        title: "¡La categoría no puede ir vacía o llevar caracteres no permitidos!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';

            }
        }
    }

    /*=============================================
    MOSTRAR CATEGORIAS
    =============================================*/
    static public function ctrMostrarCategorias($item = null, $valor = null) {

        $tabla = "categorias";

        return ModeloCategorias::mdlMostrarCategorias($tabla, $item, $valor);

    }

    /*=============================================
    EDITAR CATEGORIA
    =============================================*/
    static public function ctrEditarCategoria() {

        if (isset($_POST["editarCategoria"])) {

            $categoria  = trim($_POST["editarCategoria"]);
            $idCategoria = isset($_POST["idCategoria"]) ? (int)$_POST["idCategoria"] : 0;

            if ($idCategoria > 0 && !empty($categoria) && preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s\&\-\/\.\,]+$/', $categoria)) {

                $tabla = "categorias";

                $datos = array(
                    "categoria" => $categoria,
                    "id"        => $idCategoria
                );

                $respuesta = ModeloCategorias::mdlEditarCategoria($tabla, $datos);

                if ($respuesta == "ok") {

                    echo '<script>
                        swal({
                            type: "success",
                            title: "La categoría ha sido cambiada correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location = "categorias";
                            }
                        });
                    </script>';

                } else {

                    echo '<script>
                        swal({
                            type: "error",
                            title: "Error al actualizar la categoría",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';

                }

            } else {

                echo '<script>
                    swal({
                        type: "error",
                        title: "¡La categoría no puede ir vacía o llevar caracteres no permitidos!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';

            }
        }
    }

    /*=============================================
    BORRAR CATEGORIA
    =============================================*/
    static public function ctrBorrarCategoria() {

        if (isset($_GET["idCategoria"])) {

            $tabla = "categorias";
            $idCategoria = (int)$_GET["idCategoria"];

            if ($idCategoria > 0) {

                $respuesta = ModeloCategorias::mdlBorrarCategoria($tabla, $idCategoria);

                if ($respuesta == "ok") {

                    echo '<script>
                        swal({
                            type: "success",
                            title: "La categoría ha sido borrada correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location = "categorias";
                            }
                        });
                    </script>';

                }
            }
        }
    }
}
