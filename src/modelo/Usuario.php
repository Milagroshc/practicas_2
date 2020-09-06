<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;

class Usuario extends Model{
	protected $table = 'USUARIO';
	public $timestamps = false;
	protected $primaryKey = 'IDUSUARIO';


	public static function getUsuarioByUser($datos) {
		$result = array();
		//buscamos en objacceso
		$estado = Usuario::where($datos)->exists();
		if ($estado) {
			# code...
			$success = true;
			$mensaje = "Este usuario ya existe";
		}else{
			$success = false;
			$mensaje = "Este usuario no existe";
		}
		$result["success"] = $success;
		$result["mensaje"] = $mensaje;
		return $result;
	}


	public static function VerificarLogin($data){
		$result = array();
		$query = Usuario::query()
		->selectRaw("*")
		->where($data)
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

	public static function getDatosSesion($idusuario){
		$result = array();
		$query = Usuario::query()
		->selectRaw("
		USUARIO.USUARIO,
		LOGIN.ACCESS_TOKEN,
		PERSONA.NOMBRES,
		PERSONA.FOTO")
		->join("PERSONA","USUARIO.IDPERSONA","=","PERSONA.IDPERSONA")
		->join("LOGIN","LOGIN.IDUSUARIO","=","USUARIO.IDUSUARIO")
		->where("USUARIO.IDUSUARIO",$idusuario)
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

	public static function getDatosApp($idusuario){
		$result = array();
		$query = Usuario::query()
		->selectRaw("
		USUARIO.USUARIO,
		LOGIN.ACCESS_TOKEN,
		PERSONA.NOMBRES,
		PERSONA.FOTO")
		->join("PERSONA","USUARIO.IDPERSONA","=","PERSONA.IDPERSONA")
		->join("LOGIN","LOGIN.IDUSUARIO","=","USUARIO.IDUSUARIO")
		->where("USUARIO.IDUSUARIO",$idusuario)
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

	public static function CambiarPassword($idusuario,$pass){
		Usuario::query()
		->where('idusuario',$idusuario)
		->update(["password"=>$pass]);
	}

	public static function UpdatePrimeraConexion($idusuario) {
		Usuario::query()
		->where('idusuario',$idusuario)
		->update(["primera_conexion"=>0]);
	}


	public static function listarUsers(){
		$result = array();
		$query = Usuario::query()
		->select()
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

	public static function ListarUsuarios($pag){
		$reg_por_pag = Constante::Item_Pag_20;
		$result = array();
		$campos = "usuario.idusuario, usuario.username, usuario.estado, usuario.baja, usuario.idempleado, empleados.cod_planilla, empleados.correo_institucional, empleados.foto, empleados.id_unidad_organica, empleados.idpersona, persona.nombres, persona.ape_paterno, persona.ape_materno, persona.tipo_doc, persona.num_doc";

		$query = Usuario::query()
		->selectRaw($campos)
		->join("empleados","empleados.idempleado","=","usuario.idempleado")
		->join("persona","persona.idpersona","=","empleados.idpersona")
		->offset(($pag-1)*$reg_por_pag)
		->limit($reg_por_pag)
		->get()
		->toArray();

		$query_count = Usuario::query()
		->selectRaw("COUNT(DISTINCT usuario.idusuario) as total_registros")
		->join("empleados","empleados.idempleado","=","usuario.idempleado")
		->join("persona","persona.idpersona","=","empleados.idpersona")
		->get()
		->toArray();

		if (!empty($query)) {
			$result["data"] = $query;
			$result["reg_por_pag"] = $reg_por_pag;
			$result["total_registros"] = $query_count[0]["total_registros"];
			$success = true;
			$message = "Los datos se listaron correctamente";
		} else {
			$result["total_registros"] = 0;
			$success = false;
			$message = "No existen resultados";
		}
		$result["success"] = $success;
		$result["message"] = $message;
		return $result;
	}

	public static function FiltrarUsuarios($pag,$params){
		$reg_por_pag = Constante::Item_Pag_20;
		$result = array();
		$campos = "usuario.idusuario, usuario.username, usuario.estado, usuario.baja, usuario.idempleado, empleados.cod_planilla, empleados.correo_institucional, empleados.foto, empleados.id_unidad_organica, empleados.idpersona, persona.nombres, persona.ape_paterno, persona.ape_materno, persona.tipo_doc, persona.num_doc";

		if ($params["id_unidad_organica"] != "") {
			$query = Usuario::query()
			->selectRaw($campos)
			->join("empleados","empleados.idempleado","=","usuario.idempleado")
			->join("persona","persona.idpersona","=","empleados.idpersona")
			->where("empleados.id_unidad_organica",$params["id_unidad_organica"])
			->where("usuario.username","like","%".$params["username"]."%")
			->where("empleados.cod_planilla","like","%".$params["cod_planilla"]."%")
			->where("persona.num_doc","like","%".$params["num_doc"]."%")
			->where("usuario.estado","like","%".$params["estado"]."%")
			->offset(($pag-1)*$reg_por_pag)
			->limit($reg_por_pag)
			->get()
			->toArray();

			$query_count = Usuario::query()
			->selectRaw("COUNT(DISTINCT usuario.idusuario) as total_registros")
			->join("empleados","empleados.idempleado","=","usuario.idempleado")
			->join("persona","persona.idpersona","=","empleados.idpersona")
			->where("empleados.id_unidad_organica",$params["id_unidad_organica"])
			->where("usuario.username","like","%".$params["username"]."%")
			->where("empleados.cod_planilla","like","%".$params["cod_planilla"]."%")
			->where("persona.num_doc","like","%".$params["num_doc"]."%")
			->where("usuario.estado","like","%".$params["estado"]."%")
			->get()
			->toArray();
		} else {
			$query = Usuario::query()
			->selectRaw($campos)
			->join("empleados","empleados.idempleado","=","usuario.idempleado")
			->join("persona","persona.idpersona","=","empleados.idpersona")
			->where("usuario.username","like","%".$params["username"]."%")
			->where("empleados.cod_planilla","like","%".$params["cod_planilla"]."%")
			->where("persona.num_doc","like","%".$params["num_doc"]."%")
			->where("usuario.estado","like","%".$params["estado"]."%")
			->offset(($pag-1)*$reg_por_pag)
			->limit($reg_por_pag)
			->get()
			->toArray();

			$query_count = Usuario::query()
			->selectRaw("COUNT(DISTINCT usuario.idusuario) as total_registros")
			->join("empleados","empleados.idempleado","=","usuario.idempleado")
			->join("persona","persona.idpersona","=","empleados.idpersona")
			->where("usuario.username","like","%".$params["username"]."%")
			->where("empleados.cod_planilla","like","%".$params["cod_planilla"]."%")
			->where("persona.num_doc","like","%".$params["num_doc"]."%")
			->where("usuario.estado","like","%".$params["estado"]."%")
			->get()
			->toArray();
		}

		if (!empty($query)) {
			$result["data"] = $query;
			$result["reg_por_pag"] = $reg_por_pag;
			$result["total_registros"] = $query_count[0]["total_registros"];
			$success = true;
			$message = "Los datos se listaron correctamente";
		} else {
			$result["total_registros"] = 0;
			$success = false;
			$message = "No existen resultados";
		}
		$result["success"] = $success;
		$result["message"] = $message;
		return $result;
	}

	public static function DatosUsuarioByUsername($username){
		$result = array();
		$query = Usuario::query()
		->selectRaw("USUARIO.*")
		//->join("empleados","empleados.idempleado","=","usuario.idempleado")
		//->join("unidad_organica","unidad_organica.id_unidad_organica","=","empleados.id_unidad_organica")
		->join("PERSONA","PERSONA.IDPERSONA","=","PERSONA.IDPERSONA")
		//->join("parametros","parametros.idparametro","=","persona.tipo_doc")
		->where("USUARIO.USUARIO",$username)
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

	public static function DatosUsuarioByIdEmpleado($idempleado){
		$result = array();
		$query = Usuario::query()
		->selectRaw("usuario.idusuario, usuario.username, usuario.password, usuario.primera_conexion, empleados.idempleado, empleados.cod_planilla, empleados.foto, empleados.correo_institucional, unidad_organica.id_unidad_organica, unidad_organica.nombre_unidad_organica, unidad_organica.siglas, persona.idpersona, persona.tipo_doc AS id_tipo_doc, parametros.nombre_parametro AS tipo_doc, persona.num_doc, persona.nombres, persona.ape_paterno, persona.ape_materno")
		->join("empleados","empleados.idempleado","=","usuario.idempleado")
		->join("unidad_organica","unidad_organica.id_unidad_organica","=","empleados.id_unidad_organica")
		->join("persona","persona.idpersona","=","empleados.idpersona")
		->join("parametros","parametros.idparametro","=","persona.tipo_doc")
		->where("usuario.idempleado",$idempleado)
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

	public static function DatosUsuarioById($idusuario){
		$result = array();
		$query = Usuario::query()
		->selectRaw("usuario.idusuario, usuario.username, usuario.password, usuario.primera_conexion, empleados.idempleado, empleados.cod_planilla, empleados.foto, empleados.correo_institucional, unidad_organica.id_unidad_organica, unidad_organica.nombre_unidad_organica, unidad_organica.siglas, persona.idpersona, persona.tipo_doc AS id_tipo_doc, parametros.nombre_parametro AS tipo_doc, persona.num_doc, persona.nombres, persona.ape_paterno, persona.ape_materno")
		->join("empleados","empleados.idempleado","=","usuario.idempleado")
		->join("unidad_organica","unidad_organica.id_unidad_organica","=","empleados.id_unidad_organica")
		->join("persona","persona.idpersona","=","empleados.idpersona")
		->join("parametros","parametros.idparametro","=","persona.tipo_doc")
		->where("usuario.idusuario",$idusuario)
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
		Usuario::insert($data);
	}

	public static function ActualizarRegistro($idusuario,$data){
		Usuario::query()
		->where(['idusuario'=>intval($idusuario)])
		->update($data);
	}
}
