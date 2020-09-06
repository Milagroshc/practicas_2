<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;

class Territorio extends Model{
	protected $table = 'territorio';
	public $timestamps = false;



	public static function getTerritoriosByIdParametro($idParametro){
		$result = array();
		$query = Territorio::query()
		->selectRaw("Territorio.*")
		->where( 'Territorio.idparametro',$idParametro)
		->orderBy('Territorio.nombre', 'ASC')
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
	public static function ListarTerritorioAll(){
		$result = array();
		$query = Territorio::query()
				->selectRaw("Territorio.*,docadjunto.urlAdjunto")
				->join('docadjunto', 'Territorio.idDocAdjunto', '=', 'docadjunto.idDocAdjunto')
				->orderBy('orden', 'ASC')
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

	public static function FiltrarTerritorioes($pag,$params){
		$reg_por_pag = Constante::Item_Pag_20;
		$result = array();
		
		$query = Territorio::query()
		->selectRaw("*")
		->where("nombre_Territorio","like","%".$params["nombre_Territorio"]."%")
		->where("estado","like","%".$params["estado"]."%")
		->offset(($pag-1)*$reg_por_pag)
		->limit($reg_por_pag)
		->get()
		->toArray();

		$query_count = Territorio::query()
		->selectRaw("COUNT(DISTINCT idTerritorio) as total_registros")
		->where("nombre_Territorio","like","%".$params["nombre_Territorio"]."%")
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


	public static function getTerritorioByUrl($url){
		$result = array();

		$query = Territorio::query()
		->selectRaw("Territorio.*,docadjunto.urlAdjunto")
		->join('docadjunto', 'Territorio.idDocAdjunto', '=', 'docadjunto.idDocAdjunto')
		->where("url",$url)
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

	public static function DatosTerritorioById($idTerritorio){
		$result = array();
		$query = Territorio::query()
		->selectRaw("*")
		->where("idTerritorio",$idTerritorio)
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

	public static function DatosTerritorioByNombre($nombre_Territorio){
		$result = array();
		$query = Territorio::query()
		->selectRaw("*")
		->where("nombre_Territorio",$nombre_Territorio)
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
		Territorio::insert($data);
	}

	public static function ActualizarRegistro($idTerritorio,$data){
		Territorio::query()
		->where(['idTerritorio'=>intval($idTerritorio)])
		->update($data);
	}
}