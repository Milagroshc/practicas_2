<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;

class DocumentoRepresentaTerritorio extends Model{
	protected $table = 'documento_representa_territorio';
	public $timestamps = false;

	public static function ListarDocumento(){
		$result = array();
		$query = Documento::query()
				->selectRaw("*")
				->where("estado",1)
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

	public static function FiltrarDocumentoes($pag,$params){
		$reg_por_pag = Constante::Item_Pag_20;
		$result = array();
		
		$query = Documento::query()
		->selectRaw("*")
		->where("nombre_Documento","like","%".$params["nombre_Documento"]."%")
		->where("estado","like","%".$params["estado"]."%")
		->offset(($pag-1)*$reg_por_pag)
		->limit($reg_por_pag)
		->get()
		->toArray();

		$query_count = Documento::query()
		->selectRaw("COUNT(DISTINCT idDocumento) as total_registros")
		->where("nombre_Documento","like","%".$params["nombre_Documento"]."%")
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


	public static function ListarDocumentoByIdTematica($idTematica){
		$result = array();
		$query = Documento::query()
		->selectRaw("Documento.idDocumento,
		Documento.idTematica,
		Documento.nombre,
		Documento.descripcion,
		Documento.estado,
		Documento.unidad,
		Documento.prioridad,
		Documento.difundir,
		Documento.periodo_generacion,
		Documento.periodo_sinia,
		(select nombre_parametro from parametros where parametros.idparametro=Documento.periodo_generacion) as nombre_periodo_generacion,
		(select nombre_parametro from parametros where parametros.idparametro=Documento.periodo_sinia) as nombre_periodo_sinia")
		->join('parametros', 'Documento.periodo_generacion', '=', 'parametros.idparametro')
		->where("idTematica",$idTematica)
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

	public static function DatosDocumentoById($idDocumento){
		$result = array();
		$query = Documento::query()
		->selectRaw("*")
		->where("idDocumento",$idDocumento)
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

	public static function DatosDocumentoByNombre($nombre_Documento){
		$result = array();
		$query = Documento::query()
		->selectRaw("*")
		->where("nombre_Documento",$nombre_Documento)
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
		Documento::insert($data);
	}

	public static function ActualizarRegistro($idDocumento,$data){
		Documento::query()
		->where(['idDocumento'=>intval($idDocumento)])
		->update($data);
	}
}