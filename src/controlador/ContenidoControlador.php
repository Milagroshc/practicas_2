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
use App\Modelo\Contenido;
use App\Modelo\Archivos;
use App\Modelo\ContenidoArchivo;
// -----------------------------------------

class ContenidoControlador{
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

// ************************************************************************** MANTENIMIENTO DE CONTENIDOS *************************************************************************
	// INICIAR VISTA DE MANTENIMIENTO DE CONTENIDOS
	public function index(Request $request, Response $response, $args){
		$idrol = $this->session::get("idrol");
		$id_item_modulo = Constante::ID_ITEM_MODULO_CONTENIDO; // ID EN LA TABLA -> item_modulos
		$titulo = "CONTENIDOS";
		$secciones = ItemModulo::ListarItemModulos(0,1,0);
		$estado_publicacion = Parametros::getParametros(2);
		$acciones = Acciones::DatosAccionesByIdItemModulo($id_item_modulo);
		$tmp_acceso = Accesos::DatosAccesoByIds($idrol,$id_item_modulo);
		$idacceso = $tmp_acceso["data"]["idacceso"];
		$acceso_acciones = AccesoAcciones::DatosByIdAcceso($idacceso);
		$datos = [
			"titulo" => $titulo,
			"contenido" => "CONTENIDOS",
			"secciones" => $secciones,
			"estado_publicacion" => $estado_publicacion,
			"acciones" => $acciones,
			"acceso_acciones" => $acceso_acciones
		];
		$this->view->render($response, 'cms/contenidos/bandeja_contenidos.twig', $datos);
		return $response;
	}

	// LISTAR CONTENIDO
	public function ListarContenido(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$pag = $datos["pag"];
		$data = Contenido::ListarContenido($pag);
		return ContenidoControlador::RespuestaJSON($response,$data);
	}

	// LISTAR CONTENIDO
	public function ListarContenidoHijo(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$pag = $datos["pag"];
		$idpadre = $datos["idpadre"];
		$data = Contenido::ListarContenidoHijo($pag,$idpadre);
		return ContenidoControlador::RespuestaJSON($response,$data);
	}

	// FILTRAR CONTENIDO
	public function FiltrarContenido(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$pag = $datos["pag"];
		$params = [
			"id_item_modulo" => $datos["id_item_modulo"],
			"url" => $datos["contenido_url_buscar"],
			"titulo" => $datos["contenido_titulo_buscar"],
			"fecha_contenido" => $datos["contenido_fecha_buscar"],
			"estado_contenido" => $datos["contenido_estado_buscar"]
		];
		$data = Contenido::FiltrarContenido($pag,$params);
		return ContenidoControlador::RespuestaJSON($response,$data);
	}

	// FILTRAR CONTENIDO
	public function FiltrarContenidoHijo(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$pag = $datos["pag"];
		$params = [
			"id_item_modulo" => $datos["contenido_seccion_buscar"],
			"idpadre" => $datos["idpadre"],
			"url" => $datos["contenido_url_buscar"],
			"titulo" => $datos["contenido_titulo_buscar"],
			"fecha_contenido" => $datos["contenido_fecha_buscar"],
			"estado_contenido" => $datos["contenido_estado_buscar"]
		];
		$data = Contenido::FiltrarContenidoHijo($pag,$params);
		return ContenidoControlador::RespuestaJSON($response,$data);
	}

// ******************************************************************************************************************************************************************************
	// INICIAR VISTA NUEVO CONTENIDO
	public function indexNuevoContenido(Request $request, Response $response, $args){
		$titulo = "NUEVO CONTENIDO";
		$secciones = ItemModulo::ListarItemModulos(0,1);
		$estado_publicacion = Parametros::getParametros(2);
		$datos = [
			"titulo" => $titulo,
			"secciones" => $secciones,
			"estado_publicacion" => $estado_publicacion
		];
		$this->view->render($response, 'cms/contenidos/nuevo_contenido.twig', $datos);
		return $response;
	}

	// GUARDAR NUEVO CONTENIDO
	public function NuevoContenido(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$id_item_modulo = $datos["id_item_modulo"];
			$contenido_titulo_nuevo = $datos["contenido_titulo_nuevo"];
			$contenido_url_nuevo = Acl::GenerarURL($contenido_titulo_nuevo);
			$contenido_icono_nuevo = $datos["contenido_icono_nuevo"];
			$contenido_target_nuevo = $datos["contenido_target_nuevo"];
			$contenido_fecha_nuevo = $datos["contenido_fecha_nuevo"];
			$contenido_estado_nuevo = $datos["contenido_estado_nuevo"];
			$contenido_resumen_nuevo = $datos["contenido_resumen_nuevo"];
			$contenido_contenido_nuevo = $datos["contenido_contenido_nuevo"];

			$tmp = Contenido::DatosByUrl($contenido_url_nuevo);
			if ($tmp["success"]) {
				$mensaje = "La URL ingresada ya existe en otro contenido, ingrese uno diferente.";
				$estado = false;
			} else {
				$data = [
					"id_item_modulo" => $id_item_modulo,
					"url" => $contenido_url_nuevo,
					"titulo" => $contenido_titulo_nuevo,
					"resumen" => $contenido_resumen_nuevo,
					"contenido" => $contenido_contenido_nuevo,
					"icono" => $contenido_icono_nuevo,
					"target" => $contenido_target_nuevo,
					"estado_contenido" => $contenido_estado_nuevo,
					"fecha_contenido" => $contenido_fecha_nuevo,
					"usuario_registro" => $this->session::get("idusuario")
				];
				Contenido::NuevoRegistro($data);
				$mensaje = "Contenido creado correctamente.";
				$estado = true;
			}
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al cambiar la contraseña.";
			$estado = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		return ContenidoControlador::RespuestaJSON($response,$datin);
	}

// ******************************************************************************************************************************************************************************
	// SETEAR ID DE CONTENIDO PARA EDITAR
	public function SetearIdContenido(Request $request, Response $response, $args){
		try {
			$datos = $request->getParsedBody();
			$session = new \App\Helper\Session;
			$session::set('tmp_id_contenido',intval($datos["idcontenido"]));
			$mensaje = "ID Seteada Correctamente.";
			$estado = true;
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al setear el id contenido -> ".$e;
			$estado = false;
		}
		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		return ContenidoControlador::RespuestaJSON($response,$datin);
	}

	// INICIAR VISTA NUEVO CONTENIDO
	public function indexEditarContenido(Request $request, Response $response, $args){
		$titulo = "EDITAR CONTENIDO";
		$secciones = ItemModulo::ListarItemModulos(0,1);
		$estado_publicacion = Parametros::getParametros(2);
		$id_contenido = $this->session::get("tmp_id_contenido");
		$data = Contenido::DatosById($id_contenido);

		$datos = [
			"titulo" => $titulo,
			"secciones" => $secciones,
			"estado_publicacion" => $estado_publicacion,
			"contenido" => $data
		];
		$this->view->render($response, 'cms/contenidos/editar_contenido.twig', $datos);
		return $response;
	}

	// RETORNA DATOS DEL CONTENIDO EN CASO EXISTA
	public function DatosContenidoById(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$idcontenido = $datos["idcontenido"];
		$data = Contenido::DatosById($idcontenido);
		return ContenidoControlador::RespuestaJSON($response,$data);
	}

	// EDITAR CONTENIDO
	public function EditarContenido(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idcontenido_editar = $datos["idcontenido_editar"];
			$contenido_titulo_editar = $datos["contenido_titulo_editar"];
			$contenido_url_editar = Acl::GenerarURL($contenido_titulo_editar);
			$contenido_icono_editar = $datos["contenido_icono_editar"];
			$contenido_seccion_editar = $datos["contenido_seccion_editar"];
			$contenido_target_editar = $datos["contenido_target_editar"];
			$contenido_fecha_editar = $datos["contenido_fecha_editar"];
			$contenido_estado_editar = $datos["contenido_estado_editar"];
			$contenido_resumen_editar = $datos["contenido_resumen_editar"];
			$contenido_contenido_editar = $datos["contenido_contenido_editar"];

			$tmp_a = Contenido::DatosById($idcontenido_editar);
			if ($tmp_a["success"]) {
				if ($tmp_a["data"]["url"] == $contenido_url_editar) {
					$data = [
						"id_item_modulo" => $contenido_seccion_editar,
						"titulo" => $contenido_titulo_editar,
						"resumen" => $contenido_resumen_editar,
						"contenido" => $contenido_contenido_editar,
						"icono" => $contenido_icono_editar,
						"target" => $contenido_target_editar,
						"estado_contenido" => $contenido_estado_editar,
						"fecha_contenido" => $contenido_fecha_editar
					];
					Contenido::ActualizarRegistro($idcontenido_editar,$data);
					$mensaje = "Contenido Editado correctamente";
					$estado = true;
				} else {
					$tmp_b = Contenido::DatosByUrl($contenido_url_editar);
					if ($tmp_b["success"]) {
						$mensaje = "La URL ingresada ya existe en otro contenido, ingrese uno diferente.";
						$estado = false;
					} else {
						$data = [
							"url" => $contenido_url_editar,
							"titulo" => $contenido_titulo_editar,
							"resumen" => $contenido_resumen_editar,
							"contenido" => $contenido_contenido_editar,
							"estado_contenido" => $contenido_estado_editar,
							"fecha_contenido" => $contenido_fecha_editar
						];
						Contenido::ActualizarRegistro($idcontenido_editar,$data);
						$mensaje = "Contenido Editado correctamente";
						$estado = true;
					}
				}
			} else {
				$mensaje = "No se pudo obtener los datos para validar los cambios.";
				$estado = false;
			}
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al cambiar la contraseña.";
			$estado = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		return ContenidoControlador::RespuestaJSON($response,$datin);
	}

	//*********************************************eliminar contenido *******************/
	public static function eliminarContenido(Request $request, Response $response, $args) {
        
        
		try {
			$datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
			Contenido::where('idContenido', $datos["idContenido"])
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

// ******************************************************************************************************************************************************************************
	// SETEAR ID DE CONTENIDO PADRE PARA CREAR NUEVO CONTENIDO
	public function SetearIdPadre(Request $request, Response $response, $args){
		try {
			$datos = $request->getParsedBody();
			$session = new \App\Helper\Session;
			$session::set('tmp_idpadre', intval($datos["idpadre"]));
			$mensaje = "ID Seteada Correctamente.";
			$estado = true;
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al setear el id contenido -> ".$e;
			$estado = false;
		}
		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		return ContenidoControlador::RespuestaJSON($response,$datin);
	}

	// INICIAR VISTA NUEVO CONTENIDO
	public function indexNuevoContenidoHijo(Request $request, Response $response, $args){
		$titulo = "NUEVO CONTENIDO HIJO";
		$secciones = ItemModulo::ListarItemModulos(0,1,0);
		$estado_publicacion = Parametros::getParametros(2);
		$idpadre = $this->session::get("tmp_idpadre");
		$datos = [
			"titulo" => $titulo,
			"secciones" => $secciones,
			"estado_publicacion" => $estado_publicacion,
			"idpadre" => $idpadre
		];
		$this->view->render($response, 'cms/contenidos/nuevo_contenido_hijo.twig', $datos);
		return $response;
	}

	// GUARDAR NUEVO CONTENIDO - HIJO
	public function NuevoContenidoHijo(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$id_item_modulo = $datos["id_item_modulo"];
			$idpadre = $datos["idpadre"];
			$contenido_titulo_nuevo = $datos["contenido_titulo_nuevo"];
			$contenido_url_nuevo = Acl::GenerarURL($contenido_titulo_nuevo);
			$contenido_icono_nuevo = $datos["contenido_icono_nuevo"];
			$contenido_target_nuevo = $datos["contenido_target_nuevo"];
			$contenido_fecha_nuevo = $datos["contenido_fecha_nuevo"];
			$contenido_estado_nuevo = $datos["contenido_estado_nuevo"];
			$contenido_resumen_nuevo = $datos["contenido_resumen_nuevo"];
			$contenido_contenido_nuevo = $datos["contenido_contenido_nuevo"];

			$tmp = Contenido::DatosByUrl($contenido_url_nuevo);
			if ($tmp["success"]) {
				$mensaje = "La URL ingresada ya existe en otro contenido, ingrese uno diferente.";
				$estado = false;
			} else {
				$data = [
					"id_item_modulo" => $id_item_modulo,
					"idpadre" => $idpadre,
					"url" => $contenido_url_nuevo,
					"titulo" => $contenido_titulo_nuevo,
					"resumen" => $contenido_resumen_nuevo,
					"contenido" => $contenido_contenido_nuevo,
					"icono" => $contenido_icono_nuevo,
					"target" => $contenido_target_nuevo,
					"estado_contenido" => $contenido_estado_nuevo,
					"fecha_contenido" => $contenido_fecha_nuevo,
					"usuario_registro" => $this->session::get("idusuario")
				];
				Contenido::NuevoRegistro($data);
				$mensaje = "Contenido creado correctamente.";
				$estado = true;
			}
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al cambiar la contraseña.";
			$estado = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		return ContenidoControlador::RespuestaJSON($response,$datin);
	}

// ******************************************************************************************************************************************************************************
	// SETEAR ID DE CONTENIDO PARA VISUALIZAR O ASIGNAR ARCHIVOS
	public function SetearIdContenidoArchivo(Request $request, Response $response, $args){
		try {
			$datos = $request->getParsedBody();
			$session = new \App\Helper\Session;
			$session::set('tmp_idcontenido_archivo', intval($datos["idcontenido"]));
			$mensaje = "ID Seteada Correctamente.";
			$estado = true;
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al setear el id contenido -> ".$e;
			$estado = false;
		}
		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		return ContenidoControlador::RespuestaJSON($response,$datin);
	}

	// INICIAR VISTA ASIGNAR ARCHIVOS A UN CONTENIDO
	public function indexArchivosContenido(Request $request, Response $response, $args){
		$titulo = "ARCHIVOS";
		$idcontenido = $this->session::get("tmp_idcontenido_archivo");
		$dominio = Constante::DOMAINSITE;
		$datos = [
			"titulo" => $titulo,
			"idcontenido" => $idcontenido,
			"dominio" => $dominio
		];
		$this->view->render($response, 'cms/contenidos/archivos_contenido.twig', $datos);
		return $response;
	}

	// OBTENER TODOS LOS ARCHIVOS DE UN CONTENIDO
	public function ArchivosByIdContenido(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$idcontenido = $datos["idcontenido"];
		$archivos = ContenidoArchivo::ArchivosByIdContenido($idcontenido);
		return ContenidoControlador::RespuestaJSON($response,$archivos);
	}

	// CAMBIAR ESTADO DEL ARCHIVO DENTRO DE UN CONTENIDO
	public function CambiarEstadoArchivoContenido(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idcontenido = $datos["idcontenido"];
			$idarchivo = $datos["idarchivo"];
			$val = $datos["val"];
			switch ($val) {
				case 'true':
					$estado = 1;
					$mensaje = "ARCHIVO Habilitado.";
				break;
				case 'false':
					$estado = 0;
					$mensaje = "ARCHIVO Deshabilitado.";
				break;
			}
			$data = ["estado" => $estado];
			ContenidoArchivo::ActualizarRegistro($idcontenido,$idarchivo,$data);
			$success = true;
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al camibar el estado al Archivo -> ".$e;
			$success = false;
		}
		$datin["mensaje"] = $mensaje;
		$datin["success"] = $success;
		return ContenidoControlador::RespuestaJSON($response,$datin);
	}

	// CAMBIAR LA PORTADA DE UN CONTENIDO
	public function CambiarPortadaArchivoContenido(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idcontenido = $datos["idcontenido"];
			$idarchivo = $datos["idarchivo"];
			ContenidoArchivo::RetirarPortadaContenido($idcontenido);
			$data = ["portada" => 1];
			ContenidoArchivo::ActualizarRegistro($idcontenido,$idarchivo,$data);
			$success = true;
			$mensaje = "PORTADA Actualizada.";
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al camibar la portada del contenido -> ".$e;
			$success = false;
		}
		$datin["mensaje"] = $mensaje;
		$datin["success"] = $success;
		return ContenidoControlador::RespuestaJSON($response,$datin);
	}

	// SUBIR UN ARCHIVO PARA UN CONTENIDO
	public function SubirArchivoContenido(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idcontenido_archivo = $datos["idcontenido_archivo"];
			$tipo_archivo = $datos["tipo_archivo"];
			$uploadedFiles = $request->getUploadedFiles();
			$uploadedFile = $uploadedFiles['archivo_contenido'];
			$tipoFile  = $uploadedFile->getClientMediaType();

			switch ($tipo_archivo) {
				case '1':
					$directorio = Constante::REPOSITORIO_FILE."Contenidos/Imagenes/";
					if ($tipoFile == "image/jpeg" || $tipoFile == "image/jpg" || $tipoFile == "image/png") {
						$tmp = ContenidoControlador::GuardarArchivo($uploadedFile,$directorio,$tipoFile);
						if ($tmp["success"]) {
							$idarchivo = $tmp["data"]["idarchivo"];
							$data = [
								"idcontenido" => $idcontenido_archivo,
								"idarchivo" => $idarchivo
							];
							ContenidoArchivo::NuevoRegistro($data);
							$mensaje = "La IMAGEN se subió correctamente.";
							$estado = true;
						} else {
							$mensaje = "Hubo un error al subir la IMAGEN.";
							$estado = false;
						}
					} else {
						$mensaje = "Solo se permite archivos con extensión: .jpeg / .jpg / .png";
						$estado = false;
					}
				break;
				
				case '2':
					$directorio = Constante::REPOSITORIO_FILE."Contenidos/Documentos/";
					if ($tipoFile == "application/pdf" || $tipoFile == "application/msword" || $tipoFile == "application/vnd.openxmlformats-officedocument.wordprocessingml.document" || $tipoFile == "application/vnd.ms-excel" || $tipoFile == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
						$tmp = ContenidoControlador::GuardarArchivo($uploadedFile,$directorio,$tipoFile);
						if ($tmp["success"]) {
							$idarchivo = $tmp["data"]["idarchivo"];
							$data = [
								"idcontenido" => $idcontenido_archivo,
								"idarchivo" => $idarchivo
							];
							ContenidoArchivo::NuevoRegistro($data);
							$mensaje = "El DOCUMENTO se subió correctamente.";
							$estado = true;
						} else {
							$mensaje = "Hubo un error al subir el DOCUMENTO.";
							$estado = false;
						}
					} else {
						$mensaje = "Solo se permite archivos con extensión: .pdf / .doc / .docx / .xls / .xlsx";
						$estado = false;
					}
				break;
			}
		} catch (\ErrorException $e){
			$mensaje = "Ocurrió un error inesperado al Guardar la Imagen: ".$e;
			$estado = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		$obj1 = JsonRenderer::render($response,200,$datin);
	}

// ***************************************************************************************************************************************************************************************
	// SUBIR IMAGEN
	public function SubirImagen(Request $request, Response $response, $args) {
		try {
			$directorio = Constante::REPOSITORIO_FILE."Contenidos/Imagenes/";
			$uploadedFiles = $request->getUploadedFiles();
			$uploadedFile = $uploadedFiles['file_imagen'];
			$tipoFile  = $uploadedFile->getClientMediaType();

			if ($tipoFile == "image/jpeg" || $tipoFile == "image/jpg" || $tipoFile == "image/png") {
				$tmp = ContenidoControlador::GuardarArchivo($uploadedFile,$directorio,$tipoFile);
				if ($tmp["success"]) {
					$ruta = Constante::DOMAINSITE.$directorio.$tmp["data"]["nombre_encriptado"];
					$datin["ruta"] = $ruta;
					$mensaje = "La IMAGEN se subió correctamente en la siguiente ruta:<br><b>".$ruta."</b>";
					$estado = true;
				} else {
					$mensaje = "Hubo un error al capturar la imagen guardada.";
					$estado = false;
				}
			} else {
				$mensaje = "Solo se permite archivos con extensión: .jpeg / .jpg / .png";
				$estado = false;
			}
		} catch (\ErrorException $e){
			$mensaje = "Ocurrió un error inesperado al Guardar la Imagen: ".$e;
			$estado = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		$obj1 = JsonRenderer::render($response,200,$datin);
	}

	// SUBIR DOCUMENTO
	public function SubirDocumento(Request $request, Response $response, $args) {
		try {
			$directorio = Constante::REPOSITORIO_FILE."Contenidos/Documentos/";
			$uploadedFiles = $request->getUploadedFiles();
			$uploadedFile = $uploadedFiles['file_archivo'];
			$tipoFile  = $uploadedFile->getClientMediaType();

			if ($tipoFile == "application/pdf" || $tipoFile == "application/msword" || $tipoFile == "application/vnd.openxmlformats-officedocument.wordprocessingml.document" || $tipoFile == "application/vnd.ms-excel" || $tipoFile == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
				$tmp = ContenidoControlador::GuardarArchivo($uploadedFile,$directorio,$tipoFile);
				if ($tmp["success"]) {
					$ruta = Constante::DOMAINSITE.$directorio.$tmp["data"]["nombre_encriptado"];
					$datin["ruta"] = $ruta;
					$mensaje = "El DOCUMENTO se subió correctamente en la siguiente ruta:<br><b>".$ruta."</b>";
					$estado = true;
				} else {
					$mensaje = "Hubo un error al capturar el DOCUMENTO guardado.";
					$estado = false;
				}
			} else {
				$mensaje = "Solo se permite archivos con extensión: .pdf / .doc / .docx / .xls / .xlsx";
				$estado = false;
			}
		} catch (\ErrorException $e){
			$mensaje = "Ocurrió un error inesperado al Guardar el ARCHIVO: ".$e;
			$estado = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		$obj1 = JsonRenderer::render($response,200,$datin);
	}

// ***************************************************************************************************************************************************************************************
	// GUARDAR UN ARCHIVO EN LA BD Y EN EL DIRECTORIO
	public function GuardarArchivo($uploadedFile,$directorio,$tipoFile){
		if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
			$filename = Acl::moveUploadedFile($directorio, $uploadedFile);
			$rs_peso = Acl::ObtenerPesoFile($uploadedFile->getSize());
			$data = [
				"nombre_archivo" => $uploadedFile->getClientFilename(),
				"peso_archivo" => $rs_peso["peso"],
				"unidad_peso" => $rs_peso["unidad"],
				"formato_archivo" => $tipoFile,
				"directorio" => $directorio,
				"nombre_encriptado" => $filename,
				"usuario_registro" => $this->session::get("idusuario")
			];
			Archivos::NuevoRegistro($data);
			$tmp = Archivos::ObtenerRegistroByArray($data);
			return $tmp;
		}
	}

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