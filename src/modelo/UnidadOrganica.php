<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;

class UnidadOrganica extends Model{
	protected $table = 'unidad_organica';
	public $timestamps = false;

	public static function getEstructuraOrganica(){
		$result = array();
		$query = UnidadOrganica::query()
		->selectRaw("unidad_organica.id_unidad_organica,unidad_organica.nombre_unidad_organica,
			concat(
				IFNULL(concat(c.nombre_unidad_organica,'->'),''),
				IFNULL(concat(b.nombre_unidad_organica),'') 
			)as ruta")
		->leftJoin("unidad_organica as b","unidad_organica.id_padre","=","b.id_unidad_organica")
		->leftJoin("unidad_organica as c","b.id_padre","=","c.id_unidad_organica")
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