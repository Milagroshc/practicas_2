<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;

class Indicador extends Model{
	protected $table = 'indicador';
	public $timestamps = false;

public static function getIndicadorById($idIndicador){
	$result = array();
	$query = Indicador::query()
			->selectRaw("indicador.idIndicador,
			indicador.idTematica,
			indicador.nombre,
			indicador.descripcion,
			indicador.estado,
			indicador.unidad,
			indicador.prioridad,
			indicador.difundir,
			(select nombre_parametro from parametros where parametros.idparametro= indicador.periodo_generacion) AS periodo_generacion,
			(select nombre_parametro from parametros where parametros.idparametro= indicador.periodo_sinia) AS periodo_sinia")
			->where("idIndicador",$idIndicador)
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

	public static function ListarIndicador(){
		$result = array();
		$query = Indicador::query()
				->selectRaw("*")
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

	public static function FiltrarIndicadores($pag,$params){
		$reg_por_pag = Constante::Item_Pag_20;
		$result = array();
		
		$query = Indicador::query()
		->selectRaw("*")
		->where("nombre_Indicador","like","%".$params["nombre_Indicador"]."%")
		->where("estado","like","%".$params["estado"]."%")
		->offset(($pag-1)*$reg_por_pag)
		->limit($reg_por_pag)
		->get()
		->toArray();

		$query_count = Indicador::query()
		->selectRaw("COUNT(DISTINCT idIndicador) as total_registros")
		->where("nombre_Indicador","like","%".$params["nombre_Indicador"]."%")
		->where("estado","like","%".$params["estado"]."%")
		->get()
		->toArray();

		if (!empty($query)) {
			$result["data"] = $query;
			$result["reg_por_pag"] = $reg_por_pag;
			$result["total_registros"] = $query_count[0]["total_registros"];
			$success = true;
			$message = "Los datos se listaron correctamente";
		} else {
			$result["total_registros"] = 0;
			$success = false;
			$message = "No existen resultados";
		}
		$result["success"] = $success;
		$result["message"] = $message;
		return $result;
	}


	public static function ListarIndicadorByIdTematica($idTematica){
		$result = array();
		$query = Indicador::query()
		->selectRaw("indicador.idIndicador,
		indicador.idTematica,
		indicador.nombre,
		indicador.descripcion,
		indicador.estado,
		indicador.unidad,
		indicador.prioridad,
		indicador.difundir,
		indicador.periodo_generacion,
		indicador.periodo_sinia,
		(select nombre_parametro from parametros where parametros.idparametro=indicador.periodo_generacion) as nombre_periodo_generacion,
		(select nombre_parametro from parametros where parametros.idparametro=indicador.periodo_sinia) as nombre_periodo_sinia")
		->join('parametros', 'indicador.periodo_generacion', '=', 'parametros.idparametro')
		->where("idTematica",$idTematica)
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

	public static function DatosIndicadorById($idIndicador){
		$result = array();
		$query = Indicador::query()
		->selectRaw("*")
		->where("idIndicador",$idIndicador)
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

	public static function DatosIndicadorByNombre($nombre_Indicador){
		$result = array();
		$query = Indicador::query()
		->selectRaw("*")
		->where("nombre_Indicador",$nombre_Indicador)
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
		Indicador::insert($data);
	}

	public static function ActualizarRegistro($idIndicador,$data){
		Indicador::query()
		->where(['idIndicador'=>intval($idIndicador)])
		->update($data);
	}
}