<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;

class Tematica extends Model{
	protected $table = 'tematica';
	public $timestamps = false;



	
	public static function getTematicaByDescriptor($idParametro){
		$result = array();
		$query = Tematica::query()
		->selectRaw("tematica.idTematica,
		tematica.nombre,
		descriptor_tematico.estado")
		->join('descriptor_tematico', 'descriptor_tematico.idTematica', '=', 'tematica.idTematica')
		->where( 'descriptor_tematico.idparametro',$idParametro)
		->orderBy('tematica.nombre', 'ASC')
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



	public static function ListarTematica(){
		$result = array();

		
		$query = Tematica::query()
		->selectRaw("tematica.*,docadjunto.urlAdjunto")
		->join('docadjunto', 'tematica.idDocAdjunto', '=', 'docadjunto.idDocAdjunto')
		->where( 'tematica.estado',1)
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
	public static function ListarTematicaAll(){
		$result = array();
		$query = Tematica::query()
				->selectRaw("tematica.*,docadjunto.urlAdjunto")
				->join('docadjunto', 'tematica.idDocAdjunto', '=', 'docadjunto.idDocAdjunto')
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

	public static function FiltrarTematicaes($pag,$params){
		$reg_por_pag = Constante::Item_Pag_20;
		$result = array();
		
		$query = Tematica::query()
		->selectRaw("*")
		->where("nombre_Tematica","like","%".$params["nombre_Tematica"]."%")
		->where("estado","like","%".$params["estado"]."%")
		->offset(($pag-1)*$reg_por_pag)
		->limit($reg_por_pag)
		->get()
		->toArray();

		$query_count = Tematica::query()
		->selectRaw("COUNT(DISTINCT idTematica) as total_registros")
		->where("nombre_Tematica","like","%".$params["nombre_Tematica"]."%")
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


	public static function getTematicaByUrl($url){
		$result = array();

		$query = Tematica::query()
		->selectRaw("tematica.idTematica,
		tematica.nombre,
		tematica.descripcion,
		(select urlAdjunto from docadjunto where docadjunto.idDocAdjunto= tematica.banner) as urlBanner,
		tematica.estado,
		tematica.orden,
		tematica.url,
		tematica.token_url,
		tematica.fecha_registro,
		(select urlAdjunto from docadjunto where docadjunto.idDocAdjunto= tematica.idDocAdjunto) as urlAdjunto")
		//->join('docadjunto', 'tematica.idDocAdjunto', '=', 'docadjunto.idDocAdjunto')
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

	public static function DatosTematicaById($idTematica){
		$result = array();
		$query = Tematica::query()
		->selectRaw("*")
		->where("idTematica",$idTematica)
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

	public static function DatosTematicaByNombre($nombre_Tematica){
		$result = array();
		$query = Tematica::query()
		->selectRaw("*")
		->where("nombre_Tematica",$nombre_Tematica)
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
		Tematica::insert($data);
	}

	public static function ActualizarRegistro($idTematica,$data){
		Tematica::query()
		->where(['idTematica'=>intval($idTematica)])
		->update($data);
	}
}