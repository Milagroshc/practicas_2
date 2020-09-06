<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;

class PlantillaItemModulo extends Model{
	protected $table = 'plantilla_item_modulo';
	public $timestamps = false;

	public static function SeccionesByPlantilla($pag,$idplantilla,$tipo){
		$reg_por_pag = Constante::Item_Pag_10;
		$result = array();
		switch ($tipo) {
			case 1: // PARA MANTENIMIENTO DE PLANTILLAS
				$query = PlantillaItemModulo::query()
				->selectRaw("plantillas.idplantilla, plantillas.nombre_plantilla, plantillas.ruta_plantilla, item_modulos.id_item_modulo, item_modulos.nombre_item, item_modulos.archivo_portal_web, plantilla_item_modulo.orden, plantilla_item_modulo.estado")
				->join("plantillas","plantillas.idplantilla","=","plantilla_item_modulo.idplantilla")
				->join("item_modulos","item_modulos.id_item_modulo","=","plantilla_item_modulo.id_item_modulo")
				->where("plantillas.idplantilla",$idplantilla)
				->orderBy("plantilla_item_modulo.orden")
				->offset(($pag-1)*$reg_por_pag)
				->limit($reg_por_pag)
				->get()
				->toArray();

				$query_count = PlantillaItemModulo::query()
				->selectRaw("COUNT(DISTINCT plantilla_item_modulo.id_item_modulo) as total_registros")
				->join("plantillas","plantillas.idplantilla","=","plantilla_item_modulo.idplantilla")
				->join("item_modulos","item_modulos.id_item_modulo","=","plantilla_item_modulo.id_item_modulo")
				->where("plantillas.idplantilla",$idplantilla)
				->orderBy("plantilla_item_modulo.orden")
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
			break;
			
			case 2: // PARA LISTAR EN LA WEB
				$query = PlantillaItemModulo::query()
				->selectRaw("plantillas.idplantilla, plantillas.nombre_plantilla, plantillas.ruta_plantilla, item_modulos.id_item_modulo, item_modulos.nombre_item, item_modulos.archivo_portal_web, plantilla_item_modulo.orden, plantilla_item_modulo.estado")
				->join("plantillas","plantillas.idplantilla","=","plantilla_item_modulo.idplantilla")
				->join("item_modulos","item_modulos.id_item_modulo","=","plantilla_item_modulo.id_item_modulo")
				->where("plantillas.idplantilla",$idplantilla)
				->where("plantilla_item_modulo.estado",1)
				->orderBy("plantilla_item_modulo.orden")
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
		}
		$result["success"] = $success;
		$result["message"] = $message;
		return $result;
	}

	public static function VerificarPlantillaSeccion($idplantilla,$idseccion){
		$result = array();
		$query = PlantillaItemModulo::query()
		->selectRaw("*")
		->where("idplantilla",$idplantilla)
		->where("id_item_modulo",$idseccion)
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
		PlantillaItemModulo::insert($data);
	}

	public static function ActualizarRegistro($idplantilla,$idseccion,$data){
		PlantillaItemModulo::query()
		->where(['idplantilla'=>intval($idplantilla)])
		->where(['id_item_modulo'=>intval($idseccion)])
		->update($data);
	}
}