<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use App\Helper\Constante;
use App\Modelo\Distrito;

class ReporteTiempoLugarVolumen extends Model{
	protected $table = 'reporte_tiempo_lugar_volumen';
	public $timestamps = false;

	

	public static function getDistinctAnios(){
		$result = array();
		$query = ReporteTiempoLugarVolumen::query()
		->selectRaw("annio")
		//->where("reporte_tiempo_lugar_volumen.idIndicador",$idIndicador)
		->distinct()
		->orderBy('annio', 'DESC')
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

	public static function getDistinctAniosTable(){
		$result = array();
		$query = ReporteTiempoLugarVolumen::query()
		->selectRaw("annio as data")
		//->where("reporte_tiempo_lugar_volumen.idIndicador",$idIndicador)
		->distinct()
		->orderBy('annio', 'DESC')
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

	

	public static function getDistinctProductos(){
		$result = array();
		$query = ReporteTiempoLugarVolumen::query()
		->selectRaw("producto")
		//->where("reporte_tiempo_lugar_volumen.idIndicador",$idIndicador)
		->distinct()
		->orderBy('producto', 'ASC')
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

	public static function getHistoricoVolumen($anio){
		$result = array();
		$query = ReporteTiempoLugarVolumen::query()
		->selectRaw("producto,
		sum(case when mes='ENE' then volumen else 0 end) as Enero,
		sum(case when mes='FEB' then volumen else 0 end) as Febrero,
		sum(case when mes='MAR' then volumen else 0 end) as Marzo,
		sum(case when mes='ABR' then volumen else 0 end) as Abril,
		sum(case when mes='MAY' then volumen else 0 end) as Mayo,
		sum(case when mes='JUN' then volumen else 0 end) as Junio,
		sum(case when mes='JUL' then volumen else 0 end) as Julio,
		sum(case when mes='AGO' then volumen else 0 end) as Agosto,
		sum(case when mes='SET' then volumen else 0 end) as Setiembre,
		sum(case when mes='OCT' then volumen else 0 end) as Octubre,
		sum(case when mes='NOV' then volumen else 0 end) as Noviembre,
		sum(case when mes='DIC' then volumen else 0 end) as Diciembre")
		->where("annio",$anio)
		//->distinct()
		->groupBy('producto')
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

	public static function getProcedencia($anio){
		$result = array();
		$query = ReporteTiempoLugarVolumen::query()
		->selectRaw("departamento,
		sum(case when mes='ENE' then volumen else 0 end) as Enero,
		sum(case when mes='FEB' then volumen else 0 end) as Febrero,
		sum(case when mes='MAR' then volumen else 0 end) as Marzo,
		sum(case when mes='ABR' then volumen else 0 end) as Abril,
		sum(case when mes='MAY' then volumen else 0 end) as Mayo,
		sum(case when mes='JUN' then volumen else 0 end) as Junio,
		sum(case when mes='JUL' then volumen else 0 end) as Julio,
		sum(case when mes='AGO' then volumen else 0 end) as Agosto,
		sum(case when mes='SET' then volumen else 0 end) as Setiembre,
		sum(case when mes='OCT' then volumen else 0 end) as Octubre,
		sum(case when mes='NOV' then volumen else 0 end) as Noviembre,
		sum(case when mes='DIC' then volumen else 0 end) as Diciembre")
		->where("annio",$anio)
		//->distinct()
		->groupBy('departamento')
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

	public static function getVariedad($producto){
		$result = array();
		$query = ReporteTiempoLugarVolumen::query()
		->selectRaw("reporte_tiempo_lugar_volumen.variedad")
		->where("reporte_tiempo_lugar_volumen.producto",$producto)
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

	public static function getVariacionProcedencia($data){
		$result = array();
		switch ($data["serie"]) {
			case 'anual':
			$anios = ReporteTiempoLugarVolumen::getDistinctAnios();

			$sumQuery="";
			for ($i=0; $i < count($anios["data"]); $i++) { 
				if($i==count($anios["data"])-1){
					$sumQuery=$sumQuery." 
					SUM(CASE WHEN annio = ".$anios["data"][$i]["annio"]." THEN volumen ELSE 0 END) AS \"".$anios["data"][$i]["annio"]."\"";
				}else{
					$sumQuery=$sumQuery." 
				SUM(CASE WHEN annio = ".$anios["data"][$i]["annio"]." THEN volumen ELSE 0 END) AS \"".$anios["data"][$i]["annio"]."\",";
				}
				
			}
			$query = ReporteTiempoLugarVolumen::query()
			->selectRaw("departamento, ".$sumQuery)
			->where("producto",$data["producto"])
			->where("variedad",$data["variedad"])
			//->distinct()
			->groupBy('departamento')
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
				break;
			case 'mensual':
				# code...
				break;	
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
			SUM(CASE WHEN reporte_tiempo_lugar_volumen.anio = ".$datos["anio"][$i]." THEN reporte_tiempo_lugar_volumen.valor ELSE 0 END) AS \"A-".$datos["anio"][$i]."\",";
		}
	
		$dataWhere=Array(
			"indicador.idIndicador" =>$datos["indicador"]
		);
		$dataWhereIn=$datos["distrito"];

		$users = DB::select("SET lc_time_names = 'es_ES';"); //convierte a español todo mysql
		$query = ReporteTiempoLugarVolumen::query()
		->selectRaw("
		distrito.nombre as distrito,
		".$sumQuery."
		SUM(reporte_tiempo_lugar_volumen.valor) as Total,
		count(reporte_tiempo_lugar_volumen.valor) as cantidad,
		tematica.idTematica,
		indicador.idIndicador,
		indicador.idTematica,
		reporte_tiempo_lugar_volumen.idDistrito,
		reporte_tiempo_lugar_volumen.fecha_registro,
		reporte_tiempo_lugar_volumen.anio as Anio,
		indicador.unidad")
		->join("indicador","reporte_tiempo_lugar_volumen.idIndicador","=","indicador.idIndicador")
		->join("distrito","reporte_tiempo_lugar_volumen.idDistrito","=","distrito.idDistrito")
		->join("tematica","indicador.idTematica","=","tematica.idTematica")
		//->where("idacceso",$idacceso)
		->where($dataWhere)
		->whereIn('reporte_tiempo_lugar_volumen.idDistrito',$dataWhereIn)
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
			SUM(CASE WHEN reporte_tiempo_lugar_volumen.anio = ".$datos["anio"][$i]." THEN reporte_tiempo_lugar_volumen.valor ELSE 0 END) AS \"".$datos["anio"][$i]."\",";
		}
	
		$dataWhere=Array(
			"indicador.idIndicador" =>$datos["indicador"]
		);
		$dataWhereIn=$datos["distrito"];

		$users = DB::select("SET lc_time_names = 'es_ES';"); //convierte a español todo mysql
		$query = ReporteTiempoLugarVolumen::query()
		->selectRaw("
		distrito.nombre as distrito,
		".$sumQuery."
		SUM(reporte_tiempo_lugar_volumen.valor) as Total")
		->join("indicador","reporte_tiempo_lugar_volumen.idIndicador","=","indicador.idIndicador")
		->join("distrito","reporte_tiempo_lugar_volumen.idDistrito","=","distrito.idDistrito")
		->join("tematica","indicador.idTematica","=","tematica.idTematica")
		//->where("idacceso",$idacceso)
		->where($dataWhere)
		->whereIn('reporte_tiempo_lugar_volumen.idDistrito',$dataWhereIn)
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
		$query = ReporteTiempoLugarVolumen::query()
		->selectRaw("reporte_tiempo_lugar_volumen.idIndicador,
		reporte_tiempo_lugar_volumen.anio")
		->where("reporte_tiempo_lugar_volumen.idIndicador",$idIndicador)
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
		$query = ReporteTiempoLugarVolumen::query()
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
		ReporteTiempoLugarVolumen::insert($data);
	}

	public static function ActualizarRegistro($idacceso,$idaccion,$data){
		ReporteTiempoLugarVolumen::query()
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
				$estado2 = ReporteTiempoLugarVolumen::query()
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