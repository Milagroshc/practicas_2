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
use App\Modelo\Parametros;
use App\Modelo\Acciones;
use App\Modelo\Accesos;
use App\Modelo\AccesoAcciones;
use App\Modelo\ItemModulo;
// -----------------------------------------

class SeccionesControlador{
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

// ********************************************************************************* MANTENIMIENTO DE SECCIONES *************************************************************************
	// INICIAR VISTA DE MANTENIMIENTO DE SECCIONES
	public function index(Request $request, Response $response, $args){
		$idrol = $this->session::get("idrol");
		$id_item_modulo = 4; // ID EN LA TABLA -> item_modulos
		$titulo = "SECCIONES";
		$estado_publicacion = Parametros::getParametros(2);
		$acciones = Acciones::DatosAccionesByIdItemModulo($id_item_modulo);
		$tmp_acceso = Accesos::DatosAccesoByIds($idrol,$id_item_modulo);
		$idacceso = $tmp_acceso["data"]["idacceso"];
		$acceso_acciones = AccesoAcciones::DatosByIdAcceso($idacceso);
		$datos = [
			"titulo" => $titulo,
			"estado_publicacion" => $estado_publicacion,
			"acciones" => $acciones,
			"acceso_acciones" => $acceso_acciones
		];
		$this->view->render($response, 'cms/contenidos/bandeja_secciones.twig', $datos);
		return $response;
	}

	// LISTAR SECCIONES
	public function ListarSecciones(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$pag = $datos["pag"];
		$tipo = $datos["tipo"];
		$data = ItemModulo::ListarItemModulos($pag,$tipo);
		return SeccionesControlador::RespuestaJSON($response,$data);
	}

	// FILTRAR SECCIONES
	public function FiltrarSecciones(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$pag = $datos["pag"];
		$params = [
			"nombre_item" => $datos["seccion_nombre_buscar"],
			"estado" => $datos["seccion_estado_buscar"]
		];
		$data = ItemModulo::FiltrarSecciones($pag,$params);
		return SeccionesControlador::RespuestaJSON($response,$data);
	}

	// GUARDAR NUEVA SECCION
	public function NuevaSeccion(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$seccion_nombre_nuevo = $datos["seccion_nombre_nuevo"];
			$seccion_nomarchivo_nuevo = $datos["seccion_nomarchivo_nuevo"];

			$data = [
				"id_modulo" => 2,
				"nombre_item" => $seccion_nombre_nuevo,
				"archivo_portal_web" => $seccion_nomarchivo_nuevo
			];
			ItemModulo::NuevoRegistro($data);
			$mensaje = "Sección creada correctamente.";
			$estado = true;
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al crear la Sección.";
			$estado = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		return SeccionesControlador::RespuestaJSON($response,$datin);
	}

	// DATOS DE LA SECCION POR ID
	public function DatosSeccionById(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$idseccion = $datos["idseccion"];
		$data = ItemModulo::DatosById($idseccion);
		return SeccionesControlador::RespuestaJSON($response,$data);
	}

	// EDITAR SECCION
	public function EditarSeccion(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idseccion_editar = $datos["idseccion_editar"];
			$seccion_nombre_editar = $datos["seccion_nombre_editar"];
			$seccion_nomarchivo_editar = $datos["seccion_nomarchivo_editar"];
			$data = [
				"nombre_item" => $seccion_nombre_editar,
				"archivo_portal_web" => $seccion_nomarchivo_editar
			];
			ItemModulo::ActualizarRegistro($idseccion_editar,$data);
			$mensaje = "Datos Actualizados Correctamente.";
			$estado = true;
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al editar la Sección -> ".$e;
			$estado = false;
		}
		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		return SeccionesControlador::RespuestaJSON($response,$datin);
	}

	// CAMBIAR ESTADO DE LA SECCION
	public function CambiarEstadoSeccion(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idseccion = $datos["idseccion"];
			$valor = $datos["valor"];
			switch ($valor) {
				case 'true':
					$estado = 1;
					$mensaje = "Sección Habilitada.";
				break;
				case 'false':
					$estado = 0;
					$mensaje = "Sección Deshabilitada.";
				break;
			}
			$data = ["estado" => $estado];
			ItemModulo::ActualizarRegistro($idseccion,$data);
			$success = true;
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al camibar el estado a la Sección -> ".$e;
			$success = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $success;
		return SeccionesControlador::RespuestaJSON($response,$datin);
	}

// ***************************************************************************************************************************************************************************************
	// ENVIAR RESPUESTA DE UNA FUNCION EN FORMATO JSON
	public function RespuestaJSON($response, $data) {
		$newResponse = $response->withHeader('Access-Control-Allow-Origin', '*');
		$newResponse = $response->withAddedHeader('Content-Type', 'application/json');
		$newResponse = $newResponse->withStatus(200);
		$newResponse->getBody()->write(json_encode($data));
		return $newResponse;
	}
}
?>