<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;
use App\Modelo\Sistema;



class Empresa extends Model{
	protected $table = 'EMPRESA';
	public $timestamps = false;
	protected $primaryKey = 'IDEMPRESA';
	//protected $guarded = ['IDEmpresa', 'IDSISTEMA'];


	public static function sistemas()
    {
        return (new Empresa())->belongsTo(new Sistema, "IDSISTEMA", "IDSISTEMA");
	}

	public static function objacceso()
    {
       return (new Empresa())->hasMany(new Objacceso(),"IDMODULO","IDMODULO")->with('menus');
	   //return Sistema::hasMany(Sistema::class);
	}


	public static function getTableColumns() {
        return (new Empresa)
            ->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing((new Empresa)->getTable());
	}

	public static function getTableFilas($idSistema) {
		$result = array();
		//$datos = ["IDSISTEMA"=>$idSistema, "ESTADO"=> 1];
		$query = Empresa::query()
		->selectRaw("Empresa.*,Empresa.IDMODULO AS ACCION, SISTEMA_Empresa.IDSISTEMA as IDSISTEMA ")
		->join("SISTEMA_Empresa", "SISTEMA_Empresa.IDMODULO", "=", "Empresa.IDMODULO")
		->where("SISTEMA_Empresa.IDSISTEMA",$idSistema)
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

	public static function getEmpresaActivo(){
		$result = array();
		$query = Empresa::query()
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

	public static function getEmpresaActivoByIdSistema($IDSISTEMA){
		$result = array();
		$query = Empresa::query()
		->selectRaw("Empresa.IDMODULO as id,Empresa.NOMBRE as text")
		->where("Empresa.ESTADO",Constante::ESTADO_ACTIVO)
		->join("SISTEMA_Empresa", "SISTEMA_Empresa.IDMODULO", "=", "Empresa.IDMODULO")
		->where("SISTEMA_Empresa.IDSISTEMA",$IDSISTEMA)
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


	public static function ListarEmpresa(){
		$result = array();
		$query = Empresa::query()
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
