<?php

class ControladorProductos {

 /*=============================================
MOSTRAR PRODUCTOS
=============================================*/
static public function ctrMostrarProductos($item, $valor, $orden) {

  $tabla = "productos";
  return ModeloProductos::mdlMostrarProductos($tabla, $item, $valor, $orden);
}

  static public function ctrContarProductosPorSucursal(){

  $tabla = "productos";
  $respuesta = ModeloProductos::mdlContarProductosPorSucursal($tabla);

  return $respuesta;
}

  static public function ctrCrearProducto() {

  if (isset($_POST["nuevaDescripcion"])) {

    if (
      preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevaDescripcion"]) &&
      is_numeric($_POST["nuevoStock"]) &&
      preg_match('/^[0-9.]+$/', $_POST["nuevoPrecioCompra"]) &&
      preg_match('/^[0-9.]+$/', $_POST["nuevoPrecioVenta"])
    ) {

      $ruta = "vistas/img/productos/default/anonymous.png";

      if (isset($_FILES["nuevaImagen"]["tmp_name"]) && !empty($_FILES["nuevaImagen"]["tmp_name"])) {

        list($ancho, $alto) = getimagesize($_FILES["nuevaImagen"]["tmp_name"]);
        $nuevoAncho = 500;
        $nuevoAlto = 500;

        $directorio = "vistas/img/productos/" . $_POST["nuevoCodigo"];
        if(!is_dir($directorio)){
          mkdir($directorio, 0755, true);
        }

        $aleatorio = mt_rand(100, 999);

        if ($_FILES["nuevaImagen"]["type"] == "image/jpeg") {

          $ruta = $directorio . "/" . $aleatorio . ".jpg";
          $origen = imagecreatefromjpeg($_FILES["nuevaImagen"]["tmp_name"]);
          $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
          imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
          imagejpeg($destino, $ruta);

        } else if ($_FILES["nuevaImagen"]["type"] == "image/png") {

          $ruta = $directorio . "/" . $aleatorio . ".png";
          $origen = imagecreatefrompng($_FILES["nuevaImagen"]["tmp_name"]);
          $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
          imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
          imagepng($destino, $ruta);
        }
      }

      $tabla = "productos";

      $datos = array(
        "id_categoria" => $_POST["nuevaCategoria"],
        "codigo" => $_POST["nuevoCodigo"],
        "descripcion" => $_POST["nuevaDescripcion"],
        "stock" => $_POST["nuevoStock"],
        "stock_minimo" => $_POST["nuevoStockMinimo"],
        "precio_compra" => $_POST["nuevoPrecioCompra"],
        "precio_venta" => $_POST["nuevoPrecioVenta"],
        "tipo_sanitario" => $_POST["nuevoTipoSanitario"],
        "id_sucursal" => $_SESSION["id_sucursal"],
        "imagen" => $ruta,
        "fecha_vencimiento" => !empty($_POST["nuevaFechaVencimiento"]) ? $_POST["nuevaFechaVencimiento"] : null,
      );

      $respuesta = ModeloProductos::mdlIngresarProducto($tabla, $datos);

      if ($respuesta == "ok") {
        echo '<script>
          swal({
            type: "success",
            title: "Producto creado correctamente",
            confirmButtonText: "Cerrar"
          }).then(function(result){
            if (result.value) {
              window.location = "productos";
            }
          })
        </script>';
      }
    }
  }
}

  static public function ctrEditarProducto() {

  if (isset($_POST["editarDescripcion"])) {

    $ruta = $_POST["imagenActual"];

    if (isset($_FILES["editarImagen"]["tmp_name"]) && !empty($_FILES["editarImagen"]["tmp_name"])) {

      list($ancho, $alto) = getimagesize($_FILES["editarImagen"]["tmp_name"]);
      $nuevoAncho = 500;
      $nuevoAlto = 500;

      $directorio = "vistas/img/productos/" . $_POST["editarCodigo"];

      if (!empty($_POST["imagenActual"]) && $_POST["imagenActual"] != "vistas/img/productos/default/anonymous.png") {
        if(file_exists($_POST["imagenActual"])){
          unlink($_POST["imagenActual"]);
        }
      } else {
        if(!is_dir($directorio)){
          mkdir($directorio, 0755, true);
        }
      }

      $aleatorio = mt_rand(100, 999);

      if ($_FILES["editarImagen"]["type"] == "image/jpeg") {

        $ruta = $directorio . "/" . $aleatorio . ".jpg";
        $origen = imagecreatefromjpeg($_FILES["editarImagen"]["tmp_name"]);
        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
        imagejpeg($destino, $ruta);

      } else if ($_FILES["editarImagen"]["type"] == "image/png") {

        $ruta = $directorio . "/" . $aleatorio . ".png";
        $origen = imagecreatefrompng($_FILES["editarImagen"]["tmp_name"]);
        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
        imagepng($destino, $ruta);
      }
    }

    $tabla = "productos";

    $datos = array(
      "id_categoria" => $_POST["editarCategoria"],
      "codigo" => $_POST["editarCodigo"],
      "descripcion" => $_POST["editarDescripcion"],
      "stock" => $_POST["editarStock"],
      "stock_minimo" => $_POST["editarStockMinimo"],
      "precio_compra" => $_POST["editarPrecioCompra"],
      "precio_venta" => $_POST["editarPrecioVenta"],
      "imagen" => $ruta,
      "fecha_vencimiento" => !empty($_POST["editarFechaVencimiento"]) ? $_POST["editarFechaVencimiento"] : null,
    );

    $respuesta = ModeloProductos::mdlEditarProducto($tabla, $datos);

    if ($respuesta == "ok") {
      echo '<script>
        swal({
          type: "success",
          title: "Producto editado correctamente",
          confirmButtonText: "Cerrar"
        }).then(function(result){
          if (result.value) {
            window.location = "productos";
          }
        })
      </script>';
    }
  }
}
 static public function ctrEliminarProducto() {

  if (isset($_GET["idProducto"])) {

    $tabla = "productos";
    $id = $_GET["idProducto"];

    $respuesta = ModeloProductos::mdlEliminarProducto($tabla, $id);

    if($respuesta == "ok"){
      echo '<script>
        swal({
          type: "success",
          title: "Producto eliminado",
          confirmButtonText: "Cerrar"
        }).then(function(result){
          if (result.value) {
            window.location = "productos";
          }
        })
      </script>';
    } else {
      echo '<script>
        swal({
          type: "error",
          title: "No se pudo eliminar el producto",
          text: "Verifica que pertenezca a la sucursal actual",
          confirmButtonText: "Cerrar"
        })
      </script>';
    }
  }
}

    /*=============================================
  MOSTRAR TOTAL PRODUCTOS STOCK BAJO
  =============================================*/
  static public function ctrMostrarSumaStockBajo(){
    $tabla = "productos";
    $respuesta = ModeloProductos::mdlMostrarProductosStockBajo($tabla, 5);
    return $respuesta;
  }

  /*=============================================
  MOSTRAR TOTAL PRODUCTOS STOCK BAJO
  =============================================*/
  static public function ctrMostrarProductosStockBajo(){
    $tabla = "productos";
    $limite = 5;
    $respuesta = ModeloProductos::mdlMostrarProductosStockBajo($tabla, $limite);
    return $respuesta;
  }
}