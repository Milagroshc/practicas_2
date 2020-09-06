<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;

class Entidad extends Model{
	protected $table = 'entidad';
	public $timestamps = false;



	public static function getEntidadByIdParametro($idParametro){
		$result = array();
		$query = Entidad::query()
		->selectRaw("entidad.*")
		->where( 'entidad.idFuenteInformacion',$idParametro)
		->orderBy('Entidad.nombre', 'ASC')
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

	public static function getContactosByEntidad($idParametro){
		$result = array();
		$query = Entidad::query()
		->selectRaw("empleados.idempleado,
		persona.nombres,
		persona.ape_paterno,
		persona.ape_materno,
		entidad.nombre")
		->join('unidad_organica', 'unidad_organica.idEntidad', '=', 'entidad.idEntidad')
		->join('empleados', 'empleados.id_unidad_organica', '=', 'unidad_organica.id_unidad_organica')
		->join('persona', 'empleados.idpersona', '=', 'persona.idpersona')
		->where( 'entidad.idEntidad',$idParametro)
		->orderBy('persona.nombres', 'ASC')
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

	public static function ListarEntidadAll(){
		$result = array();
		$query = Entidad::query()
				->selectRaw("Entidad.*,docadjunto.urlAdjunto")
				->join('docadjunto', 'Entidad.idDocAdjunto', '=', 'docadjunto.idDocAdjunto')
				->orderBy('orden', 'ASC')
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

	public static function FiltrarEntidades($pag,$params){
		$reg_por_pag = Constante::Item_Pag_20;
		$result = array();
		
		$query = Entidad::query()
		->selectRaw("*")
		->where("nombre_Entidad","like","%".$params["nombre_Entidad"]."%")
		->where("estado","like","%".$params["estado"]."%")
		->offset(($pag-1)*$reg_por_pag)
		->limit($reg_por_pag)
		->get()
		->toArray();

		$query_count = Entidad::query()
		->selectRaw("COUNT(DISTINCT idEntidad) as total_registros")
		->where("nombre_Entidad","like","%".$params["nombre_Entidad"]."%")
		->where("estado","like","%".$params["estado"]."%")
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


	public static function getEntidadByUrl($url){
		$result = array();

		$query = Entidad::query()
		->selectRaw("Entidad.*,docadjunto.urlAdjunto")
		->join('docadjunto', 'Entidad.idDocAdjunto', '=', 'docadjunto.idDocAdjunto')
		->where("url",$url)
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

	public static function DatosEntidadById($idEntidad){
		$result = array();
		$query = Entidad::query()
		->selectRaw("*")
		->where("idEntidad",$idEntidad)
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

	public static function DatosEntidadByNombre($nombre_Entidad){
		$result = array();
		$query = Entidad::query()
		->selectRaw("*")
		->where("nombre_Entidad",$nombre_Entidad)
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
		Entidad::insert($data);
	}

	public static function ActualizarRegistro($idEntidad,$data){
		Entidad::query()
		->where(['idEntidad'=>intval($idEntidad)])
		->update($data);
	}
}