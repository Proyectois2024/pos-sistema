<?php
session_start();

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

require_once "../controladores/categorias.controlador.php";
require_once "../modelos/categorias.modelo.php";

class AjaxProductos{

  public $idCategoria;

  public function ajaxCrearCodigoProducto(){

    $item = "id_categoria";
    $valor = $this->idCategoria;
    $orden = "id";

    $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

    echo json_encode($respuesta);
  }

  public $idProducto;
  public $traerProductos;
  public $nombreProducto;

  public function ajaxEditarProducto(){

    if($this->traerProductos == "ok"){

      $item = null;
      $valor = null;
      $orden = "id";

      $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

      echo json_encode($respuesta);

    }else if($this->nombreProducto != ""){

      $item = "descripcion";
      $valor = $this->nombreProducto;
      $orden = "id";

      $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

      echo json_encode($respuesta);

    }else{

      $item = "id";
      $valor = $this->idProducto;
      $orden = "id";

      $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

      echo json_encode($respuesta);
    }
  }
  if(isset($_POST["codigoProducto"])){

  require_once "../controladores/productos.controlador.php";
  require_once "../modelos/productos.modelo.php";

  $item = "codigo";
  $valor = $_POST["codigoProducto"];
  $orden = "id";

  $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

  echo json_encode($respuesta);
}
}

if(isset($_POST["idCategoria"])){

  $codigoProducto = new AjaxProductos();
  $codigoProducto->idCategoria = $_POST["idCategoria"];
  $codigoProducto->ajaxCrearCodigoProducto();
}

if(isset($_POST["idProducto"])){

  $editarProducto = new AjaxProductos();
  $editarProducto->idProducto = $_POST["idProducto"];
  $editarProducto->ajaxEditarProducto();
}

if(isset($_POST["traerProductos"])){

  $traerProductos = new AjaxProductos();
  $traerProductos->traerProductos = $_POST["traerProductos"];
  $traerProductos->ajaxEditarProducto();
}

if(isset($_POST["nombreProducto"])){

  $traerProductos = new AjaxProductos();
  $traerProductos->nombreProducto = $_POST["nombreProducto"];
  $traerProductos->ajaxEditarProducto();
}
