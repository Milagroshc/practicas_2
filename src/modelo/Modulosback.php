<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;

class Modulos extends Model{
	protected $table = 'modulos';
	public $timestamps = false;

	public static function getPlantillasByModulos($idPlantilla){
		$result = array();
		$query = Modulos::query()
		->selectRaw("
			modulos.idmodulo,
			modulos.nombre_modulo,
			item_modulos.nombre_item,
			item_modulos.url,
			item_modulos.archivo_portal_web,
			plantillas.idplantilla,
			plantillas.nombre_plantilla,
			plantillas.ruta_plantilla,
			plantilla_item_modulo.tipo
		")
		->join("item_modulos", "item_modulos.id_modulo", "=", "modulos.idmodulo")
		->join("plantilla_item_modulo", "plantilla_item_modulo.id_item_modulo", "=", "item_modulos.id_item_modulo")
		->join("plantillas", "plantilla_item_modulo.idplantilla", "=", "plantillas.idplantilla")
		->where("plantillas.idplantilla",$idPlantilla)
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


	public static function getModulos($idusuario,$idrol){
		$result = array();
		$query = Modulos::query()
		->selectRaw("DISTINCT modulos.idmodulo, modulos.nombre_modulo, modulos.icono, modulos.estado")
		->join("item_modulos", "item_modulos.id_modulo", "=", "modulos.idmodulo")
		->join("accesos", "accesos.id_item_modulo", "=", "item_modulos.id_item_modulo")
		->join("rol", "rol.idrol", "=", "accesos.idrol")
		->join("usuario_rol", "usuario_rol.idrol", "=", "usuario_rol.idusuario")
		->join("usuario", "usuario.idusuario", "=", "usuario_rol.idusuario")
		->where("usuario_rol.idusuario",$idusuario)
		->where("usuario_rol.idrol",$idrol)
		->where("accesos.estado",1)
		->orderBy("modulos.orden")
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

	public static function ListarModulos(){
		$result = array();
		$query = Modulos::query()
		->selectRaw("*")
		->orderBy("orden")
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
}