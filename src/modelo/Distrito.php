<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;

class Distrito extends Model{
	protected $table = 'distrito';
	public $timestamps = false;

	public static function ListarDistrito(){
		$result = array();
		$query = Distrito::query()
				->selectRaw("*")
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

	public static function ListarDistritosbyReporteIndicador($idIndicador){
		$result = array();
		$query = Distrito::query()
		->selectRaw("registro_indicador.idIndicador,
		registro_indicador.idDistrito,
		distrito.nombre")
		->join("registro_indicador","registro_indicador.idDistrito","=","distrito.idDistrito")
		->where("registro_indicador.idIndicador",$idIndicador)
		->distinct()
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

	public static function ListarDistritoByIdProvincia($idProvincia){
		$result = array();
		$query = Distrito::query()
		->selectRaw("*")
		->where("idProvincia",$idProvincia)
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

	public static function DatosDistritoById($idDistrito){
		$result = array();
		$query = Distrito::query()
		->selectRaw("*")
		->where("idDistrito",$idDistrito)
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

	public static function DatosDistritoByNombre($nombre_Distrito){
		$result = array();
		$query = Distrito::query()
		->selectRaw("*")
		->where("nombre_Distrito",$nombre_Distrito)
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
		Distrito::insert($data);
	}

	public static function ActualizarRegistro($idDistrito,$data){
		Distrito::query()
		->where(['idDistrito'=>intval($idDistrito)])
		->update($data);
	}
}