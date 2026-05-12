<?php

class ControladorCompras {

  public function ctrCrearCompra() {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    if (isset($_POST["idProveedor"]) && isset($_POST["productos"]) && !empty($_POST["productos"])) {

      $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;

      if($idSucursal <= 0){
        echo '<script>
          swal({
            type: "error",
            title: "El usuario no tiene sucursal asignada",
            confirmButtonText: "Cerrar"
          }).then(function(result){
            if(result.value){
              window.location = "proveedores";
            }
          });
        </script>';
        return;
      }

      $datosCompra = array(
        "id_proveedor" => (int)$_POST["idProveedor"],
        "id_sucursal" => $idSucursal,
        "fecha_compra" => $_POST["fechaCompra"]
      );

      $idCompra = ModeloCompras::mdlCrearCompra("compras", $datosCompra);

      if ($idCompra) {

        $productos = $_POST["productos"];
        $cantidades = $_POST["cantidades"];
        $unidades = $_POST["unidades"];
        $preciosCompra = $_POST["preciosCompra"];
        $preciosVenta = $_POST["preciosVenta"];

        for ($i = 0; $i < count($productos); $i++) {

          $idProducto = (int)$productos[$i];
          $cantidad = (float)$cantidades[$i];
          $unidad = $unidades[$i];
          $precioCompra = $preciosCompra[$i];
          $precioVenta = $preciosVenta[$i];

          if($idProducto <= 0 || $cantidad <= 0){
            continue;
          }

          $detalle = array(
            "id_compra" => $idCompra,
            "id_producto" => $idProducto,
            "cantidad" => $cantidad,
            "unidad" => $unidad,
            "precio_compra" => $precioCompra,
            "precio_venta" => $precioVenta
          );

          $respDetalle = ModeloCompras::mdlInsertarDetalleCompra("detalle_compra", $detalle);

          echo "<pre>";
echo "DETALLE:\n";
print_r($detalle);

echo "\nRESP DETALLE:\n";
var_dump($respDetalle);
echo "</pre>";
exit();

          if(!$respDetalle){
            echo '<script>
              swal({
                type: "error",
                title: "¡Error al guardar el detalle de la compra!",
                text: "No se pudo registrar uno de los productos.",
                confirmButtonText: "Cerrar"
              }).then(function(result){
                if(result.value){
                  window.location = "proveedores";
                }
              });
            </script>';
            return;
          }

          $respStock = ModeloCompras::mdlActualizarStockYPrecio(
            "productos",
            $idProducto,
            $idSucursal,
            $cantidad,
            $precioCompra,
            $precioVenta
          );

          if(!$respStock){
            echo '<script>
              swal({
                type: "error",
                title: "¡Error al actualizar stock!",
                text: "No se pudo actualizar el inventario en la sucursal.",
                confirmButtonText: "Cerrar"
              }).then(function(result){
                if(result.value){
                  window.location = "proveedores";
                }
              });
            </script>';
            return;
          }
        }

        echo '<script>
          swal({
            type: "success",
            title: "¡Compra guardada correctamente!",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
          }).then(function(result){
            if(result.value){
              window.location = "proveedores";
            }
          });
        </script>';

      } else {

        echo '<script>
          swal({
            type: "error",
            title: "¡Error al guardar la compra!",
            text: "No se pudo registrar la compra. Intenta nuevamente.",
            confirmButtonText: "Cerrar"
          }).then(function(result){
            if(result.value){
              window.location = "proveedores";
            }
          });
        </script>';
      }

    } else {

      echo '<script>
        swal({
          type: "warning",
          title: "Datos incompletos",
          text: "Por favor agrega al menos un producto antes de guardar.",
          confirmButtonText: "Cerrar"
        }).then(function(result){
          if(result.value){
            window.location = "proveedores";
          }
        });
      </script>';
    }
  }
}
?>
