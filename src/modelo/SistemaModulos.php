<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;
use App\Modelo\Sistema;



class SistemaModulos extends Model{
	protected $table = 'SISTEMA_MODULOS';
	public $timestamps = false;
	protected $primaryKey = ['IDSISTEMA'];



	public static function modulos()
    {
       return (new SistemaModulos())->hasMany(new Modulos(),"IDMODULO","IDMODULO")->with('objacceso');
	   //return Sistema::hasMany(Sistema::class);
	}



	public static function getTableColumns() {
        return (new Modulos)
            ->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing((new Modulos)->getTable());
	}

	public static function getTableFilas($idSistema) {
		$result = array();
		//$datos = ["IDSISTEMA"=>$idSistema, "ESTADO"=> 1];
		$query = Modulos::query()
		->selectRaw("MODULOS.*,MODULOS.IDMODULO AS ACCION")
		->where("IDSISTEMA",$idSistema)
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
	
	public static function getModulosActivo(){
		$result = array();
		$query = Modulos::query()
		->selectRaw("IDMODULO,NOMBRE")
		->where("ESTADO",Constante::ESTADO_ACTIVO)
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

	public static function getModulosActivoByIdSistema($IDSISTEMA){
		$result = array();
		$query = Modulos::query()
		->selectRaw("IDMODULO as id,NOMBRE as text")
		->where("ESTADO",Constante::ESTADO_ACTIVO)
		->where("IDSISTEMA",$IDSISTEMA)
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