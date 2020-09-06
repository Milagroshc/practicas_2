<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;

class AccesoAcciones extends Model{
	protected $table = 'acceso_acciones';
	public $timestamps = false;

	public static function DatosByIdAcceso($idacceso){
		$result = array();
		$query = AccesoAcciones::query()
		->selectRaw("*")
		->where("idacceso",$idacceso)
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

	public static function VerificarAccesoAccion($idacceso,$idaccion){
		$result = array();
		$query = AccesoAcciones::query()
		->selectRaw("*")
		->where("idacceso",$idacceso)
		->where("idaccion",$idaccion)
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

	public static function NuevoRegistro($data){
		AccesoAcciones::insert($data);
	}

	public static function ActualizarRegistro($idacceso,$idaccion,$data){
		AccesoAcciones::query()
		->where(['idacceso'=>intval($idacceso)])
		->where(['idaccion'=>intval($idaccion)])
		->update($data);
	}
}