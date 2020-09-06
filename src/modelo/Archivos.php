<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;

class Archivos extends Model{
	protected $table = 'archivos';
	public $timestamps = false;

	public static function NuevoRegistro($data){
		Archivos::insert($data);
	}

	public static function ObtenerRegistroByArray($data){
		$result = array();
		$query = Archivos::query()
		->selectRaw("*")
		->where($data)
		->orderByRaw("idarchivo DESC")
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