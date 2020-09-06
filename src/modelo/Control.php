<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;
use App\Modelo\Sistema;



class Control extends Model{
	protected $table = 'CONTROL';
	public $timestamps = false;
	protected $primaryKey = 'IDCONTROL';
	protected $fillable  = [
		'PUNTOCONTROL',
		'RESPONSABLE',
		'FECHA',
		'OBSERVACION',
		'ESTADO',
		'FOTOS',
    ];

	public static function ListarControl(){
		$result = array();
		$query = Control::query()
		->selectRaw("*")
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
