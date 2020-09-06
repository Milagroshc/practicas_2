<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;
use Illuminate\Database\Capsule\Manager as DB;


class Contenido extends Model{
	protected $table = 'contenido';
	public $timestamps = false;

	public static function ListarRutasContenido(){

		$result = array();

		$query = Contenido::query()
		->selectRaw("contenido.*")
		->get()
		->toArray();


		if (!empty($query)) {
			$result["data"] = $query;
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


	public static function ListarContenido($pag){
		$reg_por_pag = Constante::Item_Pag_10;
		$result = array();


		$query = Contenido::query()
		->selectRaw("contenido.idcontenido, item_modulos.id_item_modulo, item_modulos.nombre_item, contenido.url, contenido.titulo, contenido.resumen, contenido.contenido, contenido.estado_contenido, parametros.nombre_parametro, contenido.fecha_contenido")
		->leftjoin("item_modulos","item_modulos.id_item_modulo","=","contenido.id_item_modulo")
		->join("parametros","parametros.idparametro","=","contenido.estado_contenido")
		->whereNull('contenido.idpadre')
		->offset(($pag-1)*$reg_por_pag)
		->limit($reg_por_pag)
		->get()
		->toArray();

		$query_count = Contenido::query()
		->selectRaw("COUNT(DISTINCT contenido.idcontenido) as total_registros")
		->leftjoin("item_modulos","item_modulos.id_item_modulo","=","contenido.id_item_modulo")
		->join("parametros","parametros.idparametro","=","contenido.estado_contenido")
		->whereNull('contenido.idpadre')
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

	public static function FiltrarContenido($pag,$params){
		$reg_por_pag = Constante::Item_Pag_10;
		$result = array();
		$campos = "contenido.idcontenido, item_modulos.id_item_modulo, item_modulos.nombre_item, contenido.url, contenido.titulo, contenido.resumen, contenido.contenido, contenido.estado_contenido, parametros.nombre_parametro, contenido.fecha_contenido";
		$campos_count = "COUNT(DISTINCT contenido.idcontenido) as total_registros";
		
		if ($params["id_item_modulo"] == "") {
			$query = Contenido::query()
			->selectRaw($campos)
			->leftjoin("item_modulos","item_modulos.id_item_modulo","=","contenido.id_item_modulo")
			->join("parametros","parametros.idparametro","=","contenido.estado_contenido")
			->where("contenido.url","like","%".$params["url"]."%")
			->where("contenido.titulo","like","%".$params["titulo"]."%")
			->where("contenido.estado_contenido","like","%".$params["estado_contenido"]."%")
			->where("contenido.fecha_contenido","like","%".$params["fecha_contenido"]."%")
			->offset(($pag-1)*$reg_por_pag)
			->limit($reg_por_pag)
			->get()
			->toArray();

			$query_count = Contenido::query()
			->selectRaw($campos_count)
			->leftjoin("item_modulos","item_modulos.id_item_modulo","=","contenido.id_item_modulo")
			->join("parametros","parametros.idparametro","=","contenido.estado_contenido")
			->where("contenido.url","like","%".$params["url"]."%")
			->where("contenido.titulo","like","%".$params["titulo"]."%")
			->where("contenido.estado_contenido","like","%".$params["estado_contenido"]."%")
			->where("contenido.fecha_contenido","like","%".$params["fecha_contenido"]."%")
			->get()
			->toArray();
		} else {
			$query = Contenido::query()
			->selectRaw($campos)
			->leftjoin("item_modulos","item_modulos.id_item_modulo","=","contenido.id_item_modulo")
			->join("parametros","parametros.idparametro","=","contenido.estado_contenido")
			->where("contenido.id_item_modulo","like","%".$params["id_item_modulo"]."%")
			->where("contenido.url","like","%".$params["url"]."%")
			->where("contenido.titulo","like","%".$params["titulo"]."%")
			->where("contenido.estado_contenido","like","%".$params["estado_contenido"]."%")
			->where("contenido.fecha_contenido","like","%".$params["fecha_contenido"]."%")
			->offset(($pag-1)*$reg_por_pag)
			->limit($reg_por_pag)
			->get()
			->toArray();

			$query_count = Contenido::query()
			->selectRaw($campos_count)
			->leftjoin("item_modulos","item_modulos.id_item_modulo","=","contenido.id_item_modulo")
			->join("parametros","parametros.idparametro","=","contenido.estado_contenido")
			->where("contenido.id_item_modulo","like","%".$params["id_item_modulo"]."%")
			->where("contenido.url","like","%".$params["url"]."%")
			->where("contenido.titulo","like","%".$params["titulo"]."%")
			->where("contenido.estado_contenido","like","%".$params["estado_contenido"]."%")
			->where("contenido.fecha_contenido","like","%".$params["fecha_contenido"]."%")
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

	public static function ListarContenidoHijo($pag,$idpadre){
		$reg_por_pag = Constante::Item_Pag_10;
		$result = array();

		$query = Contenido::query()
		->selectRaw("contenido.idcontenido, item_modulos.id_item_modulo, item_modulos.nombre_item, contenido.url, contenido.titulo, contenido.resumen, contenido.contenido, contenido.estado_contenido, parametros.nombre_parametro, contenido.fecha_contenido")
		->leftjoin("item_modulos","item_modulos.id_item_modulo","=","contenido.id_item_modulo")
		->join("parametros","parametros.idparametro","=","contenido.estado_contenido")
		->where("contenido.idpadre",$idpadre)
		->offset(($pag-1)*$reg_por_pag)
		->limit($reg_por_pag)
		->orderBy("contenido.fecha_registro","DESC")
		->get()
		->toArray();

		$query_count = Contenido::query()
		->selectRaw("COUNT(DISTINCT contenido.idcontenido) as total_registros")
		->leftjoin("item_modulos","item_modulos.id_item_modulo","=","contenido.id_item_modulo")
		->join("parametros","parametros.idparametro","=","contenido.estado_contenido")
		->where("contenido.idpadre",$idpadre)
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
			$message = "No existen resultados de contenidos hijos";
		}
		$result["success"] = $success;
		$result["message"] = $message;
		return $result;
	}



	public static function crearMenu($idpadre){
		$result = array();
		$query = Contenido::query()
		->selectRaw("contenido.idcontenido, contenido.idpadre, item_modulos.id_item_modulo, item_modulos.nombre_item, contenido.url, contenido.titulo, contenido.resumen, contenido.contenido, contenido.estado_contenido, parametros.nombre_parametro, contenido.fecha_contenido")
		->leftjoin("item_modulos","item_modulos.id_item_modulo","=","contenido.id_item_modulo")
		->join("parametros","parametros.idparametro","=","contenido.estado_contenido")
		->where("contenido.idpadre",$idpadre)
		->get()
		->toArray();
		if (!empty($query)) {
			$result["data"] = $query;
			$success = true;
			$message = "Los datos se listaron correctamente";
		} else {
			$success = false;
			$message = "No existen resultados de contenidos hijos";
		}
		$result["success"] = $success;
		$result["message"] = $message;
		return $result;
	}


	public static function getIdContenidoPadreRama($idcontenido)
	{
		$ancestors = Contenido::query()
		->where('idcontenido', '=', $idcontenido)
		->get();

		while ($ancestors->last()->idpadre !== null)
		{
		  $parent = Contenido::query()
		  ->where('idcontenido', '=', $ancestors->last()->idpadre)
		  ->get();
		  

		  $ancestors = $ancestors->merge($parent);
		}
		
	
		return $ancestors;
	}


	public static function menus()
    {

		return (new Contenido())->hasMany(new Contenido(),"idpadre","idcontenido")->with("hijosMenus");

        //return Contenido::hasMany(Contenido::class);
	}
	public static function hijosMenus()
	{
		return (new Contenido())->hasMany(new Contenido(),"idpadre","idcontenido")->select(["idcontenido","url","idpadre", "titulo"])->with("hijosMenus");
	}

	public static function menuTotal() {
		$result = array();
		$query = Contenido::query()
        ->selectRaw("idcontenido,url,idpadre")
        ->whereNull('idpadre')
        ->with('hijosMenus')
        ->get();
		if (!empty($query)) {
			$result["data"] = $query;
			$success = true;
			$message = "Los datos se listaron correctamente";
		} else {
			$success = false;
			$message = "No existen resultados de contenidos hijos";
		}
		$result["success"] = $success;
		$result["message"] = $message;
		return $result;
	}

	public static function menuContenido($idContenido) {
		$result = array();
		$query = Contenido::query()
        ->selectRaw("idcontenido,url,idpadre,titulo")
        ->where('idpadre',$idContenido)
        ->with('hijosMenus')
		->get()
		->toArray();
		if (!empty($query)) {
			$result["data"] = $query;
			$success = true;
			$message = "Los datos se listaron correctamente";
		} else {
			$success = false;
			$message = "No existen resultados de contenidos hijos";
		}
		$result["success"] = $success;
		$result["message"] = $message;
		return $result;
	}
	







	public static function FiltrarContenidoHijo($pag,$params){
		$reg_por_pag = Constante::Item_Pag_10;
		$result = array();
		$campos = "contenido.idcontenido, item_modulos.id_item_modulo, item_modulos.nombre_item, contenido.url, contenido.titulo, contenido.resumen, contenido.contenido, contenido.estado_contenido, parametros.nombre_parametro, contenido.fecha_contenido";
		$campos_count = "COUNT(DISTINCT contenido.idcontenido) as total_registros";

		if ($params["id_item_modulo"] == "") {
			$query = Contenido::query()
			->selectRaw($campos)
			->leftjoin("item_modulos","item_modulos.id_item_modulo","=","contenido.id_item_modulo")
			->join("parametros","parametros.idparametro","=","contenido.estado_contenido")
			->where("contenido.idpadre",$params["idpadre"])
			->where("contenido.url","like","%".$params["url"]."%")
			->where("contenido.titulo","like","%".$params["titulo"]."%")
			->where("contenido.estado_contenido","like","%".$params["estado_contenido"]."%")
			->where("contenido.fecha_contenido","like","%".$params["fecha_contenido"]."%")
			->offset(($pag-1)*$reg_por_pag)
			->limit($reg_por_pag)
			->get()
			->toArray();

			$query_count = Contenido::query()
			->selectRaw($campos_count)
			->leftjoin("item_modulos","item_modulos.id_item_modulo","=","contenido.id_item_modulo")
			->join("parametros","parametros.idparametro","=","contenido.estado_contenido")
			->where("contenido.idpadre",$params["idpadre"])
			->where("contenido.url","like","%".$params["url"]."%")
			->where("contenido.titulo","like","%".$params["titulo"]."%")
			->where("contenido.estado_contenido","like","%".$params["estado_contenido"]."%")
			->where("contenido.fecha_contenido","like","%".$params["fecha_contenido"]."%")
			->get()
			->toArray();
		} else {
			$query = Contenido::query()
			->selectRaw($campos)
			->leftjoin("item_modulos","item_modulos.id_item_modulo","=","contenido.id_item_modulo")
			->join("parametros","parametros.idparametro","=","contenido.estado_contenido")
			->where("contenido.id_item_modulo",$params["id_item_modulo"])
			->where("contenido.idpadre",$params["idpadre"])
			->where("contenido.url","like","%".$params["url"]."%")
			->where("contenido.titulo","like","%".$params["titulo"]."%")
			->where("contenido.estado_contenido","like","%".$params["estado_contenido"]."%")
			->where("contenido.fecha_contenido","like","%".$params["fecha_contenido"]."%")
			->offset(($pag-1)*$reg_por_pag)
			->limit($reg_por_pag)
			->get()
			->toArray();

			$query_count = Contenido::query()
			->selectRaw($campos_count)
			->leftjoin("item_modulos","item_modulos.id_item_modulo","=","contenido.id_item_modulo")
			->join("parametros","parametros.idparametro","=","contenido.estado_contenido")
			->where("contenido.id_item_modulo",$params["id_item_modulo"])
			->where("contenido.idpadre",$params["idpadre"])
			->where("contenido.url","like","%".$params["url"]."%")
			->where("contenido.titulo","like","%".$params["titulo"]."%")
			->where("contenido.estado_contenido","like","%".$params["estado_contenido"]."%")
			->where("contenido.fecha_contenido","like","%".$params["fecha_contenido"]."%")
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

	public static function DatosByUrl($url){

		$result = array();
		$query = Contenido::query()
		->selectRaw("*")
		->where("url",$url)
		->get()
		->toArray();

		$queryMenu = Contenido::query()
		->selectRaw("*")
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

	
	public static function BannerPublicitarios(){
	
		$result = array();
		$query = Contenido::query()
		->selectRaw("contenido.*")
		->where("idPadre", Constante::IDPADRE_BANNER)
		->where("estado_contenido", Constante::ESTADO_BANNER_PUBLICADO)
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

	public static function contenidoHijos($idpadre){
	
		$result = array();
		$query = Contenido::query()
		->selectRaw("contenido.*")
		->where("idPadre", $idpadre)
		->where("estado_contenido", Constante::ESTADO_PUBLICADO)
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

	public static function getPopUp($idpadre){
	
		$result = array();
		$query = Contenido::query()
		->selectRaw("contenido.icono as src, contenido.resumen as type")
		->where("idPadre", $idpadre)
		->where("estado_contenido", Constante::ESTADO_PUBLICADO)
		->orderBy("contenido.fecha_contenido","DESC")
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

	public static function seccionHome($idContenido){
	
		$result = array();
		$query = Contenido::query()
		->selectRaw("contenido.*")
		->where("idcontenido", $idContenido)
		->where("estado_contenido", Constante::ESTADO_PUBLICADO)
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



	public static function NoticiasByArgs($args){

		$result = array();
		$query = Contenido::query()
		->selectRaw("*")
		->whereMonth("fecha_registro",$args["mes"])
		->whereYear("fecha_registro",$args["anio"])
		->where("idPadre", Constante::IDPADRE_NOTICIAS)
		->where("estado_contenido",6)
		->orderBy("contenido.fecha_contenido","DESC")
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

	public static function anioNoticias(){

		$result = array();
		$query = Contenido::query()
		->selectRaw("YEAR(contenido.fecha_registro) as anio")
		->selectRaw("MAX(MONTH(contenido.fecha_registro)) as mes")
		//->whereMonth("fecha_registro",$args["mes"])
		//->whereYear("fecha_registro",$args["anio"])
		->where("idPadre", Constante::IDPADRE_NOTICIAS)
		->groupBy("anio")
		->orderBy("anio","DESC")
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


	public static function NoticiasByMesAnio($param){
		//armar  la fecha
		$anio=$param["anio"];
		$mes = $param["mes"];
		$dia = "01";
		$fecha=$anio."-".$mes."-".$dia;
		if ($param["primera"]==true) {
			$w = "contenido.fecha_registro >= date_sub('".$fecha."', interval 5 month)";
		}else{
			$w = "YEAR(contenido.fecha_registro) = '".$anio."' and "." MONTH(contenido.fecha_registro) = '".$mes."'";
		}
		$result = array();
		$query = Contenido::query()
		->selectRaw("contenido.*, 
		contenido.icono as urlPortada
		")
		->whereRaw($w)
		->where("idPadre", Constante::IDPADRE_NOTICIAS)
		//->groupBy(DB::raw('YEAR(contenido.fecha_registro)') ,DB::raw('MONTH(contenido.fecha_registro)'))
		->where("estado_contenido",6)
		->orderBy("contenido.fecha_contenido","DESC")
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
	

	public static function NoticiasByMesAnioUltimas(){
		//armar  la fecha

		$anio=date("Y");;
		$mes =date("m");;
		$dia = "01";
		$fecha=$anio."-".$mes."-".$dia;
		$w = "contenido.fecha_registro >= date_sub('".$fecha."', interval 5 month)";

		$result = array();
		$query = Contenido::query()
		->selectRaw("contenido.*, 
		(SELECT CONCAT(directorio,nombre_encriptado) FROM contenido_archivo INNER JOIN archivos ON contenido_archivo.idarchivo = archivos.idarchivo WHERE contenido_archivo.idcontenido=contenido.idcontenido and contenido_archivo.portada=1) as urlPortada
		")
		->whereRaw($w)
		->where("idPadre", Constante::IDPADRE_NOTICIAS)
		//->groupBy(DB::raw('YEAR(contenido.fecha_registro)') ,DB::raw('MONTH(contenido.fecha_registro)'))
		->limit(3)
		->where("estado_contenido",6)
		->orderBy("contenido.fecha_contenido","DESC")
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




	public static function NoticiasByAnio($anio){

		$result = array();
		$query = Contenido::query()
		->selectRaw("
			YEAR(contenido.fecha_registro) as anio,
			MONTH(contenido.fecha_registro) as mes,
			count(MONTH(contenido.fecha_registro)) as cantidad
		")
		->whereYear("fecha_registro",$anio)
		->where("idPadre", Constante::IDPADRE_NOTICIAS)
		->where("estado_contenido",6)
		->groupBy(DB::raw('YEAR(contenido.fecha_registro)') ,DB::raw('MONTH(contenido.fecha_registro)'))
		->orderBy("mes","DESC")
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

	public static function DatosById($idcontenido){
		$result = array();
		$query = Contenido::query()
		->selectRaw("*")
		->where("idcontenido",$idcontenido)
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
		Contenido::insert($data);
	}

	public static function ActualizarRegistro($idcontenido,$data){
		Contenido::query()
		->where(['idcontenido'=>intval($idcontenido)])
		->update($data);
	}
	
	public static function ContenidoBySeccion($idseccion){
		$result = array();
		$query = Contenido::query()
		->selectRaw("contenido.idcontenido, item_modulos.id_item_modulo, item_modulos.nombre_item, contenido.url, contenido.titulo, contenido.resumen, contenido.contenido, contenido.estado_contenido, parametros.nombre_parametro, contenido.fecha_contenido, (SELECT CONCAT(archivos.directorio,'',archivos.nombre_encriptado) FROM contenido_archivo INNER JOIN archivos ON archivos.idarchivo = contenido_archivo.idarchivo WHERE contenido_archivo.idcontenido = contenido.idcontenido AND contenido_archivo.portada = 1) as foto_portada")
		->leftjoin("item_modulos","item_modulos.id_item_modulo","=","contenido.id_item_modulo")
		->join("parametros","parametros.idparametro","=","contenido.estado_contenido")
		->where("contenido.id_item_modulo",$idseccion)
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