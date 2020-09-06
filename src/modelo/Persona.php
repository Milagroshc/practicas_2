<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use App\Helper\Constante;

class Persona extends Model{
	protected $table = 'PERSONA';
	public $timestamps = false;
	protected $primaryKey = 'IDPERSONA';

	protected $fillable = ['TIPODOCUMENTO', 'NRODOCUMENTO', 'NOMBRES'];


/*	public static function getTableColumns() {
        return (new Persona)
            ->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing((new Persona)->getTable());
	}*/


	public static function transportistas()
		{
			 //return (new Persona())->hasMany(new Empresa(),"IDPERSONA","IDPERSONA")->with('menus');
			 return (new Empresa())->hasMany(new VinculoEmpresa(),"IDEMPRESA","IDEMPRESA")->where('TIPO_VINCULO', CONSTANTE::TIPO_VINCULO_EMPRESA_TRANSPORTE);
		 //return Sistema::hasMany(Sistema::class);
	}

	public static function proveedores()
		{
			 //return (new Persona())->hasMany(new Empresa(),"IDPERSONA","IDPERSONA")->with('menus');
			 return (new Empresa())->hasMany(new VinculoEmpresa(),"IDEMPRESA","IDEMPRESA")->where('TIPO_VINCULO', CONSTANTE::TIPO_VINCULO_EMPRESA_PROVEEDOR);
		 //return Sistema::hasMany(Sistema::class);
	}



	public static function personal()
		{
			 //return (new Persona())->hasMany(new Empresa(),"IDPERSONA","IDPERSONA")->with('menus');
			 return (new Empresa())->hasMany(new Personal(),"IDEMPRESA","IDEMPRESA");
		 //return Sistema::hasMany(Sistema::class);
	}

	public static function personas()
		{
			 //return (new Persona())->hasMany(new Empresa(),"IDPERSONA","IDPERSONA")->with('menus');
			 return (new Persona())->hasMany(new Empresa(),"IDPERSONA","IDPERSONA");
		 //return Sistema::hasMany(Sistema::class);
	}

	public static function getPersonas(){
			$result = array();
			$datos = ["TIPO_PERSONA"=>Constante::TIPO_PERSONA_NATURAL];
			$query = Persona::query()
			->selectRaw("PERSONA.*")
			->where($datos)
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



	public static function getEmpresas(){
			$result = array();
			//$datos = ["IDSISTEMA"=>$idSistema, "ESTADO"=> 1];
			$query = Persona::query()
			->selectRaw("PERSONA.*,EMPRESA.*")
			->join("EMPRESA", "EMPRESA.IDPERSONA", "=", "PERSONA.IDPERSONA")
			->with('proveedores','transportistas','personal')
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





	public static function getTableColumns()
	{
	    return DB::select(
	        DB::raw('SHOW COLUMNS FROM PERSONA')
	    );
	}

	public static function getTableFilas() {
		$result = array();
		//$datos = ["IDSISTEMA"=>$idSistema, "ESTADO"=> 1];
		$query = Persona::query()
		->selectRaw("PERSONA.IDPERSONA,
		(SELECT NOMBRE FROM TAB_TABLAS WHERE IDTABLA=PERSONA.TIPODOCUMENTO) as 'NOMBRE TIPO DOCUMENTO',
		PERSONA.TIPO_PERSONA,
		(SELECT NOMBRE FROM TAB_TABLAS WHERE IDTABLA=PERSONA.TIPO_PERSONA) as 'NOMBRE TIPO PERSONA',
		PERSONA.*,
		PERSONA.IDPERSONA AS ACCION")
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

	public static function BuscarNumDoc($tipodoc,$numdoc){
		$result = array();
		$query = Persona::query()
		->selectRaw("*")
		->where("tipo_doc",$tipodoc)
		->where("num_doc",$numdoc)
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
		Persona::insert($data);
	}

	public static function ActualizarRegistro($idpersona,$data){
		Persona::query()
		->where(['idpersona'=>intval($idpersona)])
		->update($data);
	}


	public static function verificaCorreo($correo){
		$result = array();
		$query = Persona::query()
		->selectRaw("*")
		->where("correo_personal",$correo)
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

	public static function validaPersona($datos) {
		$result = array();

		//buscamos en objacceso
		$estado = Persona::where($datos)->exists();
		if ($estado) {
			# code...
			$success = true;
			$message = "Esta persona ya existe con este DNI";
		}else{
			$success = false;
			$message = "Esta persona no existe";
		}
		$result["success"] = $success;
		$result["message"] = $message;
		return $result;
	}


	public static function getPersonaByTipDocNro($datos) {
		$result = array();

		//buscamos en objacceso
		$estado = Persona::where($datos)->exists();
		if ($estado) {
			# code...
			$success = true;
			$mensaje = "Esta persona ya existe con este documento";
		}else{
			$success = false;
			$mensaje = "Esta persona no existe";
		}
		$result["success"] = $success;
		$result["mensaje"] = $mensaje;
		return $result;
	}


}
