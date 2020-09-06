<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;
use App\Modelo\Sistema;



class VinculoEmpresa extends Model{
	protected $table = 'VINCULO_EMPRESA';
	public $timestamps = false;
	protected $primaryKey = 'IDVINCULOEMPRESA';
	//protected $guarded = ['IDVinculoEmpresa', 'IDSISTEMA'];

	public static function transportistas()
    {
				 $estado = ["TAB_TABLAS.ESTADO"=> Constante::ESTADO_ACTIVO];
	       return (new Persona())->hasMany(new Empresa(),"IDCATALOGOTABLAS","IDCATALOGOTABLAS")->where($estado);
		   //return Catalogotablas::hasMany(Catalogotablas::class);
		}

public static function getProveedores(){
	$result = array();
	//$datos = ["IDSISTEMA"=>$idSistema, "ESTADO"=> 1];
	$query = VinculoEmpresa::query()
	->selectRaw("VINCULO_EMPRESA.IDEMPRESAVINCULO,
(SELECT PERSONA.NOMBRES FROM PERSONA INNER JOIN EMPRESA ON EMPRESA.IDPERSONA = PERSONA.IDPERSONA WHERE EMPRESA.IDEMPRESA= VINCULO_EMPRESA.IDEMPRESAVINCULO) AS EMPRESA,
(SELECT	NOMBRE FROM TAB_TABLAS WHERE IDTABLA=VINCULO_EMPRESA.TIPO_VINCULO) AS VINCULO")
	->where("TIPO_VINCULO",Constante::TIPO_VINCULO_EMPRESA_PROVEEDOR)
	->with('transportistas')
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
	return $result["data"];
}



	public static function sistemas()
    {
        return (new VinculoEmpresa())->belongsTo(new Sistema, "IDSISTEMA", "IDSISTEMA");
	}

	public static function objacceso()
    {
       return (new VinculoEmpresa())->hasMany(new Objacceso(),"IDMODULO","IDMODULO")->with('menus');
	   //return Sistema::hasMany(Sistema::class);
	}


	public static function getTableColumns() {
        return (new VinculoEmpresa)
            ->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing((new VinculoEmpresa)->getTable());
	}

	public static function getTableFilas($idSistema) {
		$result = array();
		//$datos = ["IDSISTEMA"=>$idSistema, "ESTADO"=> 1];
		$query = VinculoEmpresa::query()
		->selectRaw("VinculoEmpresa.*,VinculoEmpresa.IDMODULO AS ACCION, SISTEMA_VinculoEmpresa.IDSISTEMA as IDSISTEMA ")
		->join("SISTEMA_VinculoEmpresa", "SISTEMA_VinculoEmpresa.IDMODULO", "=", "VinculoEmpresa.IDMODULO")
		->where("SISTEMA_VinculoEmpresa.IDSISTEMA",$idSistema)
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

	public static function getVinculoEmpresaActivo(){
		$result = array();
		$query = VinculoEmpresa::query()
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

	public static function getVinculoEmpresaActivoByIdSistema($IDSISTEMA){
		$result = array();
		$query = VinculoEmpresa::query()
		->selectRaw("VinculoEmpresa.IDMODULO as id,VinculoEmpresa.NOMBRE as text")
		->where("VinculoEmpresa.ESTADO",Constante::ESTADO_ACTIVO)
		->join("SISTEMA_VinculoEmpresa", "SISTEMA_VinculoEmpresa.IDMODULO", "=", "VinculoEmpresa.IDMODULO")
		->where("SISTEMA_VinculoEmpresa.IDSISTEMA",$IDSISTEMA)
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


	public static function ListarVinculoEmpresa(){
		$result = array();
		$query = VinculoEmpresa::query()
		->select()
		//->orderBy("orden")
		->get();
		//->toArray();

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
		return $query;
	}
}
