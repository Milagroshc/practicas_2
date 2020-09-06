<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;

class Parametros extends Model{
	protected $table = 'parametros';
	public $timestamps = false;

	public static function getParametros($idcatalogo){
		$result = array();
		$query = parametros::query()
		->selectRaw("idparametro,idcatalogo,nombre_parametro,estado")
		->where("idcatalogo",$idcatalogo)
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


	public static function getParametrosById($idparametro){
		$result = array();
		$query = parametros::query()
		->selectRaw("idparametro,idcatalogo,nombre_parametro,estado")
		->where("idparametro",$idparametro)
		->where("estado",1)
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