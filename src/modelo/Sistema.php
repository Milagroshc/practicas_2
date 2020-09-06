<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use App\Helper\Constante;

class Sistema extends Model{
	public $table = 'SISTEMA';
	public $timestamps = false;
	protected $primaryKey = 'IDSISTEMA';


	public static function listaSistemas(){
		$result = array();
		$query = Sistema::query()
		->select("*")
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

	public static function modulos()
    {
       //return (new Sistema())->hasMany(new SistemaModulos(),"IDSISTEMA","IDSISTEMA");
	   //return Sistema::hasMany(Sistema::class);

	   return (new Sistema())->hasManyThrough(
			new Modulos(), //modelo final
			new SistemaModulos(), //modelo intermedio
			'IDSISTEMA', // Foreign key on users table... . El tercer argumento es el nombre de la clave externa en el modelo intermedio
			'IDMODULO', // Foreign key on posts table...
			'IDSISTEMA', // Local key on countries table...
			'IDMODULO' // Local key on users table...
		)->with('objacceso');



	}




        public function moduls()
    {
        return $this->belongsToMany('App\Modelo\Modulos', "SISTEMA","IDSISTEMA","IDSISTEMA");
	}


	public static function getTableColumns() {
        return (new Sistema)
            ->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing((new Sistema)->getTable());
	}

	public static function getTableFilas() {
		$result = array();
		//$datos = ["IDSISTEMA"=>$idSistema, "ESTADO"=> 1];
		$query = Sistema::query()
		->selectRaw("SISTEMA.*,SISTEMA.IDSISTEMA AS ACCION")
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


	public static function getSistemasActivo(){
		$result = array();
		$query = Sistema::query()
		->selectRaw("IDSISTEMA,NOMBRE")
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

	public static function listaSistemasModulos($idSistema){
		$result = array();
		$datos = ["IDSISTEMA"=>$idSistema, "ESTADO"=> 1];
		$query = Login::query()
		->select("*")
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

	public static function getSistemaByRuta($ruta){
		$result = array();
		$where = ["RUTA"=>$ruta];
		$query = Sistema::query()
		->select("*")
		->where($where)
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
