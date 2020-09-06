<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;

class LogConexion extends Model{
	protected $table = 'LOG_CONEXION';
	public $timestamps = false;
	protected $primaryKey = 'IDLOGCONEXION';
	protected $fillable  = [
		'UUID',
		'PLATAFORMA',
		'MODELO',
		'VERSION',
		'MANUFACTURA',
		'SERIAL',
		'ESTADO'
    ];

		public static function ListarLogConexion(){
			$result = array();
			$query = LogConexion::query()
			->selectRaw("*")
			->orderBy('FECHA_CONEXION', 'DESC')
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
