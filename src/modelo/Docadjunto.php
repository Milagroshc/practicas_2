<?php
namespace App\Modelo;

use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;
/**
*
*/
class Docadjunto extends Model
{
	protected $table = 'DOCADJUNTO';
	public $timestamps = false; /*para que no aparesca el time error*/
	//protected $fillable = ['N_ID_CONTENIDO','C_NOMBRE','N_ID_SECCION','C_CONTENIDO'];


public static function getFotosApp($idFotos){
  $result = array();
  $query = Docadjunto::query()
  ->selectRaw("*")
  ->whereIn("idDocAdjunto",$idFotos)
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
