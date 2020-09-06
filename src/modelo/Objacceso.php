<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;
use App\Modelo\Modulos;



class Objacceso extends Model{
	protected $table = 'OBJACCESO';
	public $timestamps = false;
	protected $primaryKey = 'IDOBJACCESO';
	//protected $guarded = ['IDMODULOS', 'IDSISTEMA'];


	public static function modulos()
    {
        return (new Objacceso())->belongsTo(new Modulos, "IDMODULO", "IDMODULO")->with('sistemas');
	}

	public static function menus()
    {
       return (new Objacceso())->hasMany(new Menu(),"IDOBJACCESO","IDOBJACCESO");
	   //return Sistema::hasMany(Sistema::class);
	}
	

	public static function modulosbyruta($ruta) {
		$result = array();
		$query = Objacceso::query()
        ->select()
        ->where('RUTA',$ruta)
        ->with('modulos')
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
	public static function getObjaccesoById($idObjacceso) {
		$result = array();
		$query = Objacceso::query()
        ->select()
        ->where('IDOBJACCESO',$idObjacceso)
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


	public static function getTableColumns() {
        return (new Objacceso)
            ->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing((new Objacceso)->getTable());
	}
	
	public static function getTableFilas($idModulo) {
		$result = array();
		//$datos = ["IDSISTEMA"=>$idSistema, "ESTADO"=> 1];
		$query = Objacceso::query()
		->selectRaw("OBJACCESO.*,OBJACCESO.IDOBJACCESO AS ACCION")
		->where("IDMODULO",$idModulo)
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
	

	public static function getObjAccesoActivoByIdModulo($IDMODULO){
		$result = array();
		$query = Objacceso::query()
		->selectRaw("IDOBJACCESO as id,NOMBRE as text")
		->where("ESTADO",Constante::ESTADO_ACTIVO)
		->where("IDMODULO",$IDMODULO)
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