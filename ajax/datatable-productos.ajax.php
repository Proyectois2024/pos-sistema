<?php
session_start();

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

require_once "../controladores/categorias.controlador.php";
require_once "../modelos/categorias.modelo.php";

class TablaProductos {

    public function mostrarTablaProductos() {

        $item = null;
        $valor = null;
        $orden = "id";

        $productos = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

        if(!$productos || count($productos) == 0){
            echo json_encode(["data" => []]);
            return;
        }

        $modoCotizacion = isset($_GET["modo"]) && $_GET["modo"] == "cotizacion";

        $data = [];

        foreach ($productos as $i => $producto) {

            $imagen = !empty($producto["imagen"])
                ? "<img src='".$producto["imagen"]."' width='40px'>"
                : "";

            $categoria = !empty($producto["categoria"])
                ? $producto["categoria"]
                : "Sin categoría";

            $stockActual = (int)$producto["stock"];
            $stockMinimo = isset($producto["stock_minimo"]) ? (int)$producto["stock_minimo"] : 0;

            if($stockMinimo > 0){
                if($stockActual <= ($stockMinimo/3)){
                    $claseStock = "btn-danger";
                }elseif($stockActual <= ($stockMinimo/2)){
                    $claseStock = "btn-warning";
                }else{
                    $claseStock = "btn-success";
                }
            }else{
                $claseStock = "btn-success";
            }

            $stockFinal = "<button class='btn ".$claseStock." btn-xs'>".$stockActual."</button>";

            $fechaVencimiento = !empty($producto["fecha_vencimiento"])
                ? $producto["fecha_vencimiento"]
                : "No definida";

            if($modoCotizacion){

                $botones = "<div class='btn-group'>
                    <button type='button' class='btn btn-primary agregarProductoCotizacion'
                        idProducto='".$producto["id"]."'>
                        <i class='fa fa-plus'></i>
                    </button>
                </div>";

            }else{

                $botones = "<div class='btn-group'>

                    <button class='btn btn-warning btnEditarProducto'
                        idProducto='".$producto["id"]."'
                        data-toggle='modal'
                        data-target='#modalEditarProducto'>
                        <i class='fa fa-pencil'></i>
                    </button>

                    <button class='btn btn-danger btnEliminarProducto'
                        idProducto='".$producto["id"]."'
                        codigo='".$producto["codigo"]."'
                        imagen='".$producto["imagen"]."'>
                        <i class='fa fa-times'></i>
                    </button>

                </div>";
            }

            $data[] = [
                ($i+1),
                $imagen,
                $producto["codigo"],
                $producto["descripcion"],
                $categoria,
                $stockFinal,
                "Q ".number_format((float)$producto["precio_compra"], 2),
                "Q ".number_format((float)$producto["precio_venta"], 2),
                $fechaVencimiento,
                $producto["fecha"],
                $botones
            ];
        }

        echo json_encode(["data" => $data]);
    }
}

$activarProductos = new TablaProductos();
$activarProductos->mostrarTablaProductos();