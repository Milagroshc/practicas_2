<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;

class ContenidoArchivo extends Model{
	protected $table = 'contenido_archivo';
	public $timestamps = false;

	public static function ArchivosByIdContenido($idcontenido){
		$result = array();
		$query = ContenidoArchivo::query()
		->selectRaw("contenido_archivo.idcontenido, contenido.url, contenido.titulo, contenido_archivo.idarchivo, archivos.nombre_archivo, archivos.formato_archivo, archivos.directorio, archivos.nombre_encriptado, contenido_archivo.portada, contenido_archivo.estado")
		->join("contenido","contenido.idcontenido","=","contenido_archivo.idcontenido")
		->join("archivos","archivos.idarchivo","=","contenido_archivo.idarchivo")
		->where("contenido_archivo.idcontenido",$idcontenido)
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

	public static function ActualizarRegistro($idcontenido,$idarchivo,$data){
		ContenidoArchivo::query()
		->where(['idcontenido'=>intval($idcontenido)])
		->where(['idarchivo'=>intval($idarchivo)])
		->update($data);
	}

	public static function RetirarPortadaContenido($idcontenido){
		ContenidoArchivo::query()
		->where(['idcontenido'=>intval($idcontenido)])
		->update(["portada"=>0]);
	}

	public static function NuevoRegistro($data){
		ContenidoArchivo::insert($data);
	}
}