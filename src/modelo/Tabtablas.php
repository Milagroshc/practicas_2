<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;



class Tabtablas extends Model{
	protected $table = 'TAB_TABLAS';
	public $timestamps = false;
	protected $primaryKey = 'IDTABLA';
	//protected $guarded = ['IDMODULOS', 'IDSISTEMA'];

	public static function catalogotabla()
    {
        return (new Tabtablas())->belongsTo(new Tabtablas, "IDCATALOGOTABLAS", "IDCATALOGOTABLAS");
	}

	public static function getTablaTablasByIDS($dato){
		$result = array();
		$estado = ["TAB_TABLAS.ESTADO"=>"A", "TAB_TABLAS.ESTADO"=>"A"];
		$query = Tabtablas::query()
		->selectRaw("*")
		->join("TAB_TABLAS", "TAB_TABLAS.IDCATALOGOTABLAS", "=", "TAB_TABLAS.IDCATALOGOTABLAS")
		->where($estado)
		->whereIn("TAB_TABLAS.IDCATALOGOTABLAS", $dato)
		->with('catalogotabla')
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

	public static function getTablaTablas(){
		$result = array();
		$estado = ["TAB_TABLAS.ESTADO"=>Constante::ESTADO_ACTIVO];
		$query = Tabtablas::query()
		->selectRaw("TAB_TABLAS.*")
		->where($estado)
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

	



	public static function getTableColumns() {
        return (new Tabtablas)
            ->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing((new Tabtablas)->getTable());
	}
	
	public static function getTableFilas($idCatalogoTablas) {
		$result = array();
		$datos = ["IDCATALOGOTABLAS"=>$idCatalogoTablas];
		$query = Tabtablas::query()
		->selectRaw("TAB_TABLAS.*,TAB_TABLAS.IDCATALOGOTABLAS AS ACCION")
		->where($datos)
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