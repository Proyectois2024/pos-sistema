<header class="main-header"> 
	
	<a href="inicio" class="logo">
		
		<span class="logo-mini">
			<img src="vistas/img/plantilla/logosolo.png" class="img-responsive" style="padding:10px">
		</span>

		<span class="logo-lg" style="text-align: left; display: flex; align-items: center; height: 50px;">
			<img src="vistas/img/plantilla/logomeme.png" style="width: 35px; margin-right: 8px;">
		</span>

	</a>

	<nav class="navbar navbar-static-top" role="navigation">
		
	 	<a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        	<span class="sr-only">Toggle navigation</span>
      	</a>

		<div class="navbar-custom-menu">
				
			<ul class="nav navbar-nav">

				<?php
					$cantStockBajo = ControladorProductos::ctrMostrarProductosStockBajo();

					if(!is_array($cantStockBajo) || !isset($cantStockBajo["total"])){
						$cantStockBajo = array("total" => 0);
					}

					$textoSucursal = "Sucursal no definida";

					if(isset($_SESSION["id_sucursal"]) && (int)$_SESSION["id_sucursal"] === 0){
						$textoSucursal = "Todas las sucursales";
					}elseif(isset($_SESSION["nombre_sucursal"]) && !empty($_SESSION["nombre_sucursal"])){
						$textoSucursal = $_SESSION["nombre_sucursal"];
					}elseif(isset($_SESSION["id_sucursal"]) && !empty($_SESSION["id_sucursal"])){
						$textoSucursal = "Sucursal ID: ".$_SESSION["id_sucursal"];
					}
				?>

				<?php if($_SESSION["perfil"] == "Administrador"): ?>

				<li style="padding:8px 10px;">
					<select class="form-control selectSucursal" style="width:220px;">

						<option value="0" <?php echo (isset($_SESSION["id_sucursal"]) && (int)$_SESSION["id_sucursal"] === 0) ? "selected" : ""; ?>>
							Todas las sucursales
						</option>

						<?php
						$stmtSuc = Conexion::conectar()->prepare("SELECT id, nombre, codigo FROM sucursales WHERE estado = 1 ORDER BY id ASC");
						$stmtSuc->execute();
						$sucursales = $stmtSuc->fetchAll(PDO::FETCH_ASSOC);

						if(is_array($sucursales)){
							foreach($sucursales as $suc){

								$selected = (isset($_SESSION["id_sucursal"]) && (int)$_SESSION["id_sucursal"] === (int)$suc["id"]) ? "selected" : "";

								echo '<option value="'.$suc["id"].'" '.$selected.'>'.
										$suc["nombre"].' - '.$suc["codigo"].' (ID '.$suc["id"].')
									  </option>';
							}
						}
						?>
					</select>
				</li>

				<?php endif; ?>

				<li class="dropdown notifications-menu">
					
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						<i class="fa fa-bell-o"></i>
						
						<?php if((int)$cantStockBajo["total"] > 0): ?>
							<span class="label label-danger"><?php echo (int)$cantStockBajo["total"]; ?></span>
						<?php endif; ?>
					</a>

					<ul class="dropdown-menu">
						<li class="header">
							Tienes <?php echo (int)$cantStockBajo["total"]; ?> productos con stock bajo
						</li>
						<li>
							<ul class="menu">
								<li>
									<a href="productos">
										<i class="fa fa-warning text-red"></i> Ver inventario crítico
									</a>
								</li>
							</ul>
						</li>
					</ul>

				</li>

				<li class="dropdown user user-menu">
					
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">

						<?php
						if($_SESSION["foto"] != ""){
							echo '<img src="'.$_SESSION["foto"].'" class="user-image">';
						}else{
							echo '<img src="vistas/img/usuarios/default/anonymous.png" class="user-image">';
						}
						?>
						
						<span class="hidden-xs">
							<?php echo $_SESSION["nombre"]; ?>
							<small style="display:block; font-size:11px; color:#ddd;"><?php echo $textoSucursal; ?></small>
						</span>

					</a>

					<ul class="dropdown-menu">
						
						<li class="user-header">

							<?php
							if($_SESSION["foto"] != ""){
								echo '<img src="'.$_SESSION["foto"].'" class="img-circle" alt="User Image">';
							}else{
								echo '<img src="vistas/img/usuarios/default/anonymous.png" class="img-circle" alt="User Image">';
							}
							?>

							<p>
								<?php echo $_SESSION["nombre"]; ?>
								<small><?php echo $textoSucursal; ?></small>
							</p>

						</li>
						
						<li class="user-body">
							
							<div class="text-center" style="margin-bottom:10px;">
								<strong><?php echo $textoSucursal; ?></strong>
							</div>

							<div class="pull-right">
								<a href="salir" class="btn btn-default btn-flat">Salir</a>
							</div>

						</li>

					</ul>

				</li>

			</ul>

		</div>

	</nav>

</header>