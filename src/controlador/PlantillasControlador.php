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
use App\Modelo\Acciones;
use App\Modelo\Accesos;
use App\Modelo\AccesoAcciones;
use App\Modelo\Plantillas;
use App\Modelo\PlantillaItemModulo;
// -----------------------------------------

class PlantillasControlador{
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
	// INICIAR VISTA DE MANTENIMIENTO DE PLANTILLAS
	public function index(Request $request, Response $response, $args){
		$idrol = $this->session::get("idrol");
		$id_item_modulo = 5; // ID EN LA TABLA -> item_modulos
		$titulo = "PLANTILLAS";
		$acciones = Acciones::DatosAccionesByIdItemModulo($id_item_modulo);
		$tmp_acceso = Accesos::DatosAccesoByIds($idrol,$id_item_modulo);
		$idacceso = $tmp_acceso["data"]["idacceso"];
		$acceso_acciones = AccesoAcciones::DatosByIdAcceso($idacceso);
		$datos = [
			"titulo" => $titulo,
			"acciones" => $acciones,
			"acceso_acciones" => $acceso_acciones
		];
		$this->view->render($response, 'cms/contenidos/bandeja_plantillas.twig', $datos);
		return $response;
	}

	// LISTAR PLANTILLAS
	public function ListarPlantillas(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$pag = $datos["pag"];
		$data = Plantillas::ListarPlantillas($pag);
		return PlantillasControlador::RespuestaJSON($response,$data);
	}

	// FILTRAR PLANTILLAS
	public function FiltrarPlantillas(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$pag = $datos["pag"];
		$params = [
			"nombre_plantilla" => $datos["plantilla_nombre_buscar"],
			"estado" => $datos["plantilla_estado_buscar"]
		];
		$data = Plantillas::FiltrarPlantillas($pag,$params);
		return PlantillasControlador::RespuestaJSON($response,$data);
	}

	// GUARDAR NUEVA PLANTILLA
	public function NuevaPlantilla(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$plantilla_nombre_nuevo = $datos["plantilla_nombre_nuevo"];
			$plantilla_ruta_nuevo = $datos["plantilla_ruta_nuevo"];

			$data = [
				"nombre_plantilla" => $plantilla_nombre_nuevo,
				"ruta_plantilla" => $plantilla_ruta_nuevo
			];
			Plantillas::NuevoRegistro($data);
			$mensaje = "Plantilla creada correctamente.";
			$estado = true;
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al crear una Plantilla.";
			$estado = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		return PlantillasControlador::RespuestaJSON($response,$datin);
	}

	// DATOS DE LA SECCION POR ID
	public function DatosPlantillaById(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$idplantilla = $datos["idplantilla"];
		$data = Plantillas::DatosPlantillaById($idplantilla);
		return PlantillasControlador::RespuestaJSON($response,$data);
	}

	// EDITAR SECCION
	public function EditarPlantilla(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idplantilla_editar = $datos["idplantilla_editar"];
			$plantilla_nombre_editar = $datos["plantilla_nombre_editar"];
			$plantilla_ruta_editar = $datos["plantilla_ruta_editar"];
			$data = [
				"nombre_plantilla" => $plantilla_nombre_editar,
				"ruta_plantilla" => $plantilla_ruta_editar
			];
			Plantillas::ActualizarRegistro($idplantilla_editar,$data);
			$mensaje = "Datos Actualizados Correctamente.";
			$estado = true;
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al editar la Plantilla -> ".$e;
			$estado = false;
		}
		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		return PlantillasControlador::RespuestaJSON($response,$datin);
	}

	// CAMBIAR ESTADO DE LA PLANTILLA
	public function CambiarEstadoPlantilla(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idplantilla = $datos["idplantilla"];
			$valor = $datos["valor"];
			switch ($valor) {
				case 'true':
					$estado = 1;
					$mensaje = "Plantilla Habilitada.";
				break;
				case 'false':
					$estado = 0;
					$mensaje = "Plantilla Deshabilitada.";
				break;
			}
			$data = ["estado" => $estado];
			Plantillas::ActualizarRegistro($idplantilla,$data);
			$success = true;
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al camibar el estado a la Plantilla -> ".$e;
			$success = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $success;
		return PlantillasControlador::RespuestaJSON($response,$datin);
	}

	// LISTAR SECCIONES POR PLANTILLA
	public function SeccionesByPlantilla(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$pag = $datos["pag"];
		$idplantilla = $datos["idplantilla"];
		$tipo = $datos["tipo"];
		$data = PlantillaItemModulo::SeccionesByPlantilla($pag,$idplantilla,$tipo);
		return PlantillasControlador::RespuestaJSON($response,$data);
	}

	// ASIGNAR UNA SECCION A UNA PLANTILLA
	public function AsignarSeccionPlantilla(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idplantilla = $datos["idplantilla"];
			$idseccion = $datos["idseccion"];
			
			$tmp = PlantillaItemModulo::VerificarPlantillaSeccion($idplantilla,$idseccion);
			if ($tmp["success"]) {
				$mensaje = "La SECCIÓN ya se encuentra Asignada a esta PLANTILLA";
				$estado = false;
			} else {
				$data = [
					"idplantilla" => $idplantilla,
					"id_item_modulo" => $idseccion
				];
				PlantillaItemModulo::NuevoRegistro($data);
				$mensaje = "La SECCIÓN fue Asignada Correctamente.";
				$estado = true;
			}
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al asignar la Sección a la Plantilla -> ".$e;
			$estado = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		return PlantillasControlador::RespuestaJSON($response,$datin);
	}

	// CAMBIAR ESTADO DE LA SECCIÓN ASIGANADA UNA PLANTILLA
	public function CambiarEstadoSeccionPlantilla(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idplantilla = $datos["idplantilla"];
			$idseccion = $datos["idseccion"];
			$valor = $datos["valor"];
			switch ($valor) {
				case 'true':
					$estado = 1;
					$mensaje = "Sección Habilitada en la Plantilla.";
				break;
				case 'false':
					$estado = 0;
					$mensaje = "Sección Deshabilitada de la Plantilla.";
				break;
			}
			$data = ["estado" => $estado];
			PlantillaItemModulo::ActualizarRegistro($idplantilla,$idseccion,$data);
			$success = true;
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al camibar el estado a la Sección de la Plantilla -> ".$e;
			$success = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $success;
		return PlantillasControlador::RespuestaJSON($response,$datin);
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