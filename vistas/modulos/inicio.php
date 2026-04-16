<div class="content-wrapper">

  <section class="content-header">
    <h1>
      Tablero
      <small>Panel de Control</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Tablero</li>
    </ol>
  </section>

  <section class="content">

    <?php
      $nombreSucursal = isset($_SESSION["nombre_sucursal"]) ? $_SESSION["nombre_sucursal"] : "Sin sucursal";
      $idSucursal = isset($_SESSION["id_sucursal"]) ? (int)$_SESSION["id_sucursal"] : 0;
      $modoGlobal = ($idSucursal === 0);
    ?>

    <?php if($_SESSION["perfil"] == "Administrador"): ?>
    <div class="row">
      <div class="col-lg-12">
        <div class="alert alert-info" style="margin-bottom:15px;">
          <strong>Vista actual:</strong>
          <?php
            if($modoGlobal){
              echo 'Reporte global de todas las sucursales';
            }else{
              echo 'Sucursal activa: '.$nombreSucursal;
            }
          ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <div class="row">

      <?php
      if($_SESSION["perfil"] == "Administrador"){
        include "inicio/cajas-superiores.php";
      }
      ?>

      <?php if($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Vendedor"): ?>

      <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:#8e44ad; color:#fff;">
          <div class="inner">
            <h3>COT</h3>
            <p>Cotizaciones y pedidos</p>
          </div>
          <div class="icon">
            <i class="fa fa-file-text-o"></i>
          </div>
          <a href="cotizaciones" class="small-box-footer" style="color:#fff;">
            Más info <i class="fa fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>

      <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:#e91e63; color:#fff;">
          <div class="inner">
            <h3>+</h3>
            <p>Nueva cotización</p>
          </div>
          <div class="icon">
            <i class="fa fa-plus-square"></i>
          </div>
          <a href="crear-cotizacion" class="small-box-footer" style="color:#fff;">
            Crear <i class="fa fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>

      <?php endif; ?>

      <?php if($_SESSION["perfil"] == "Administrador"): ?>
      <div class="col-lg-3 col-xs-6">
        <div class="small-box" style="background:#16a085; color:#fff;">
          <div class="inner">
            <h3><i class="fa fa-exchange"></i></h3>
            <p>Transferencias</p>
          </div>
          <div class="icon">
            <i class="fa fa-random"></i>
          </div>
          <a href="transferencias" class="small-box-footer" style="color:#fff;">
            Más info <i class="fa fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>
      <?php endif; ?>

    </div>

    <div class="row">

      <div class="col-lg-12">
        <?php
        if($_SESSION["perfil"] == "Administrador"){
          include "reportes/grafico-ventas.php";
        }
        ?>
      </div>

      <div class="col-lg-6">
        <?php
        if($_SESSION["perfil"] == "Administrador"){
          include "reportes/productos-mas-vendidos.php";
        }
        ?>
      </div>

      <div class="col-lg-6">
        <?php
        if($_SESSION["perfil"] == "Administrador"){
          include "inicio/productos-recientes.php";
        }
        ?>
      </div>

      <div class="col-lg-6">
        <?php
        if($_SESSION["perfil"] == "Administrador"){
          include "reportes/vendedores.php";
        }
        ?>
      </div>

      <div class="col-lg-6">
        <?php
        if($_SESSION["perfil"] == "Administrador"){
          include "reportes/compradores.php";
        }
        ?>
      </div>

      <div class="col-lg-12">
        <?php
        if($_SESSION["perfil"] == "Especial" || $_SESSION["perfil"] == "Vendedor"){
          echo '<div class="box box-success">
            <div class="box-header">
              <h1>Bienvenid@ '.$_SESSION["nombre"].'</h1>
              <p style="margin-top:10px;"><strong>Sucursal activa:</strong> '.$nombreSucursal.'</p>
            </div>
          </div>';
        }
        ?>
      </div>

    </div>

  </section>

</div>