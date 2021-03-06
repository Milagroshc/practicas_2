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

use Culqi;
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


class ObjaccesoControlador{
	private $app;
	private $view;
	private $logger;
	private $hash;
	private $auth;
	private $session;
	private $jsonRequest;
	protected $table;
	public function __construct($app, Twig $view, LoggerInterface $logger, Builder $table, JsonRequest $jsonRequest, $hash, $auth){
		$this->hash = $hash;
		$this->auth = $auth;
		$this->app = $app;
		$this->session = new \App\Helper\Session;
		$this->jsonRequest = new JsonRequest();
		$this->JsonRender = new JsonRenderer();
		$this->view = $view;
		$this->logger = $logger;
		$this->table = $table;
	}
	public function index(Request $request, Response $response, $args){


		$uri       = $request->getUri();

		$baseUrl    = $uri->getBaseUrl();
		$path       = $uri->getPath();
		//echo $uri;
		$data = $request->getParams(); //extrae los parametros enviados de 
		if (!empty($data["param0"])) {
			# code...
			$datin["idModulo"]=$data["param0"];
		}
		//exit;
//var_dump($data);
		//echo $route;
//		exit;

		$porciones = explode("/", $path);
		$ruta = $porciones[1]."/".$porciones[2]."/".$porciones[3];

		//$datin["IDSISTEMA"]=$args["param0"];

		// extraer todos las opciones del menu
		$route = $request->getAttribute('route');

		// return NotFound for non existent route
		if (empty($route)) {
			throw new NotFoundException($request, $response);
		}
		$name = $route->getName();
		$datin["routName"]=$name;

		$objOpciones = Menu::getOpcionesById($name);
	
		$datin["objOpciones"]=$objOpciones;
		$groups = $route->getGroups();
		$rutagrupos = "";
		foreach ($groups as $group) {
			$rutagrupos = $rutagrupos.$group->getPattern();
		}
		$datin["rutagrupos"]=$rutagrupos."/".$name;

		//extraer la lista de sistemas 
		$datin["sistemas"]= Sistema::getSistemasActivo()["data"];
		$datin["modulos"]= Modulos::getModulosActivo()["data"];

		
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
		$datin["parametros"] = ['title'=>"Objeto accesos :: negocio", 'titulo' => "Editar tabla objetos para acceso" ];
	//var_dump($datin["estados"]["data"]["tabtablas"]);
	//exit;
		$this->view->render($response, 'negocio/pedidos/mantenimiento-tablas/objacceso.twig', $datin);
		return $response;
		
	}

	public function listaModulos(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		
		$datin["data"]= Modulos::getModulosActivoByIdSistema($datos["IDSISTEMA"]);
		$obj1 = JsonRenderer::render($response,200,$datin);
                
		  return $obj1;

	}
	public function listaObjacceso(Request $request, Response $response, $args){
		
		//return $response->withRedirect('../'.$args['param0']);

		//return $response->withRedirect($this->router->pathFor($route->getName(), ['game' => 'bar']));
		$data = $args;
  //var_dump($this->app->getContainer()->router);
  //exit;
//var_dump($data);
//exit;
		//return $response->withRedirect($this->router->pathFor('tabla-modulos', ['data' => $data]));//https://github.com/slimphp/Slim/issues/1933
		return $response->withRedirect($this->app->getContainer()->router->pathFor('tabla-objetos', [], $args));
	}
		// RETORNA DATOS DEL USUARIO EN CASO EXISTA
		public function getColumnasObjacceso(Request $request, Response $response, $args){
		
			$columnas =Objacceso::getTableColumns();

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

		public function getFilasObjacceso(Request $request, Response $response, $args){
			$idModulo = $request->getQueryParam("IDMODULO");
			$filas =Objacceso::getTableFilas($idModulo);
			
			if ($filas["success"]) {
				# code...
				//return $filas["mensaje"];
			}else{
				$filas["data"]=null;
			}
			return JsonRenderer::RespuestaJSON($response,$filas["data"]);
		}

	

		public function cambiarEstado(Request $request, Response $response, $args){

            try {
                $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
                Objacceso::where('IDOBJACCESO', $datos["idObjacceso"])	   
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
			public function nuevoObjacceso(Request $request, Response $response, $args){

				try {
					$datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
					$ruta = Acl::GenerarURL($datos["NOMBRE"]);
					$datos["RUTA"]= $ruta;
					$idObjacceso = Objacceso::insertGetId($datos);
					$datos["IDOBJACCESO"] = $idObjacceso;
					$datos["ACCION"] = $idObjacceso;
					
					$mensaje ="se registró correctamente el Objeto de acceso";
					$estado = true;
					$datin["objeto"]= $datos;
						
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
			public function editarObjacceso(Request $request, Response $response, $args){

				try {
					$datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
					$ruta = Acl::GenerarURL($datos["NOMBRE"]);
					$datos["RUTA"]= $ruta;
					Objacceso::where('IDOBJACCESO', $datos["IDOBJACCESO"])	   
					->update($datos);
					$datin["objeto"]= $datos;
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
			public function eliminarObjacceso(Request $request, Response $response, $args){

				try {
					$datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
					
				Objacceso::where('IDOBJACCESO', $datos["IDOBJACCESO"])
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