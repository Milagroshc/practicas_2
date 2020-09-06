<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;

class Accesos extends Model{
	protected $table = 'accesos';
	public $timestamps = false;

	public static function getAccesos($idusuario,$idrol,$idmodulo){
		$result = array();
		$query = Accesos::query()
		->selectRaw("accesos.id_item_modulo, accesos.idrol, item_modulos.nombre_item, item_modulos.url, item_modulos.id_modulo")
		->join("rol", "rol.idrol", "=", "accesos.idrol")
		->join("item_modulos", "item_modulos.id_item_modulo", "=", "accesos.id_item_modulo")
		->join("usuario_rol", "usuario_rol.idrol", "=", "rol.idrol")
		->join("usuario", "usuario.idusuario", "=", "usuario_rol.idusuario")
		->where("usuario_rol.idusuario",$idusuario)
		->where("usuario_rol.idrol",$idrol)
		->where("item_modulos.id_modulo",$idmodulo)
		->where("accesos.estado",1)
		->orderBy("item_modulos.orden")
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
		$result["success"] = $success;
		$result["message"] = $message;
		return $result;
	}

	public static function DatosAccesoByIds($idrol,$id_item_modulo){
		$result = array();
		$query = Accesos::query()
		->selectRaw("*")
		->where("idrol",$idrol)
		->where("id_item_modulo",$id_item_modulo)
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

	public static function NuevoRegistro($data){
		Accesos::insert($data);
	}

	public static function ActualizarRegistro($idacceso,$data){
		Accesos::query()
		->where(['idacceso'=>intval($idacceso)])
		->update($data);
	}
}