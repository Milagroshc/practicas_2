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
//---------------------------------
use Balping\JsonRaw\Raw;
use Balping\JsonRaw\Encoder;

// MODELOS
// -----------------------------------------
use App\Modelo\Parametros;
use App\Modelo\UnidadOrganica;
use App\Modelo\Acciones;
use App\Modelo\Accesos;
use App\Modelo\AccesoAcciones;
use App\Modelo\Usuario;
use App\Modelo\Persona;
use App\Modelo\Empleado;
use App\Modelo\UsuarioRol;
use App\Modelo\Rol;
use App\Modelo\Modulos;
use App\Modelo\Sistema;
use App\Modelo\Objacceso;
use App\Modelo\Menu;
use App\Modelo\Catalogotablas;
// -----------------------------------------


class SistemaControlador{
	private $view;
	private $logger;
	private $hash;
	private $auth;
	private $session;
	private $jsonRequest;
	protected $table;
	public function __construct(Twig $view, LoggerInterface $logger, Builder $table, JsonRequest $jsonRequest, $hash, $auth){
		$this->hash = $hash;
		$this->auth = $auth;
		$this->session = new \App\Helper\Session;
		$this->jsonRequest = new JsonRequest();
		$this->JsonRender = new JsonRenderer();
		$this->view = $view;
		$this->logger = $logger;
		$this->table = $table;
	}
	public function index(Request $request, Response $response, $args){

		//return $response->withHeader('Location', 'urlviaje');
		//$app->redirect('/books', '/library', 301);
		//$response->redirect($app->urlFor('games.markets', array('game' => "1")));
		//exit;

		$uri       = $request->getUri();

		$baseUrl    = $uri->getBaseUrl();
		$path       = $uri->getPath();
		//echo $uri;
		//echo $path;
	
		// extraer todos las opciones del menu
		$route = $request->getAttribute('route');

		// return NotFound for non existent route
		if (empty($route)) {
			throw new NotFoundException($request, $response);
		}
		$name = $route->getName();

		$objOpciones = Menu::getOpcionesById($name);
		$datin["objOpciones"]=$objOpciones;
		//var_dump($obj["data"][0]["modulos"]["IDMODULO"]);
		//$objOpciones = Objacceso::getObjaccesoById($obj["data"][0]["IDMODULO"]);
		//var_dump($objOpciones);
		$groups = $route->getGroups();
		$rutagrupos = "";
		foreach ($groups as $group) {
			//var_dump($group->getPattern());
			//echo strstr($group->getPattern(), '/')."\n";
			//$vowels = array("/");
			//$ruta = str_replace($vowels, "", $group->getPattern());
			//$getSistema = Sistema::getSistemaByRuta($ruta);
			//var_dump($getSistema);
			$rutagrupos = $rutagrupos.$group->getPattern();
		}
		$datin["rutagrupos"]=$path;
/*
		$methods = $route->getMethods();
		//var_dump($methods);
		$arguments = $route->getArguments();
		//var_dump($arguments);
*/

		$datin["datos"] = [
			"title" => "CMS",
			//"saludo" => "Bienvenido ".$nombres." ".$ape_paterno." ".$ape_materno,
			"titulo" => "BIENVENIDO A LA ADMINISTRACIÓN DEL CMS",
			//"idusuario" => $idusuario,
			//"idempleado" => $idempleado,
			//"id_unidad_organica" => $id_unidad_organica
		];
		//datos de la tabla de tabla necesario
		//Array de tablas necesarias para formulario
		
		$datin["estados"]=Catalogotablas::getTablaTablasByIDS(Constante::ID_ESTADOGENERAL);
		$datin["parametros"] = ['title'=>"Sistemas :: negocio", 'titulo' => "Editar tabla sistemas" ];
	//var_dump($datin["estados"]["data"]["tabtablas"]);
	//exit;
		$this->view->render($response, 'negocio/pedidos/mantenimiento-tablas/sistemas.twig', $datin);
		return $response;
	}

		// RETORNA DATOS DEL USUARIO EN CASO EXISTA
		public function getColumnasSistema(Request $request, Response $response, $args){
		
			$columnas =Sistema::getTableColumns();

			$caracteres = array("_");

			foreach ($columnas as $index=>$columna) {
				$colum[$index]["name"]=$columna;
				$colum[$index]["title"]=str_replace($caracteres, " ", $columna);
				$colum[$index]["breakpoints"]="xs sm";
				//$colum[$index]["formatter"]=new Raw("function(value, options, rowData){ return '<span>' + value + '</span>';}");
				//$colum[$index]["ACCION"]="A";
			}
			$colum[$index+1]["name"]="ACCION";
				$colum[$index+1]["title"]="ACCIÓN";
				$colum[$index+1]["breakpoints"]="xs sm";
		$json = Encoder::encode($colum);
			return JsonRenderer::RespuestaJSONEncoder($response,$json);
		}

		public function getFilasSistema(Request $request, Response $response, $args){
			$filas =Sistema::getTableFilas();
			return JsonRenderer::RespuestaJSON($response,$filas["data"]);
		}

	

		public function cambiarEstado(Request $request, Response $response, $args){

            try {
                $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
                Sistema::where('IDSISTEMA', $datos["idSistema"])	   
                ->update(['ESTADO' => $datos["estado"]]);
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
			
			//guardar un nuevo sistema
			public function nuevoSistema(Request $request, Response $response, $args){

				try {
					$datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
					$ruta = Acl::GenerarURL($datos["NOMBRE_CORTO"]);
					$datos["RUTA"]= $ruta;
					$idSistema = Sistema::insertGetId($datos);
					$datos["IDSISTEMA"] = $idSistema;
					$datos["ACCION"] = $idSistema;
					
					$mensaje ="se registró correctamente el sistema";
					$estado = true;
					$datin["sistema"]= $datos;
						
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
			//actualiza los campos del sistema
			public function editarSistema(Request $request, Response $response, $args){

				try {
					$datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
					
					Sistema::where('IDSISTEMA', $datos["IDSISTEMA"])	   
					->update($datos);
					$mensaje ="se actualizó correctamente el registro";
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

				//actualiza los campos del sistema
			public function eliminarSistema(Request $request, Response $response, $args){

				try {
					$datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
					
				Sistema::where('IDSISTEMA', $datos["IDSISTEMA"])
				->delete();
				$mensaje ="se elimino correctamente";
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
}
?>