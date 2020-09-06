<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;

class Empleado extends Model{
	protected $table = 'empleados';
	public $timestamps = false;

	public static function DatosEmpleadoByIdPersona($idpersona){
		$result = array();
		$query = Empleado::query()
		->selectRaw("*")
		->where("idpersona",$idpersona)
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

	public static function DatosEmpleadoById($idempleado){
		$result = array();
		$query = Empleado::query()
		->selectRaw("*")
		->where("idempleado",$idempleado)
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
		Empleado::insert($data);
	}

	public static function ActualizarRegistro($idempleado,$data){
		Empleado::query()
		->where(['idempleado'=>intval($idempleado)])
		->update($data);
	}
}