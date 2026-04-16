<?php

class ControladorControlSanitario{

    /*=============================================
    CREAR REGISTRO
    =============================================*/

    static public function ctrCrearSanitario(){

        if(isset($_POST["idVentaSanitaria"])){

            $tabla = "control_sanitario";

            $datos = array(
                "id_animal" => $_POST["idAnimal"],
                "id_venta" => $_POST["idVentaSanitaria"],
                "producto" => $_POST["productoSanitario"],
                "tipo" => $_POST["tipoSanitario"],
                "dosis" => $_POST["dosisSanitaria"],
                "fecha_aplicacion" => $_POST["fechaAplicacion"],
                "proxima_aplicacion" => $_POST["proximaDosis"],
                "observaciones" => $_POST["observacionesSanitaria"]
            );

            $respuesta = ModeloControlSanitario::mdlIngresarSanitario($tabla, $datos);

            if($respuesta == "ok"){

                echo'<script>
                    swal({
                        type: "success",
                        title: "Registro guardado correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "control-sanitario";
                        }
                    });
                </script>';
            }
        }
    }


    /*=============================================
    MOSTRAR REGISTROS
    =============================================*/

    static public function ctrMostrarSanitario(){

        $tabla = "control_sanitario";

        return ModeloControlSanitario::mdlMostrarSanitario($tabla);

    }

}