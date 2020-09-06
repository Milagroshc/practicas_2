<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;

class UsuarioRol extends Model{
	protected $table = 'usuario_rol';
	public $timestamps = false;

	public static function getRolesPorUsuario($idusuario,$tipo,$pag=1){
		$reg_por_pag = Constante::Item_Pag_5;
		$result = array();
		$campos = "usuario_rol.idusuario,usuario_rol.idrol,usuario_rol.estado,rol.nombre_rol,usuario.username";
		switch ($tipo) {
			case "1": // ROLES POR USUARIO PARA LOGIN
				$query = UsuarioRol::query()
				->selectRaw($campos)
				->join("rol","rol.idrol","=","usuario_rol.idrol")
				->join("usuario","usuario.idusuario","=","usuario_rol.idusuario")
				->where("usuario_rol.idusuario",$idusuario)
				->where("usuario_rol.estado",1)
				->where("rol.estado",1)
				->get()
				->toArray();

				if (!empty($query)) {
					$result["data"] = $query;
					$success = true;
					$message = "Los datos se listaron correctamente";
				} else {
					$success = false;
					$message = "No existen resultados";
				}
			break;
			
			case "2": // ROLES POR USUARIO CON PAGINACION PARA ASIGNAR ROL A UN USUARIO
				$query = UsuarioRol::query()
				->selectRaw($campos)
				->join("rol","rol.idrol","=","usuario_rol.idrol")
				->join("usuario","usuario.idusuario","=","usuario_rol.idusuario")
				->where("usuario_rol.idusuario",$idusuario)
				->offset(($pag-1)*$reg_por_pag)
				->limit($reg_por_pag)
				->get()
				->toArray();

				$query_count = UsuarioRol::query()
				->selectRaw("COUNT(DISTINCT usuario_rol.idrol) as total_registros")
				->join("rol","rol.idrol","=","usuario_rol.idrol")
				->join("usuario","usuario.idusuario","=","usuario_rol.idusuario")
				->where("usuario_rol.idusuario",$idusuario)
				->get()
				->toArray();

				if (!empty($query)) {
					$result["data"] = $query;
					$result["reg_por_pag"] = $reg_por_pag;
					$result["total_registros"] = $query_count[0]["total_registros"];
					$success = true;
					$message = "Los datos se listaron correctamente";
				} else {
					$result["total_registros"] = 0;
					$success = false;
					$message = "No existen resultados";
				}
			break;
		}

		$result["success"] = $success;
		$result["message"] = $message;
		return $result;
	}

	public static function NuevoRegistro($data){
		UsuarioRol::insert($data);
	}

	public static function ActualizarRegistro($idusuario,$idrol,$data){
		UsuarioRol::query()
		->where(['idusuario'=>intval($idusuario)])
		->where(['idrol'=>intval($idrol)])
		->update($data);
	}

	public static function VerificarUsuarioRol($idusuario,$idrol){
		$result = array();
		$query = UsuarioRol::query()
		->selectRaw("*")
		->where("idusuario",$idusuario)
		->where("idrol",$idrol)
		->get()
		->toArray();

		if (!empty($query)) {
			$result["data"] = $query[0];
			$success = true;
			$message = "Los datos se listaron correctamente";
		} else {
			$success = false;
			$message = "No existen resultados";
		}
		$result["success"] = $success;
		$result["message"] = $message;
		return $result;
	}
}