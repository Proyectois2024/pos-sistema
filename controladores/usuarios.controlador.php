<?php

class ControladorUsuarios{

	/*=============================================
	FUNCIÓN AUXILIAR: SUBIR IMAGEN A CLOUDINARY
	=============================================*/
	static private function subirACloudinary($archivoTemporal, $nombreCampo) {
		$uploadPreset = "pos_preset"; 
		$cloudName = "pkpk2vjr";

		$cFile = curl_file_create($archivoTemporal, $_FILES[$nombreCampo]["type"], $_FILES[$nombreCampo]["name"]);

		$postData = array(
			'file' => $cFile,
			'upload_preset' => $uploadPreset,
			'folder' => 'pos_usuarios' // Organiza las fotos de usuarios en esta carpeta de Cloudinary
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
	INGRESO DE USUARIO
	=============================================*/
	static public function ctrIngresoUsuario(){

		if(isset($_POST["ingUsuario"])){

			if(
				preg_match('/^[a-zA-Z0-9._-]+$/', $_POST["ingUsuario"]) &&
				!empty($_POST["ingPassword"])
			){

				$tabla = "usuarios";
				$item = "usuario";
				$valor = $_POST["ingUsuario"];

				$respuesta = ModeloUsuarios::MdlMostrarUsuarios($tabla, $item, $valor);

				if($respuesta != false){

					if($respuesta["usuario"] == $_POST["ingUsuario"] && app_verify_password($_POST["ingPassword"], $respuesta["password"])){

						if(app_password_needs_rehash($respuesta["password"])){

							$nuevoHash = app_hash_password($_POST["ingPassword"]);
							$actualizarHash = ModeloUsuarios::mdlActualizarPassword($tabla, $respuesta["id"], $nuevoHash);

							if($actualizarHash == "ok"){
								$respuesta["password"] = $nuevoHash;
							}
						}

						if($respuesta["estado"] == 1){

							$_SESSION["iniciarSesion"] = "ok";
							$_SESSION["id"] = $respuesta["id"];
							$_SESSION["nombre"] = isset($respuesta["nombre"]) ? $respuesta["nombre"] : "";
							$_SESSION["usuario"] = $respuesta["usuario"];
							$_SESSION["foto"] = $respuesta["foto"];
							$_SESSION["perfil"] = $respuesta["perfil"];
							$_SESSION["id_sucursal"] = $respuesta["id_sucursal"];

							require_once "controladores/sucursales.controlador.php";
							require_once "modelos/sucursales.modelo.php";

							$sucursal = ControladorSucursales::ctrMostrarSucursales("id", $respuesta["id_sucursal"]);
							$_SESSION["nombre_sucursal"] = (is_array($sucursal) && isset($sucursal["nombre"]))
								? $sucursal["nombre"]
								: "Sin sucursal";

							$fechaActual = app_now();

							$item1 = "ultimo_login";
							$valor1 = $fechaActual;

							$item2 = "id";
							$valor2 = $respuesta["id"];

							$ultimoLogin = ModeloUsuarios::mdlActualizarUsuario($tabla, $item1, $valor1, $item2, $valor2);

							if($ultimoLogin == "ok"){

								echo '<script>
									window.location = "inicio";
								</script>';

							}

						}else{

							echo '<br><div class="alert alert-danger">El usuario aún no está activado</div>';

						}

					}else{

						echo '<br><div class="alert alert-danger">Usuario o contraseña incorrectos</div>';

					}

				}else{

					echo '<br><div class="alert alert-danger">El usuario no existe</div>';

				}
			}
		}
	}

	/*=============================================
	CREAR USUARIO
	=============================================*/
	static public function ctrCrearUsuario(){

		if(isset($_POST["nuevoUsuario"])){

			if(
				preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoNombre"]) &&
				preg_match('/^[a-zA-Z0-9._-]+$/', $_POST["nuevoUsuario"]) &&
				preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoPassword"])
			){

				$ruta = "";

				if(isset($_FILES["nuevaFoto"]["tmp_name"]) && !empty($_FILES["nuevaFoto"]["tmp_name"])){

					$urlCloudinary = self::subirACloudinary($_FILES["nuevaFoto"]["tmp_name"], "nuevaFoto");

					if($urlCloudinary != null){
						$ruta = $urlCloudinary;
					}

				}

				$tabla = "usuarios";

				$usuarioExistente = ModeloUsuarios::mdlMostrarUsuarios($tabla, "usuario", $_POST["nuevoUsuario"]);

				if($usuarioExistente){

					echo '<script>
						swal({
							type: "error",
							title: "¡El usuario ya existe!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then(function(result){
							if(result.value){
								window.location = "usuarios";
							}
						});
					</script>';

					return;
				}

				$idSucursal = null;

				if($_POST["nuevoPerfil"] != "Administrador"){

					if(empty($_POST["nuevoIdSucursal"])){

						echo '<script>
							swal({
								type: "error",
								title: "¡Debe seleccionar una sucursal!",
								showConfirmButton: true,
								confirmButtonText: "Cerrar"
							}).then(function(result){
								if(result.value){
									window.location = "usuarios";
								}
							});
						</script>';

						return;
					}

					$idSucursal = (int) $_POST["nuevoIdSucursal"];
				}

				$encriptar = app_hash_password($_POST["nuevoPassword"]);

				$datos = array(
					"nombre" => $_POST["nuevoNombre"],
					"usuario" => $_POST["nuevoUsuario"],
					"password" => $encriptar,
					"perfil" => $_POST["nuevoPerfil"],
					"id_sucursal" => $idSucursal,
					"foto" => $ruta
				);

				$respuesta = ModeloUsuarios::mdlIngresarUsuario($tabla, $datos);

				if($respuesta == "ok"){

					echo '<script>
						swal({
							type: "success",
							title: "¡El usuario ha sido guardado correctamente!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});

						setTimeout(function(){
							window.location = "usuarios";
						}, 1000);
					</script>';

				}else{

					echo '<script>
						swal({
							type: "error",
							title: "No se pudo guardar el usuario",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});

						setTimeout(function(){
							window.location = "usuarios";
						}, 1000);
					</script>';

				}

			}else{

				echo '<script>
					swal({
						type: "error",
						title: "¡El usuario no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if(result.value){
							window.location = "usuarios";
						}
					});
				</script>';
			}
		}
	}

	/*=============================================
	MOSTRAR USUARIO
	=============================================*/
	static public function ctrMostrarUsuarios($item, $valor){

		$tabla = "usuarios";

		$respuesta = ModeloUsuarios::MdlMostrarUsuarios($tabla, $item, $valor);

		return $respuesta;
	}

	/*=============================================
	EDITAR USUARIO
	=============================================*/
	static public function ctrEditarUsuario(){

		if(isset($_POST["editarUsuario"])){

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarNombre"])){

				/*=============================================
				VALIDAR IMAGEN
				=============================================*/

				$ruta = $_POST["fotoActual"];

				if(isset($_FILES["editarFoto"]["tmp_name"]) && !empty($_FILES["editarFoto"]["tmp_name"])){

					$urlCloudinary = self::subirACloudinary($_FILES["editarFoto"]["tmp_name"], "editarFoto");

					if($urlCloudinary != null){
						$ruta = $urlCloudinary;
					}

				}

				$tabla = "usuarios";

				if($_POST["editarPassword"] != ""){

					if(preg_match('/^[a-zA-Z0-9]+$/', $_POST["editarPassword"])){

						$encriptar = app_hash_password($_POST["editarPassword"]);

					}else{

						echo'<script>

								swal({
									  type: "error",
									  title: "¡La contraseña no puede ir vacía o llevar caracteres especiales!",
									  showConfirmButton: true,
									  confirmButtonText: "Cerrar"
									  }).then(function(result) {
										if (result.value) {

										window.location = "usuarios";

										}
									})

							</script>';

						return;

					}

				}else{

					$encriptar = $_POST["passwordActual"];

				}

				$idSucursal = null;

				if($_POST["editarPerfil"] != "Administrador"){

					if(empty($_POST["editarIdSucursal"])){

						echo '<script>
							swal({
								type: "error",
								title: "¡Debe seleccionar una sucursal!",
								showConfirmButton: true,
								confirmButtonText: "Cerrar"
							}).then(function(result){
								if(result.value){
									window.location = "usuarios";
								}
							});
						</script>';

						return;
					}

					$idSucursal = (int) $_POST["editarIdSucursal"];
				}

				$datos = array(
					"nombre" => $_POST["editarNombre"],
					"usuario" => $_POST["editarUsuario"],
					"password" => $encriptar,
					"perfil" => $_POST["editarPerfil"],
					"id_sucursal" => $idSucursal,
					"foto" => $ruta
				);

				$respuesta = ModeloUsuarios::mdlEditarUsuario($tabla, $datos);

				if($respuesta == "ok"){

					echo'<script>

					swal({
						  type: "success",
						  title: "El usuario ha sido editado correctamente",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result) {
									if (result.value) {

									window.location = "usuarios";

									}
								})

					</script>';

				}

			}else{

				echo'<script>

					swal({
						  type: "error",
						  title: "¡El nombre no puede ir vacío o llevar caracteres especiales!",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result) {
							if (result.value) {

							window.location = "usuarios";

							}
						})

				</script>';

			}

		}

	}

	/*=============================================
	BORRAR USUARIO
	=============================================*/
	static public function ctrBorrarUsuario(){

		if(isset($_GET["idUsuario"])){

			$tabla ="usuarios";
			$datos = $_GET["idUsuario"];

			$respuesta = ModeloUsuarios::mdlBorrarUsuario($tabla, $datos);

			if($respuesta == "ok"){

				echo'<script>

				swal({
					  type: "success",
					  title: "El usuario ha sido borrado correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar",
					  closeOnConfirm: false
					  }).then(function(result) {
								if (result.value) {

								window.location = "usuarios";

								}
							})

				</script>';

			}		

		}

	}

}
