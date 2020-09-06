<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;
use App\Modelo\Modulos;
use App\Modelo\Objacceso;



class Menu extends Model{
	protected $table = 'MENU';
	public $timestamps = false;
	protected $primaryKey = 'IDMENU';
	//protected $guarded = ['IDMODULOS', 'IDSISTEMA'];

/*
	public static function modulos()
    {
        return (new Objacceso())->belongsTo(new Modulos, "IDMODULO", "IDMODULO")->with('sistemas');
	}

	public static function menus()
    {
       return (new Objacceso())->hasMany(new Menu(),"IDOBJACCESO","IDOBJACCESO");
	   //return Sistema::hasMany(Sistema::class);
	}
	
*/


public static function getOpcionesByName($nombre) {
	$result = array();

	//buscamos en objacceso
	$datos = ['RUTA' => $nombre, 'MIDDLEWARE' => Constante::ESTADO_ACTIVO]; //verifica su existe la ruta y grupo para logearse o ser publico
	$estado = Objacceso::where($datos)->exists();
	if ($estado) {
		# se encontro coincidencia de la name route en OBJACCESO
		$result["data"] = ['estado' => $estado, 'tabla'=> 'OBJACCESO'];
		$success = true;
		$message = "Se encontró coincidencia en la tabla";
	}else{
		# no se existe dato en OBJACCESO
		# buscar en Menu
		$datosM = ['URL' => $nombre, 'MIDDLEWARE' => Constante::ESTADO_ACTIVO]; //verifica su existe la URL para logearse o ser publico
		$estadoM = Menu::where($datosM)->exists();
		if ($estadoM) {
			# code...
			$result["data"] = ['estado' => $estadoM, 'tabla'=> 'MENU'];
			$success = true;
			$message = "Se encontró coincidencia en la tabla";
		}else{
			$success = false;
			$message = "No se encontró coincidencia en la tabla";
		}
	}

	$result["success"] = $success;
	$result["message"] = $message;
	return $result;
}



public static function getOpcionesById($rutaObjacceso) {
	$result = array();
	$query = Menu::query()
	->select("MENU.*")
	->join("OBJACCESO", "MENU.IDOBJACCESO", "=", "OBJACCESO.IDOBJACCESO")
	->where('OBJACCESO.RUTA',$rutaObjacceso)
	->get()
	->toArray();


	if (!empty($query)) {
		$result["data"] = $query;
		$success = true;
		$message = "Los datos se listaron correctamente";
	} else {
		$success = false;
		$message = "No existen resultados de contenidos hijos";
	}
	$result["success"] = $success;
	$result["message"] = $message;
	return $result;
}

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

	public static function getTableColumns() {
        return (new Menu)
            ->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing((new Menu)->getTable());
	}
	
	public static function getTableFilas($idObjacceso) {
		$result = array();
		//$datos = ["IDSISTEMA"=>$idSistema, "ESTADO"=> 1];
		$query = Menu::query()
		->selectRaw("MENU.*,MENU.IDMENU AS ACCION")
		->where("IDOBJACCESO",$idObjacceso)
		->get()
		->toArray();
		if (!empty($query)) {
			$result["data"] = $query;
			$success = true;
			$message = "Se listo correctamente";
		} else {
			$success = false;
			$message = "No existen datos";
		}
		$result["success"] = $success;
		$result["message"] = $message;
		return $result;
    }

}