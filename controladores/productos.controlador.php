<?php

class ControladorProductos {

  /*=============================================
  FUNCIÓN AUXILIAR: SUBIR IMAGEN A CLOUDINARY
  =============================================*/
  static private function subirACloudinary($archivoTemporal, $nombreArchivo) {
    // Reemplaza esto con el nombre de tu Preset 'Unsigned' creado en Cloudinary
    $uploadPreset = "pos_preset"; 
    $cloudName = "pkpk2vjr";

    $cFile = curl_file_create($archivoTemporal, $_FILES[$nombreArchivo]["type"], $_FILES[$nombreArchivo]["name"]);

    $postData = array(
      'file' => $cFile,
      'upload_preset' => $uploadPreset,
      'folder' => 'pos_productos' // Opcional: Organiza las imágenes dentro de esta carpeta en Cloudinary
    );

    $ch = curl_init("https://api.cloudinary.com/v1_1/" . $cloudName . "/image/upload");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($response['secure_url'])) {
      return $response['secure_url'];
    }

    return null;
  }

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

  /*=============================================
  CREAR PRODUCTO
  =============================================*/
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
          
          $urlCloudinary = self::subirACloudinary($_FILES["nuevaImagen"]["tmp_name"], "nuevaImagen");
          
          if ($urlCloudinary != null) {
            $ruta = $urlCloudinary;
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

  /*=============================================
  EDITAR PRODUCTO
  =============================================*/
  static public function ctrEditarProducto() {

    if (isset($_POST["editarDescripcion"])) {

      $ruta = $_POST["imagenActual"];

      if (isset($_FILES["editarImagen"]["tmp_name"]) && !empty($_FILES["editarImagen"]["tmp_name"])) {

        $urlCloudinary = self::subirACloudinary($_FILES["editarImagen"]["tmp_name"], "editarImagen");

        if ($urlCloudinary != null) {
          $ruta = $urlCloudinary;
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

  /*=============================================
  ELIMINAR PRODUCTO
  =============================================*/
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
