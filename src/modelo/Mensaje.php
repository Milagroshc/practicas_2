<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;

class Mensaje extends Model{
	protected $table = 'mensaje';
	public $timestamps = false;

	public static function BuscarNumDoc($tipodoc,$numdoc){
		$result = array();
		$query = Mensaje::query()
		->selectRaw("*")
		->where("tipo_doc",$tipodoc)
		->where("num_doc",$numdoc)
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


	public static function verificaCorreo($correo){
		$result = array();
		$query = Mensaje::query()
		->selectRaw("*")
		->where("correo_mensaje",$correo)
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
		Mensaje::insert($data);
	}

	public static function ActualizarRegistro($idMensaje,$data){
		Mensaje::query()
		->where(['idMensaje'=>intval($idMensaje)])
		->update($data);
	}
}