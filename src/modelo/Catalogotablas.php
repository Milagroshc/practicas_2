<?php
namespace App\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Helper\Constante;




class Catalogotablas extends Model{
	protected $table = 'CATALOGO_TABLAS';
	public $timestamps = false;
	protected $primaryKey = 'IDCATALOGOTABLAS';
	//protected $guarded = ['IDMODULOS', 'IDCatalogotablas'];


	public static function tabtablas()
    {
	$estado = ["TAB_TABLAS.ESTADO"=> Constante::ESTADO_ACTIVO];
       return (new Catalogotablas())->hasMany(new Tabtablas(),"IDCATALOGOTABLAS","IDCATALOGOTABLAS")->where($estado);
	   //return Catalogotablas::hasMany(Catalogotablas::class);
	}


	public static function getTablaTablasByIDS($dato){
		$result = array();
		$estado = ["CATALOGO_TABLAS.ESTADO"=>Constante::ESTADO_ACTIVO];
		$query = Catalogotablas::query()
		->selectRaw("CATALOGO_TABLAS.*")
		//->join("TAB_TABLAS", "TAB_TABLAS.IDCATALOGOTABLAS", "=", "CATALOGO_TABLAS.IDCATALOGOTABLAS")
		->where($estado)
		->where("CATALOGO_TABLAS.IDCATALOGOTABLAS", $dato)
		->with('tabtablas')
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

	public static function getCatalogoTablas(){
		$result = array();
		$estado = ["CATALOGO_TABLAS.ESTADO"=>Constante::ESTADO_ACTIVO];
		$query = Catalogotablas::query()
		->selectRaw("CATALOGO_TABLAS.*")
		->where($estado)
		->with('tabtablas')
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
		return $query;
	}





	public static function getTableColumns() {
        return (new Catalogotablas)
            ->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing((new Catalogotablas)->getTable());
	}

	public static function getTableFilas() {
		$result = array();
		//$datos = ["IDCatalogotablas"=>$idCatalogotablas, "ESTADO"=> 1];
		$query = Catalogotablas::query()
		->selectRaw("CATALOGO_TABLAS.*,CATALOGO_TABLAS.IDCATALOGOTABLAS AS ACCION")
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
