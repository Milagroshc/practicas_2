<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;
use App\Helper\Acl;
use Illuminate\Database\Capsule\Manager as DB;

class Documento extends Model{
	protected $table = 'documento';
	public $timestamps = false;

	public static function anioDocumentos($tipoDocumento){

		$result = array();
		$query = Documento::query()
		->selectRaw("DISTINCT YEAR(documento.fecha_registro) as anio")
		->join('parametros', 'documento.tipoDocumento', '=', 'parametros.idparametro')
		->join('catalogo_parametros', 'parametros.idcatalogo', '=', 'catalogo_parametros.idcatalogo')
		//->whereYear("documento.fecha_registro",$args["anio"])
		->where("catalogo_parametros.idcatalogo", $tipoDocumento)
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

	public static function DocumentosMenuLateral($args){


 		if(!empty($args["search"])){
			
			$fecha = Acl::fechaFormato($args["search"]["fecha_publicacion"]);
			$datos= $args["search"];
			$args = Array("anio"=>$fecha["year"], "tipoDocumento" => $datos["tipoDocumento"]);
			//$w = "YEAR(documento.fecha_registros) = '".$fecha["year"]."' and "." catalogo_parametros.idcatalogo = ".$args["search"]["tipoDocumento"];
		}else{
			//$w = "YEAR(documento.fecha_registro) = '".$fecha["year"]."' and "." catalogo_parametros.idcatalogo = ".$args["search"]["tipoDocumento"];
		} 

		$result = array();
		$query = Documento::query()
		->selectRaw("
			YEAR(documento.fecha_registro) as anio,
			MONTH(documento.fecha_registro) as mes,
			count(MONTH(documento.fecha_registro)) as cantidad
		")
		->join('parametros', 'documento.tipoDocumento', '=', 'parametros.idparametro')
		->join('catalogo_parametros', 'parametros.idcatalogo', '=', 'catalogo_parametros.idcatalogo')
		->whereYear("documento.fecha_registro",$args["anio"])
		->where("catalogo_parametros.idcatalogo", $args["tipoDocumento"])
		//->whereRaw($w)
		->groupBy(DB::raw('YEAR(documento.fecha_registro)') ,DB::raw('MONTH(documento.fecha_registro)'))
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



	public static function DocumentosByArgs($args){

		$result = array();
		$query = Documento::query();
		$query= $query->selectRaw("
documento.*,
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.tipoDocumento) as 'TipoDoc', 
catalogo_parametros.idcatalogo,
catalogo_parametros.nombre_catalogo,
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.ambitoAplicacion) as 'Ambito', 
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.representacionTerritorial) as 'Representacion', 
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.fuenteInformacion) as 'Fuente', 
(SELECT entidad.idEntidad FROM documento d INNER JOIN entidad_documento ON entidad_documento.idDocumento = d.idDocumento INNER JOIN entidad ON entidad_documento.idEntidad = entidad.idEntidad where entidad_documento.idDocumento = documento.idDocumento) as 'Entidad',
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.descriptorTematico) as 'Descriptor', 
(SELECT urlAdjunto FROM docadjunto WHERE docadjunto.idDocAdjunto = documento.idDocAdjunto) as urlDocAdjunto,
(SELECT urlAdjunto FROM docadjunto WHERE docadjunto.idDocAdjunto = documento.caratula) as urlDocCaratula,
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.nodo) as 'NombreNodo', 
(SELECT persona.nombres FROM empleados INNER JOIN persona ON empleados.idpersona = persona.idpersona where empleados.idempleado = documento.idEmpleado) as 'Empleado', 
(SELECT persona.nombres FROM empleados INNER JOIN persona ON empleados.idpersona = persona.idpersona where empleados.idempleado = documento.idContacto) as 'Contacto'
");
$query= $query->join('parametros', 'documento.tipoDocumento', '=', 'parametros.idparametro');
$query= $query->join('catalogo_parametros', 'parametros.idcatalogo', '=', 'catalogo_parametros.idcatalogo');

$query= $query->where("catalogo_parametros.idcatalogo", intval($args["tipoDocumento"]));

if(!empty($args["search"])){
	$fecha = Acl::fechaFormatoString($args["search"]["fecha_publicacion"]);
	$datos= $args["search"];
	if(!empty($args["search"]["fecha_publicacion"])){
		$query= $query->orWhereDate("documento.fecha_registro", $fecha);
		
	}
	if(!empty($args["search"]["titulo"])){
		$query= $query->orWhere('documento.titulo', 'like', '%' . $datos["titulo"] . '%');
	}
	 
}else{
	$query= $query->whereMonth("documento.fecha_registro",$args["mes"]);
	$query= $query->whereYear("documento.fecha_registro",$args["anio"]);
	$query= $query->where("catalogo_parametros.idcatalogo", $args["tipoDocumento"]);
}
$query= $query->get();
$query= $query->toArray();


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
		->selectRaw("documento.idDocumento,
		documento.idTematica,
		documento.nombre,
		documento.descripcion,
		documento.estado,
		documento.unidad,
		documento.prioridad,
		documento.difundir,
		documento.periodo_generacion,
		documento.periodo_sinia,
		(select nombre_parametro from parametros where parametros.idparametro=documento.periodo_generacion) as nombre_periodo_generacion,
		(select nombre_parametro from parametros where parametros.idparametro=documento.periodo_sinia) as nombre_periodo_sinia")
		->join('parametros', 'documento.periodo_generacion', '=', 'parametros.idparametro')
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
		->selectRaw("
documento.*,
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.tipoDocumento) as 'TipoDoc', 
catalogo_parametros.idcatalogo,
catalogo_parametros.nombre_catalogo,
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.ambitoAplicacion) as 'Ambito', 
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.representacionTerritorial) as 'Representacion', 
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.fuenteInformacion) as 'Fuente', 
(SELECT entidad.idEntidad FROM documento d INNER JOIN entidad_documento ON entidad_documento.idDocumento = d.idDocumento INNER JOIN entidad ON entidad_documento.idEntidad = entidad.idEntidad where entidad_documento.idDocumento = documento.idDocumento) as 'Entidad',
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.descriptorTematico) as 'Descriptor', 
(SELECT urlAdjunto FROM docadjunto WHERE docadjunto.idDocAdjunto = documento.idDocAdjunto) as urlDocAdjunto,
(SELECT urlAdjunto FROM docadjunto WHERE docadjunto.idDocAdjunto = documento.caratula) as urlDocCaratula,
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.nodo) as 'NombreNodo', 
(SELECT persona.nombres FROM empleados INNER JOIN persona ON empleados.idpersona = persona.idpersona where empleados.idempleado = documento.idEmpleado) as 'Empleado', 
(SELECT persona.nombres FROM empleados INNER JOIN persona ON empleados.idpersona = persona.idpersona where empleados.idempleado = documento.idContacto) as 'Contacto'
")
		->join('parametros', 'documento.tipoDocumento', '=', 'parametros.idparametro')
		->join('catalogo_parametros', 'parametros.idcatalogo', '=', 'catalogo_parametros.idcatalogo')
		->where("documento.idDocumento",$idDocumento)
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

	public static function listaDocumentos(){
		$result = array();
		$query = Documento::query()
		->selectRaw("
documento.idDocumento,
documento.tipoDocumento,
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.tipoDocumento) as 'TipoDoc', 
catalogo_parametros.idcatalogo,
catalogo_parametros.nombre_catalogo,
documento.titulo,
documento.numero,
documento.ambitoAplicacion,
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.ambitoAplicacion) as 'Ambito', 
documento.descripcion,
documento.fecha_publicacion,
documento.fecha_registro,
documento.representacionTerritorial,
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.representacionTerritorial) as 'Representacion', 
documento.fuenteInformacion,
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.fuenteInformacion) as 'Fuente', 
-- (SELECT entidad.nombre FROM documento INNER JOIN entidad_documento ON entidad_documento.idDocumento = documento.idDocumento INNER JOIN entidad ON entidad_documento.idEntidad = entidad.idEntidad where entidad_documento.idDocumento = documento.idDocumento) as 'Entidad',
documento.estado,
documento.descriptorTematico,
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.descriptorTematico) as 'Descriptor', 
documento.idDocAdjunto,
(SELECT urlAdjunto FROM docadjunto WHERE docadjunto.idDocAdjunto = documento.idDocAdjunto) as urlDocAdjunto,
documento.caratula,
(SELECT urlAdjunto FROM docadjunto WHERE docadjunto.idDocAdjunto = documento.caratula) as urlDocCaratula,
documento.nodo,
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.nodo) as 'NombreNodo', 
documento.idEmpleado,
(SELECT persona.nombres FROM empleados INNER JOIN persona ON empleados.idpersona = persona.idpersona where empleados.idempleado = documento.idEmpleado) as 'Empleado', 
documento.url_audiovisual,
documento.numero_pagina,
documento.idContacto, 
(SELECT persona.nombres FROM empleados INNER JOIN persona ON empleados.idpersona = persona.idpersona where empleados.idempleado = documento.idContacto) as 'Contacto'
")
		->join('parametros', 'documento.tipoDocumento', '=', 'parametros.idparametro')
		->join('catalogo_parametros', 'parametros.idcatalogo', '=', 'catalogo_parametros.idcatalogo')
		//->where("catalogo_parametros.idcatalogo",5)
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

	public static function listaDocumentosByTipo($idcatalogo){
		$result = array();
		$query = Documento::query()
		->selectRaw("
documento.idDocumento,
documento.tipoDocumento,
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.tipoDocumento) as 'TipoDoc', 
catalogo_parametros.idcatalogo,
catalogo_parametros.nombre_catalogo,
documento.titulo,
documento.numero,
documento.ambitoAplicacion,
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.ambitoAplicacion) as 'Ambito', 
documento.descripcion,
documento.fecha_publicacion,
documento.fecha_registro,
documento.representacionTerritorial,
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.representacionTerritorial) as 'Representacion', 
documento.fuenteInformacion,
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.fuenteInformacion) as 'Fuente', 
-- (SELECT entidad.nombre FROM documento INNER JOIN entidad_documento ON entidad_documento.idDocumento = documento.idDocumento INNER JOIN entidad ON entidad_documento.idEntidad = entidad.idEntidad where entidad_documento.idDocumento = documento.idDocumento) as 'Entidad',
documento.estado,
documento.descriptorTematico,
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.descriptorTematico) as 'Descriptor', 
documento.idDocAdjunto,
(SELECT urlAdjunto FROM docadjunto WHERE docadjunto.idDocAdjunto = documento.idDocAdjunto) as urlDocAdjunto,
documento.caratula,
(SELECT urlAdjunto FROM docadjunto WHERE docadjunto.idDocAdjunto = documento.caratula) as urlDocCaratula,
documento.nodo,
(SELECT parametros.nombre_parametro FROM parametros where parametros.idparametro = documento.nodo) as 'NombreNodo', 
documento.idEmpleado,
(SELECT persona.nombres FROM empleados INNER JOIN persona ON empleados.idpersona = persona.idpersona where empleados.idempleado = documento.idEmpleado) as 'Empleado', 
documento.url_audiovisual,
documento.numero_pagina,
documento.idContacto, 
(SELECT persona.nombres FROM empleados INNER JOIN persona ON empleados.idpersona = persona.idpersona where empleados.idempleado = documento.idContacto) as 'Contacto'
")
		->join('parametros', 'documento.tipoDocumento', '=', 'parametros.idparametro')
		->join('catalogo_parametros', 'parametros.idcatalogo', '=', 'catalogo_parametros.idcatalogo')
		->where("catalogo_parametros.idcatalogo",$idcatalogo)
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