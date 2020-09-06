<?php
namespace App\Controlador;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Illuminate\Database\Query\Builder;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Helper\Hash;
use App\Helper\Acl;
use App\Helper\JsonRequest;
use App\Helper\JsonRenderer;
use App\Helper\Session;
use App\Helper\Constante;
use RKA\Middleware\IpAddress;

// MODELOS
// -----------------------------------------
use App\Modelo\PlantillaItemModulo;
use App\Modelo\Contenido;
use App\Modelo\Docadjunto;
use App\Modelo\LogConexion;
use App\Modelo\Control;

// -----------------------------------------

class PruebaControlador{
	private $view;
	private $logger;
	private $hash;
	private $auth;
	private $session;
	private $jsonRequest;
	protected $table;
	protected $carpetaUpload;
	public function __construct(Twig $view, LoggerInterface $logger, Builder $table, JsonRequest $jsonRequest, $hash, $auth,
	$carpetaUpload){
		$this->hash = $hash;
		$this->auth = $auth;
		$this->session = new \App\Helper\Session;
		$this->jsonRequest = new JsonRequest();
		$this->JsonRender = new JsonRenderer();
		$this->view = $view;
		$this->logger = $logger;
		$this->table = $table;
		$this->carpetaUpload= $carpetaUpload;
	}

	public function index(Request $request, Response $response, $args){
		$secciones = PlantillaItemModulo::SeccionesByPlantilla(0,1,2);
		$cont_secc = array();
		for ($i=0; $i < count($secciones); $i++) {
			$idseccion = $secciones["data"][$i]["id_item_modulo"];
			$tmp = Contenido::ContenidoBySeccion($idseccion);
			array_push($cont_secc,$tmp);
		}
		$datos = [
			"secciones" => $secciones,
			"cont_secc" => $cont_secc
		];
		$this->view->render($response, "web/index_prueba.twig",$datos);
		return $response;
	}

	public function appFile(Request $request, Response $response, $args){

		try {
			$datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
			$uploadedFiles = $request->getUploadedFiles();
			// handle single input with single file upload
			$uploadedFile = $uploadedFiles['file']; //se extrae el archivo
			$datitos = JsonRenderer::StringJSONDecoder($datos["operativo"]);
			$directory=$this->carpetaUpload;
			$idDocAdjunto =Array();
			foreach ($uploadedFile as $key => $uplFile) {
				// code...
				//$idDocAdjunto = Acl::creaCarpetaGuardaArchivo($uplFile,$directory);
				//array_push($idDocAdjunto, Acl::creaCarpetaGuardaArchivo($uplFile,$directory));
				//$idDocAdjunto += [ "idDocAdjunto" =>  Acl::creaCarpetaGuardaArchivo($uplFile,$directory) ];
				$idDocAdjunto[$key] = ["idDocAdjunto" =>Acl::creaCarpetaGuardaArchivo($uplFile,$directory)];
			}
			$datitos["ESTADO"] =1;
			$datitos += [ "FOTOS" => json_encode($idDocAdjunto) ];
			//array_push($datitos, "FOTOS", json_encode($idDocAdjunto));
			$control = new Control($datitos);
			$control->save();
			$datin["control"]= $control;
			$mensaje ="se cambio de estado correctamente";
			$estado = true;
		} catch (\ErrorException $e) {
			$mensaje="Algo no salio muy bien";
			$estado=false;
			}
		   $datin["mensaje"]= $mensaje;
		   $datin["success"] = $estado;
			/*[{"team": ""},]*/
		   $obj1 = JsonRenderer::render($response,200,$datin);

		   return $obj1;
		}

		public function validaServidor(Request $request, Response $response, $args){
			try {
				//$datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
				$datos =  $request->getQueryParams();
				$datitos =  JsonRenderer::StringJSONDecoder($datos["LogConexion"]);
				$logConexion = new LogConexion($datitos);
				$logConexion->save();
				$datin["datos"]= $logConexion;
				$mensaje ="Tiene conexión con el servidor";
				$estado = true;
			} catch (\ErrorException $e) {
				$mensaje="No tiene conexión con el servidor";
				$estado=false;
				}
				 $datin["mensaje"]= $mensaje;
				 $datin["success"] = $estado;
				/*[{"team": ""},]*/
				 $obj1 = JsonRenderer::render($response,200,$datin);

				 return $obj1;
			}


}
