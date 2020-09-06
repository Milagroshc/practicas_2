<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use App\Helper\Constante;
use App\Modelo\Distrito;

class RegistroIndicador extends Model{
	protected $table = 'registro_indicador';
	public $timestamps = false;

	public static function EstadisticaReporte($datos){
		$result = array();
		$sumQuery="";
		for ($i=0; $i < count($datos["anio"]); $i++) { 
			# code...
			$sumQuery=$sumQuery." 
			SUM(CASE WHEN registro_indicador.anio = ".$datos["anio"][$i]." THEN registro_indicador.valor ELSE 0 END) AS \"A-".$datos["anio"][$i]."\",";
		}
	
		$dataWhere=Array(
			"indicador.idIndicador" =>$datos["indicador"]
		);
		$dataWhereIn=$datos["distrito"];

		$users = DB::select("SET lc_time_names = 'es_ES';"); //convierte a español todo mysql
		$query = RegistroIndicador::query()
		->selectRaw("
		distrito.nombre as distrito,
		".$sumQuery."
		SUM(registro_indicador.valor) as Total,
		count(registro_indicador.valor) as cantidad,
		tematica.idTematica,
		indicador.idIndicador,
		indicador.idTematica,
		registro_indicador.idDistrito,
		registro_indicador.fecha_registro,
		registro_indicador.anio as Anio,
		indicador.unidad")
		->join("indicador","registro_indicador.idIndicador","=","indicador.idIndicador")
		->join("distrito","registro_indicador.idDistrito","=","distrito.idDistrito")
		->join("tematica","indicador.idTematica","=","tematica.idTematica")
		//->where("idacceso",$idacceso)
		->where($dataWhere)
		->whereIn('registro_indicador.idDistrito',$dataWhereIn)
		->whereIn('Anio',$datos["anio"])
		->groupBy('distrito.nombre' )
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


	public static function EstadisticaReporteTables($datos){
		$result = array();
		$sumQuery="";
		for ($i=0; $i < count($datos["anio"]); $i++) { 
			# code...
			$sumQuery=$sumQuery." 
			SUM(CASE WHEN registro_indicador.anio = ".$datos["anio"][$i]." THEN registro_indicador.valor ELSE 0 END) AS \"".$datos["anio"][$i]."\",";
		}
	
		$dataWhere=Array(
			"indicador.idIndicador" =>$datos["indicador"]
		);
		$dataWhereIn=$datos["distrito"];

		$users = DB::select("SET lc_time_names = 'es_ES';"); //convierte a español todo mysql
		$query = RegistroIndicador::query()
		->selectRaw("
		distrito.nombre as distrito,
		".$sumQuery."
		SUM(registro_indicador.valor) as Total")
		->join("indicador","registro_indicador.idIndicador","=","indicador.idIndicador")
		->join("distrito","registro_indicador.idDistrito","=","distrito.idDistrito")
		->join("tematica","indicador.idTematica","=","tematica.idTematica")
		//->where("idacceso",$idacceso)
		->where($dataWhere)
		->whereIn('registro_indicador.idDistrito',$dataWhereIn)
		->whereIn('Anio',$datos["anio"])
		->groupBy('distrito.nombre' )
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


	public static function ListarAniobyReporteIndicador($idIndicador){
		$result = array();
		$query = RegistroIndicador::query()
		->selectRaw("registro_indicador.idIndicador,
		registro_indicador.anio")
		->where("registro_indicador.idIndicador",$idIndicador)
		->distinct()
		->orderBy('anio', 'DESC')
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
		$query = RegistroIndicador::query()
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
		RegistroIndicador::insert($data);
	}

	public static function ActualizarRegistro($idacceso,$idaccion,$data){
		RegistroIndicador::query()
		->where(['idacceso'=>intval($idacceso)])
		->where(['idaccion'=>intval($idaccion)])
		->update($data);
	}

	public static function isExist($data){
		
		try {
			

$mensaje="";
		$estado=true;
		$keys=array_keys($data);
		for ($i=0; $i < count($keys); $i++) {
			if (!isset($data[$keys[$i]]) || empty($data[$keys[$i]])) { //is not null
				$estado = false;
				$mensaje = "El campo esta vacio";
				break;
			}else{
				$estado2 = RegistroIndicador::query()
				->where($data)
				->exists();
				if ($estado2) {
					$mensaje = "Ya existe otro registro";
					$estado=false;
					break;
				}else{
					switch ($i) {
						case 2:
							$estado3 = Distrito::query()
							->where("idDistrito", $data["idDistrito"])
							->exists();
							//var_dump($data["idDistrito"]);
							if (!$estado3) {
								$mensaje = "No existe este distrito";
								$estado=false;
								break;
							}
							break;
						case 3:
							if(!is_numeric($data["valor"])) {
								$estado=false;
								$mensaje="El valor no es númerico";
								break;
							}
							break;
					}
				}
			}
		}




		} catch (\Throwable $th) {
			//throw $th;
			$estado=false;
			$mensaje=$th;
		}

		if ($estado==true) {
			$mensaje = "Los datos son correctos";
			$estado= true;
		}

		$dato=Array("estado"=>$estado, "mensaje"=>$mensaje);
		return $dato;
	}
}