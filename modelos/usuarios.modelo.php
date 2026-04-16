<?php

require_once "conexion.php";

class ModeloUsuarios{

	/*=============================================
	MOSTRAR USUARIOS
	=============================================*/
	static public function mdlMostrarUsuarios($tabla, $item, $valor){

		if($item != null){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");
			$stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
			$stmt->execute();

			return $stmt->fetch(PDO::FETCH_ASSOC);

		}else{

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");
			$stmt->execute();

			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
	}

	/*=============================================
	REGISTRO DE USUARIO
	=============================================*/
	static public function mdlIngresarUsuario($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("
			INSERT INTO $tabla(nombre, usuario, password, perfil, id_sucursal, foto) 
			VALUES (:nombre, :usuario, :password, :perfil, :id_sucursal, :foto)
		");

		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_STR);
		$stmt->bindParam(":password", $datos["password"], PDO::PARAM_STR);
		$stmt->bindParam(":perfil", $datos["perfil"], PDO::PARAM_STR);
		$stmt->bindParam(":id_sucursal", $datos["id_sucursal"], PDO::PARAM_INT);
		$stmt->bindParam(":foto", $datos["foto"], PDO::PARAM_STR);

		return $stmt->execute() ? "ok" : "error";
	}

	/*=============================================
	EDITAR USUARIO
	=============================================*/
	static public function mdlEditarUsuario($tabla, $datos){
	
		$stmt = Conexion::conectar()->prepare("
			UPDATE $tabla 
			SET nombre = :nombre, 
				password = :password, 
				perfil = :perfil, 
				id_sucursal = :id_sucursal,
				foto = :foto 
			WHERE usuario = :usuario
		");

		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":password", $datos["password"], PDO::PARAM_STR);
		$stmt->bindParam(":perfil", $datos["perfil"], PDO::PARAM_STR);
		$stmt->bindParam(":id_sucursal", $datos["id_sucursal"], PDO::PARAM_INT);
		$stmt->bindParam(":foto", $datos["foto"], PDO::PARAM_STR);
		$stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_STR);

		return $stmt->execute() ? "ok" : "error";
	}

	/*=============================================
	ACTUALIZAR USUARIO
	=============================================*/
	static public function mdlActualizarUsuario($tabla, $item1, $valor1, $item2, $valor2){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE $item2 = :$item2");

		$stmt->bindParam(":".$item1, $valor1, PDO::PARAM_STR);
		$stmt->bindParam(":".$item2, $valor2, PDO::PARAM_STR);

		return $stmt->execute() ? "ok" : "error";
	}

	/*=============================================
	ACTUALIZAR PASSWORD
	=============================================*/
	static public function mdlActualizarPassword($tabla, $idUsuario, $nuevoHash){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET password = :password WHERE id = :id");

		$stmt->bindParam(":password", $nuevoHash, PDO::PARAM_STR);
		$stmt->bindParam(":id", $idUsuario, PDO::PARAM_INT);

		return $stmt->execute() ? "ok" : "error";
	}

	/*=============================================
	BORRAR USUARIO
	=============================================*/
	static public function mdlBorrarUsuario($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
		$stmt->bindParam(":id", $datos, PDO::PARAM_INT);

		return $stmt->execute() ? "ok" : "error";
	}
}