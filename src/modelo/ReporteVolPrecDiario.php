<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use App\Helper\Constante;
use App\Modelo\Distrito;

class ReporteVolPrecDiario extends Model{
	protected $table = 'reporte_vol_prec_diario';
	public $timestamps = false;

	
	public static function getVolumenPrecio($fecha){
		$result = array();
		$query = ReporteVolPrecDiario::query()
		->selectRaw("proddesc,variedad,maxi,mini,prom")
		->where("dia",$fecha["dia"])
		->where("mes",$fecha["mes"])
		->where("annio",$fecha["anio"])
		//->distinct()
		->orderBy('annio', 'DESC')
		->orderBy('mes', 'DESC')
		->orderBy('dia', 'DESC')
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

	public static function EstadisticaReporte($datos){
		$result = array();
		$sumQuery="";
		for ($i=0; $i < count($datos["anio"]); $i++) { 
			# code...
			$sumQuery=$sumQuery." 
			SUM(CASE WHEN reporte_vol_prec_diario.anio = ".$datos["anio"][$i]." THEN reporte_vol_prec_diario.valor ELSE 0 END) AS \"A-".$datos["anio"][$i]."\",";
		}
	
		$dataWhere=Array(
			"indicador.idIndicador" =>$datos["indicador"]
		);
		$dataWhereIn=$datos["distrito"];

		$users = DB::select("SET lc_time_names = 'es_ES';"); //convierte a español todo mysql
		$query = ReporteVolPrecDiario::query()
		->selectRaw("
		distrito.nombre as distrito,
		".$sumQuery."
		SUM(reporte_vol_prec_diario.valor) as Total,
		count(reporte_vol_prec_diario.valor) as cantidad,
		tematica.idTematica,
		indicador.idIndicador,
		indicador.idTematica,
		reporte_vol_prec_diario.idDistrito,
		reporte_vol_prec_diario.fecha_registro,
		reporte_vol_prec_diario.anio as Anio,
		indicador.unidad")
		->join("indicador","reporte_vol_prec_diario.idIndicador","=","indicador.idIndicador")
		->join("distrito","reporte_vol_prec_diario.idDistrito","=","distrito.idDistrito")
		->join("tematica","indicador.idTematica","=","tematica.idTematica")
		//->where("idacceso",$idacceso)
		->where($dataWhere)
		->whereIn('reporte_vol_prec_diario.idDistrito',$dataWhereIn)
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
			SUM(CASE WHEN reporte_vol_prec_diario.anio = ".$datos["anio"][$i]." THEN reporte_vol_prec_diario.valor ELSE 0 END) AS \"".$datos["anio"][$i]."\",";
		}
	
		$dataWhere=Array(
			"indicador.idIndicador" =>$datos["indicador"]
		);
		$dataWhereIn=$datos["distrito"];

		$users = DB::select("SET lc_time_names = 'es_ES';"); //convierte a español todo mysql
		$query = ReporteVolPrecDiario::query()
		->selectRaw("
		distrito.nombre as distrito,
		".$sumQuery."
		SUM(reporte_vol_prec_diario.valor) as Total")
		->join("indicador","reporte_vol_prec_diario.idIndicador","=","indicador.idIndicador")
		->join("distrito","reporte_vol_prec_diario.idDistrito","=","distrito.idDistrito")
		->join("tematica","indicador.idTematica","=","tematica.idTematica")
		//->where("idacceso",$idacceso)
		->where($dataWhere)
		->whereIn('reporte_vol_prec_diario.idDistrito',$dataWhereIn)
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
		$query = ReporteVolPrecDiario::query()
		->selectRaw("reporte_vol_prec_diario.idIndicador,
		reporte_vol_prec_diario.anio")
		->where("reporte_vol_prec_diario.idIndicador",$idIndicador)
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
		$query = ReporteVolPrecDiario::query()
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
		ReporteVolPrecDiario::insert($data);
	}

	public static function ActualizarRegistro($idacceso,$idaccion,$data){
		ReporteVolPrecDiario::query()
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
				$estado2 = ReporteVolPrecDiario::query()
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