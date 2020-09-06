<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;
use App\Modelo\Sistema;



class Personal extends Model{
	protected $table = 'PERSONAL';
	public $timestamps = false;
	protected $primaryKey = 'IDPERSONAL';
	//protected $guarded = ['IDPersonal', 'IDSISTEMA'];

	public static function transportistas()
    {
				 $estado = ["TAB_TABLAS.ESTADO"=> Constante::ESTADO_ACTIVO];
	       return (new Persona())->hasMany(new Empresa(),"IDCATALOGOTABLAS","IDCATALOGOTABLAS")->where($estado);
		   //return Catalogotablas::hasMany(Catalogotablas::class);
		}

public static function getProveedores(){
	$result = array();
	//$datos = ["IDSISTEMA"=>$idSistema, "ESTADO"=> 1];
	$query = Personal::query()
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
        return (new Personal())->belongsTo(new Sistema, "IDSISTEMA", "IDSISTEMA");
	}

	public static function objacceso()
    {
       return (new Personal())->hasMany(new Objacceso(),"IDMODULO","IDMODULO")->with('menus');
	   //return Sistema::hasMany(Sistema::class);
	}


	public static function getTableColumns() {
        return (new Personal)
            ->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing((new Personal)->getTable());
	}

	public static function getTableFilas($idSistema) {
		$result = array();
		//$datos = ["IDSISTEMA"=>$idSistema, "ESTADO"=> 1];
		$query = Personal::query()
		->selectRaw("Personal.*,Personal.IDMODULO AS ACCION, SISTEMA_Personal.IDSISTEMA as IDSISTEMA ")
		->join("SISTEMA_Personal", "SISTEMA_Personal.IDMODULO", "=", "Personal.IDMODULO")
		->where("SISTEMA_Personal.IDSISTEMA",$idSistema)
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

	public static function getPersonalActivo(){
		$result = array();
		$query = Personal::query()
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

	public static function getPersonalActivoByIdSistema($IDSISTEMA){
		$result = array();
		$query = Personal::query()
		->selectRaw("Personal.IDMODULO as id,Personal.NOMBRE as text")
		->where("Personal.ESTADO",Constante::ESTADO_ACTIVO)
		->join("SISTEMA_Personal", "SISTEMA_Personal.IDMODULO", "=", "Personal.IDMODULO")
		->where("SISTEMA_Personal.IDSISTEMA",$IDSISTEMA)
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


	public static function ListarPersonal(){
		$result = array();
		$query = Personal::query()
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
