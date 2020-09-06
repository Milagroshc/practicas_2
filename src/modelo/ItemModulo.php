<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;

class ItemModulo extends Model{
	protected $table = 'item_modulos';
	public $timestamps = false;

	public static function ListarItemModuloByAcceso($idrol){
		$result = array();
		$query = ItemModulo::query()
		->selectRaw("item_modulos.*,(IF(item_modulos.id_item_modulo in (select accesos.id_item_modulo from accesos where accesos.idrol=".$idrol." AND accesos.estado=1),'SI','NO')) as tiene")
		->join("modulos", "modulos.idmodulo", "=", "item_modulos.id_modulo")
		->whereRaw("url IS NOT NULL")
		->orderBy("modulos.orden")
		->orderBy("item_modulos.orden")
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
	
	public static function ListarItemModulos($pag,$tipo){
		$reg_por_pag = Constante::Item_Pag_10;
		$result = array();
		switch ($tipo) {
			case 1: // LISTA PARA LOS COMBOS DENTRO DEL CMS
				$query = ItemModulo::query()
				->selectRaw("*")
				->where("estado",1)
				->whereRaw("archivo_portal_web IS NOT NULL")
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
			case 2: // LISTA PARA EL MANTENIMIENTO DE SECCIONES
				$query = ItemModulo::query()
				->selectRaw("*")
				->whereRaw("archivo_portal_web IS NOT NULL")
				->offset(($pag-1)*$reg_por_pag)
				->limit($reg_por_pag)
				->get()
				->toArray();

				$query_count = ItemModulo::query()
				->selectRaw("COUNT(DISTINCT id_item_modulo) as total_registros")
				->whereRaw("archivo_portal_web IS NOT NULL")
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
		}
		$result["success"] = $success;
		$result["message"] = $message;
		return $result;
	}

	public static function FiltrarSecciones($pag,$params){
		$reg_por_pag = Constante::Item_Pag_10;
		$result = array();
		$query = ItemModulo::query()
		->selectRaw("*")
		->where("nombre_item","like","%".$params["nombre_item"]."%")
		->where("estado","like","%".$params["estado"]."%")
		->whereRaw("archivo_portal_web IS NOT NULL")
		->offset(($pag-1)*$reg_por_pag)
		->limit($reg_por_pag)
		->get()
		->toArray();

		$query_count = ItemModulo::query()
		->selectRaw("COUNT(DISTINCT id_item_modulo) as total_registros")
		->where("nombre_item","like","%".$params["nombre_item"]."%")
		->where("estado","like","%".$params["estado"]."%")
		->whereRaw("archivo_portal_web IS NOT NULL")
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

	public static function DatosById($id_item_modulo){
		$result = array();
		$query = ItemModulo::query()
		->selectRaw("*")
		->where("id_item_modulo",$id_item_modulo)
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
		ItemModulo::insert($data);
	}

	public static function ActualizarRegistro($id_item_modulo,$data){
		ItemModulo::query()
		->where(['id_item_modulo'=>intval($id_item_modulo)])
		->update($data);
	}
}