<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;
use App\Modelo\Sistema;



class RequicitoInspeccion extends Model{
	protected $table = 'REQUICITO_INSPECCION';
	public $timestamps = false;
	protected $primaryKey = 'IDREQUICITOINSPECCION';
	//protected $guarded = ['IDRequicitoInspeccion', 'IDSISTEMA'];


	public static function sistemas()
    {
        return (new RequicitoInspeccion())->belongsTo(new Sistema, "IDSISTEMA", "IDSISTEMA");
	}

	public static function hijo()
    {
       return (new RequicitoInspeccion())->hasMany(new RequicitoInspeccion(),"IDREQUICITOINSPECCIONPADRE","IDREQUICITOINSPECCION")->with('hijo');
	   //return Sistema::hasMany(Sistema::class);
	}
	
	

	public static function getAllRequicitoInspeccion(){
		$result = array();
		$query = RequicitoInspeccion::query()
		->selectRaw("*")
		->where("ESTADO",Constante::ESTADO_ACTIVO)
		->whereIn('IDTIPOOBJETOINSPECCION',Array(263,264,265,266,267))
		->with('hijo')
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
		return $query;
	}

	public static function getTableColumns() {
        return (new RequicitoInspeccion)
            ->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing((new RequicitoInspeccion)->getTable());
	}

	public static function getTableFilas($idSistema) {
		$result = array();
		//$datos = ["IDSISTEMA"=>$idSistema, "ESTADO"=> 1];
		$query = RequicitoInspeccion::query()
		->selectRaw("RequicitoInspeccion.*,RequicitoInspeccion.IDMODULO AS ACCION, SISTEMA_RequicitoInspeccion.IDSISTEMA as IDSISTEMA ")
		->join("SISTEMA_RequicitoInspeccion", "SISTEMA_RequicitoInspeccion.IDMODULO", "=", "RequicitoInspeccion.IDMODULO")
		->where("SISTEMA_RequicitoInspeccion.IDSISTEMA",$idSistema)
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
	
	public static function getRequicitoInspeccionActivo(){
		$result = array();
		$query = RequicitoInspeccion::query()
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

	public static function getRequicitoInspeccionActivoByIdSistema($IDSISTEMA){
		$result = array();
		$query = RequicitoInspeccion::query()
		->selectRaw("RequicitoInspeccion.IDMODULO as id,RequicitoInspeccion.NOMBRE as text")
		->where("RequicitoInspeccion.ESTADO",Constante::ESTADO_ACTIVO)
		->join("SISTEMA_RequicitoInspeccion", "SISTEMA_RequicitoInspeccion.IDMODULO", "=", "RequicitoInspeccion.IDMODULO")
		->where("SISTEMA_RequicitoInspeccion.IDSISTEMA",$IDSISTEMA)
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
	

	public static function ListarRequicitoInspeccion(){
		$result = array();
		$query = RequicitoInspeccion::query()
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