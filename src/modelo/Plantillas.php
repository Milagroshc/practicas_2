<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;

class Plantillas extends Model{
	protected $table = 'plantillas';
	public $timestamps = false;

	public static function ListarPlantillas($pag){
		$reg_por_pag = Constante::Item_Pag_10;
		$result = array();
		$query = Plantillas::query()
		->selectRaw("*")
		->offset(($pag-1)*$reg_por_pag)
		->limit($reg_por_pag)
		->get()
		->toArray();

		$query_count = Plantillas::query()
		->selectRaw("COUNT(DISTINCT idplantilla) as total_registros")
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

	public static function FiltrarPlantillas($pag,$params){
		$reg_por_pag = Constante::Item_Pag_10;
		$result = array();
		$query = Plantillas::query()
		->selectRaw("*")
		->where("nombre_plantilla","like","%".$params["nombre_plantilla"]."%")
		->where("estado","like","%".$params["estado"]."%")
		->offset(($pag-1)*$reg_por_pag)
		->limit($reg_por_pag)
		->get()
		->toArray();

		$query_count = Plantillas::query()
		->selectRaw("COUNT(DISTINCT idplantilla) as total_registros")
		->where("nombre_plantilla","like","%".$params["nombre_plantilla"]."%")
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

	public static function DatosPlantillaById($idplantilla){
		$result = array();
		$query = Plantillas::query()
		->selectRaw("*")
		->where("idplantilla",$idplantilla)
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
		Plantillas::insert($data);
	}

	public static function ActualizarRegistro($idplantilla,$data){
		Plantillas::query()
		->where(['idplantilla'=>intval($idplantilla)])
		->update($data);
	}
}