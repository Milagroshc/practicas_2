<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;

class Acciones extends Model{
	protected $table = 'acciones';
	public $timestamps = false;

	public static function DatosAccionesByIdItemModulo($id_item_modulo){
		$result = array();
		$query = Acciones::query()
		->selectRaw("*")
		->where("id_item_modulo",$id_item_modulo)
		->where("estado",1)
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

	public static function ListarAccionesByIdAcceso($idacceso,$id_item_modulo){
		$result = array();
		$query = Acciones::query()
		->selectRaw("acciones.*,item_modulos.nombre_item,(IF(acciones.idaccion in (select acceso_acciones.idaccion from acceso_acciones where acceso_acciones.idacceso=".$idacceso." AND acceso_acciones.estado=1),'SI','NO')) as tiene")
		->join("item_modulos","item_modulos.id_item_modulo","=","acciones.id_item_modulo")
		->where("item_modulos.id_item_modulo",$id_item_modulo)
		->orderBy("acciones.idaccion")
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
}