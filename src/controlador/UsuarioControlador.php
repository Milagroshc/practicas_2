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
use App\Modelo\ItemModulo;
use App\Modelo\Catalogotablas;

// -----------------------------------------

class UsuarioControlador{
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
		echo "estoy en usuario";
	}

		/*crear usuario*/
		public function validaUsuarioAjax(Request $request, Response $response, $args){
			try {
				$datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
				$datin["usuario"]=Usuario::getUsuarioByUser($datos);	   
				$mensaje ="Su consulta de validación es correcta";
				$estado = true;
			} catch (\ErrorException $e) {
				$mensaje="Algo no salio muy bien";
				$estado=false;
			}
			   $datin["mensaje"]= $mensaje;
			   $datin["success"] = $estado;
			$obj1 = JsonRenderer::render($response,200,$datin);
			return $obj1;
		}

		// GUARDAR NUEVO USUARIO
	public function nuevoUsuarioAjax(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();

			$usuario = new Usuario;
			$usuario->USUARIO = $datos["USUARIO"];
			$usuario->CLAVE = $datos["CLAVE"];
			//objeto persona
			$persona = new Persona;
		/*	$persona->NOMBRES = $datos["NOMBRES"];
			$persona->APELLIDO_PATERNO = $datos["APELLIDO_PATERNO"];
			$persona->APELLIDO_MATERNO = $datos["APELLIDO_MATERNO"];
		*/
		$persona->setRawAttributes($datos);
		var_dump($persona->getMorphClass());
		var_dump($persona->push());
		var_dump($persona->getAttributes());
		exit;
		/*var_dump($persona->setRawAttributes($datos));
		var_dump(get_object_vars($persona)); */

			$tmp_a = Usuario::DatosUsuarioByUsername($usuario->USUARIO);
			//verifico si el usuario existe
			if ($tmp_a["success"]) { //si existe
				$mensaje = "El username ".$usuario->USUARIO." ya existe, ingrese uno diferente.";
				$estado = false;
			} else { //no existe
				$tmp_b = Persona::BuscarNumDoc($usuario_tipodoc_nuevo,$usuario_numdoc_nuevo);
				if ($tmp_b["success"]) {
					$idpersona = $tmp_b["data"]["idpersona"];
					$tmp_c = Empleado::DatosEmpleadoByIdPersona($idpersona);
					if ($tmp_c["success"]) {
						$idempleado = $tmp_c["data"]["idempleado"];
						$tmp_d = Usuario::DatosUsuarioByIdEmpleado($idempleado);
						if ($tmp_d["success"]) {
							$mensaje = "El empleado con ".$tmp_d["data"]["tipo_doc"]." Nª ".$tmp_d["data"]["num_doc"]." ya tiene un USUARIO creado, es cual es: ".$tmp_d["data"]["username"];
							$estado = false;
						} else {
							$data_usuario = [
								"idempleado" => $idempleado,
								"username" => $usuario_username_nuevo
							];
							Usuario::NuevoRegistro($data_usuario);
							$tmp_d = Usuario::DatosUsuarioByUsername($usuario_username_nuevo);
							if ($tmp_d["success"]) {
								$mensaje = "El USUARIO se registró correctamente.";
								$estado = true;
							} else {
								$mensaje = "No se pudo Guardar el USUARIO. Contacte con el Administrador del Sistema.";
								$estado = false;
							}
						}
					} else {
						$data_empleado = [
							"idpersona" => $idpersona,
							"id_unidad_organica" => $usuario_unidadorganica_nuevo,
							"cod_planilla" => $usuario_codplanilla_nuevo,
							"correo_institucional" => $usuario_correoinsti_nuevo
						];
						Empleado::NuevoRegistro($data_empleado);
						$tmp_e = Empleado::DatosEmpleadoByIdPersona($idpersona);
						if ($tmp_e["success"]) {
							$idempleado = $tmp_e["data"]["idempleado"];
							$data_usuario = [
								"idempleado" => $idempleado,
								"username" => $usuario_username_nuevo
							];
							Usuario::NuevoRegistro($data_usuario);
							$tmp_f = Usuario::DatosUsuarioByUsername($usuario_username_nuevo);
							if ($tmp_f["success"]) {
								$mensaje = "El USUARIO se registró correctamente.";
								$estado = true;
							} else {
								$mensaje = "No se pudo Guardar el USUARIO. Contacte con el Administrador del Sistema.";
								$estado = false;
							}
						} else {
							$mensaje = "No se pudo Guardar los datos del EMPLEADO. Contacte con el Administrador del Sistema.";
							$estado = false;
						}
					}
				} else {
					$data_persona = [
						"tipo_doc" => $usuario_tipodoc_nuevo,
						"num_doc" => $usuario_numdoc_nuevo,
						"nombres" => $usuario_nombres_nuevo,
						"ape_paterno" => $usuario_apepat_nuevo,
						"ape_materno" => $usuario_apemat_nuevo
					];
					Persona::NuevoRegistro($data_persona);
					$tmp_g = Persona::BuscarNumDoc($usuario_tipodoc_nuevo,$usuario_numdoc_nuevo);
					if ($tmp_g["success"]) {
						$idpersona = $tmp_g["data"]["idpersona"];
						$data_empleado = [
							"idpersona" => $idpersona,
							"id_unidad_organica" => $usuario_unidadorganica_nuevo,
							"cod_planilla" => $usuario_codplanilla_nuevo,
							"correo_institucional" => $usuario_correoinsti_nuevo
						];
						Empleado::NuevoRegistro($data_empleado);
						$tmp_h = Empleado::DatosEmpleadoByIdPersona($idpersona);
						if ($tmp_h["success"]) {
							$idempleado = $tmp_h["data"]["idempleado"];
							$data_usuario = [
								"idempleado" => $idempleado,
								"username" => $usuario_username_nuevo
							];
							Usuario::NuevoRegistro($data_usuario);
							$tmp_i = Usuario::DatosUsuarioByUsername($usuario_username_nuevo);
							if ($tmp_i["success"]) {
								$mensaje = "El USUARIO se registró correctamente.";
								$estado = true;
							} else {
								$mensaje = "No se pudo Guardar el USUARIO. Contacte con el Administrador del Sistema.";
								$estado = false;
							}
						} else {
							$mensaje = "No se pudo Guardar los datos del EMPLEADO. Contacte con el Administrador del Sistema.";
							$estado = false;
						}
					} else {
						$mensaje = "No se pudo Guardar los datos de la PERSONA. Contacte con el Administrador del Sistema.";
						$estado = false;
					}
				}
			}
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al cambiar la contraseña.";
			$estado = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		return UsuarioControlador::RespuestaJSON($response,$datin);
	}




	/*crear usuario*/
	public function crearUsuario(Request $request, Response $response, $args){
		$datin["tipoDocumentos"]= Catalogotablas::getTablaTablasByIDS(Constante::ID_TIPODOCUMENTOS);
		$datin["parametros"] = ['title'=>"Registrar usuario :: negocio", 'titulo' => "Registrar Usuario" ];
		$this->view->render($response, 'negocio/registrar.twig', $datin);
		return $response;
	}

	// RETORNA DATOS DEL USUARIO EN CASO EXISTA
	public function DatosUsuarioByUsername(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$username = $datos["username"];
		$data = Usuario::DatosUsuarioByUsername($username);
		return UsuarioControlador::RespuestaJSON($response,$data);
	}

	// BUSCAR DNI EN LA BD
	public function DatosPersonaByTipoNumDocumento(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$tipodoc = $datos["tipodoc"];
		$numdoc = $datos["numdoc"];
		$data = Persona::BuscarNumDoc($tipodoc,$numdoc);
		return UsuarioControlador::RespuestaJSON($response,$data);
	}

	// RETORNA DATOS DEL EMPLEADO EN CASO EXISTA
	public function DatosEmpleadoByIdPersona(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$idpersona = $datos["idpersona"];
		$data = Empleado::DatosEmpleadoByIdPersona($idpersona);
		return UsuarioControlador::RespuestaJSON($response,$data);
	}

	// RETORNA DATOS DEL EMPLEADO EN CASO EXISTA
	public function DatosUsuarioById(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$idusuario = $datos["idusuario"];
		$data = Usuario::DatosUsuarioById($idusuario);
		return UsuarioControlador::RespuestaJSON($response,$data);
	}

	// RETORNA DATOS DEL EMPLEADO EN CASO EXISTA
	public function DatosRolById(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$idrol = $datos["idrol"];
		$data = Rol::DatosRolById($idrol);
		return UsuarioControlador::RespuestaJSON($response,$data);
	}

// ******************************************************************************* MANTENIMIENTO DE USUARIOS *****************************************************************************
	// INICIAR VISTA DE MANTENIMIENTO DE USUARIOS
	public function indexUsuarios(Request $request, Response $response, $args){
		$idrol = $this->session::get("idrol");
		$id_item_modulo = 1; // ID EN LA TABLA -> item_modulos
		$titulo = "MANTENIMIENTO DE USUARIOS";
		$tipodoc = Parametros::getParametros(1);
		$estructura_organica = UnidadOrganica::getEstructuraOrganica();
		$acciones = Acciones::DatosAccionesByIdItemModulo($id_item_modulo);
		$tmp_acceso = Accesos::DatosAccesoByIds($idrol,$id_item_modulo);
		$idacceso = $tmp_acceso["data"]["idacceso"];
		$acceso_acciones = AccesoAcciones::DatosByIdAcceso($idacceso);
		$datos = [
			"titulo" => $titulo,
			"tipodoc"=> $tipodoc,
			"estructura_organica" => $estructura_organica,
			"acciones" => $acciones,
			"acceso_acciones" => $acceso_acciones
		];
		$this->view->render($response, 'cms/usuarios/usuarios.twig', $datos);
		return $response;
	}

	// LISTAR USUARIOS
	public function ListarUsuarios(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$pag = $datos["pag"];
		$data = Usuario::ListarUsuarios($pag);
		return UsuarioControlador::RespuestaJSON($response,$data);
	}

	// FILTRAR USUARIOS
	public function FiltrarUsuarios(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$pag = $datos["pag"];
		$params = [
			"id_unidad_organica" => $datos["usuario_unidadorganica_buscar"],
			"username" => $datos["usuario_username_buscar"],
			"cod_planilla" => $datos["usuario_codplanilla_buscar"],
			"num_doc" => $datos["usuario_numdoc_buscar"],
			"estado" => $datos["usuario_estado_buscar"]
		];
		$data = Usuario::FiltrarUsuarios($pag,$params);
		return UsuarioControlador::RespuestaJSON($response,$data);
	}

	// GUARDAR NUEVO USUARIO
	public function NuevoUsuario(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();

			$usuario = new Usuario;
			$usuario->USUARIO = $datos["USUARIO"];
			$usuario->CLAVE = $datos["CLAVE"];
			//objeto persona
			$persona = new Persona;
			$persona->NOMBRES = $datos["NOMBRES"];
			$persona->APELLIDO_PATERNO = $datos["APELLIDO_PATERNO"];
			$persona->APELLIDO_MATERNO = $datos["APELLIDO_MATERNO"];
			

			$tmp_a = Usuario::DatosUsuarioByUsername($usuario->USUARIO);
			//verifico si el usuario existe
			if ($tmp_a["success"]) { //si existe
				$mensaje = "El username ".$usuario->USUARIO." ya existe, ingrese uno diferente.";
				$estado = false;
			} else { //no existe
				$tmp_b = Persona::BuscarNumDoc($usuario_tipodoc_nuevo,$usuario_numdoc_nuevo);
				if ($tmp_b["success"]) {
					$idpersona = $tmp_b["data"]["idpersona"];
					$tmp_c = Empleado::DatosEmpleadoByIdPersona($idpersona);
					if ($tmp_c["success"]) {
						$idempleado = $tmp_c["data"]["idempleado"];
						$tmp_d = Usuario::DatosUsuarioByIdEmpleado($idempleado);
						if ($tmp_d["success"]) {
							$mensaje = "El empleado con ".$tmp_d["data"]["tipo_doc"]." Nª ".$tmp_d["data"]["num_doc"]." ya tiene un USUARIO creado, es cual es: ".$tmp_d["data"]["username"];
							$estado = false;
						} else {
							$data_usuario = [
								"idempleado" => $idempleado,
								"username" => $usuario_username_nuevo
							];
							Usuario::NuevoRegistro($data_usuario);
							$tmp_d = Usuario::DatosUsuarioByUsername($usuario_username_nuevo);
							if ($tmp_d["success"]) {
								$mensaje = "El USUARIO se registró correctamente.";
								$estado = true;
							} else {
								$mensaje = "No se pudo Guardar el USUARIO. Contacte con el Administrador del Sistema.";
								$estado = false;
							}
						}
					} else {
						$data_empleado = [
							"idpersona" => $idpersona,
							"id_unidad_organica" => $usuario_unidadorganica_nuevo,
							"cod_planilla" => $usuario_codplanilla_nuevo,
							"correo_institucional" => $usuario_correoinsti_nuevo
						];
						Empleado::NuevoRegistro($data_empleado);
						$tmp_e = Empleado::DatosEmpleadoByIdPersona($idpersona);
						if ($tmp_e["success"]) {
							$idempleado = $tmp_e["data"]["idempleado"];
							$data_usuario = [
								"idempleado" => $idempleado,
								"username" => $usuario_username_nuevo
							];
							Usuario::NuevoRegistro($data_usuario);
							$tmp_f = Usuario::DatosUsuarioByUsername($usuario_username_nuevo);
							if ($tmp_f["success"]) {
								$mensaje = "El USUARIO se registró correctamente.";
								$estado = true;
							} else {
								$mensaje = "No se pudo Guardar el USUARIO. Contacte con el Administrador del Sistema.";
								$estado = false;
							}
						} else {
							$mensaje = "No se pudo Guardar los datos del EMPLEADO. Contacte con el Administrador del Sistema.";
							$estado = false;
						}
					}
				} else {
					$data_persona = [
						"tipo_doc" => $usuario_tipodoc_nuevo,
						"num_doc" => $usuario_numdoc_nuevo,
						"nombres" => $usuario_nombres_nuevo,
						"ape_paterno" => $usuario_apepat_nuevo,
						"ape_materno" => $usuario_apemat_nuevo
					];
					Persona::NuevoRegistro($data_persona);
					$tmp_g = Persona::BuscarNumDoc($usuario_tipodoc_nuevo,$usuario_numdoc_nuevo);
					if ($tmp_g["success"]) {
						$idpersona = $tmp_g["data"]["idpersona"];
						$data_empleado = [
							"idpersona" => $idpersona,
							"id_unidad_organica" => $usuario_unidadorganica_nuevo,
							"cod_planilla" => $usuario_codplanilla_nuevo,
							"correo_institucional" => $usuario_correoinsti_nuevo
						];
						Empleado::NuevoRegistro($data_empleado);
						$tmp_h = Empleado::DatosEmpleadoByIdPersona($idpersona);
						if ($tmp_h["success"]) {
							$idempleado = $tmp_h["data"]["idempleado"];
							$data_usuario = [
								"idempleado" => $idempleado,
								"username" => $usuario_username_nuevo
							];
							Usuario::NuevoRegistro($data_usuario);
							$tmp_i = Usuario::DatosUsuarioByUsername($usuario_username_nuevo);
							if ($tmp_i["success"]) {
								$mensaje = "El USUARIO se registró correctamente.";
								$estado = true;
							} else {
								$mensaje = "No se pudo Guardar el USUARIO. Contacte con el Administrador del Sistema.";
								$estado = false;
							}
						} else {
							$mensaje = "No se pudo Guardar los datos del EMPLEADO. Contacte con el Administrador del Sistema.";
							$estado = false;
						}
					} else {
						$mensaje = "No se pudo Guardar los datos de la PERSONA. Contacte con el Administrador del Sistema.";
						$estado = false;
					}
				}
			}
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al cambiar la contraseña.";
			$estado = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		return UsuarioControlador::RespuestaJSON($response,$datin);
	}

	// EDITAR USUARIO
	public function EditarUsuario(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idusuario_editar = $datos["idusuario_editar"];
			$idempleado_editar = $datos["idempleado_editar"];
			$idpersona_editar = $datos["idpersona_editar"];
			$usuario_username_editar = $datos["usuario_username_editar"];
			$usuario_tipodoc_editar = $datos["usuario_tipodoc_editar"];
			$usuario_numdoc_editar = $datos["usuario_numdoc_editar"];
			$usuario_nombres_editar = $datos["usuario_nombres_editar"];
			$usuario_apepat_editar = $datos["usuario_apepat_editar"];
			$usuario_apemat_editar = $datos["usuario_apemat_editar"];
			$usuario_codplanilla_editar = $datos["usuario_codplanilla_editar"];
			$usuario_correoinsti_editar = $datos["usuario_correoinsti_editar"];
			$usuario_unidadorganica_editar = $datos["usuario_unidadorganica_editar"];

			$tmp_a = Usuario::DatosUsuarioById($idusuario_editar);
			if ($tmp_a["success"]) {
				if ($tmp_a["data"]["username"] != $usuario_username_editar) {
					$tmp_b = Usuario::DatosUsuarioByUsername($usuario_username_editar);
					if ($tmp_b["success"]) {
						$mensaje = "El username ".$usuario_username_editar." ya existe, ingrese uno diferente.";
						$estado = false;
					} else {
						$data_usuario = ["username" => $usuario_username_editar];
						Usuario::ActualizarRegistro($idusuario_editar,$data_usuario);
						if ($tmp_a["data"]["id_tipo_doc"] != $usuario_tipodoc_editar && $tmp_a["data"]["num_doc"] != $usuario_numdoc_editar) {
							$tmp_c = Persona::BuscarNumDoc($usuario_tipodoc_editar,$usuario_numdoc_editar);
							if ($tmp_c["success"]) {
								$mensaje = "El TIPO DOC. y el Nº DOC. ingresados ya existen, ingrese uno diferente.";
								$estado = false;
							} else {
								$data_persona = [
									"tipo_doc" => $usuario_tipodoc_editar,
									"num_doc" => $usuario_numdoc_editar,
									"nombres" => $usuario_nombres_editar,
									"ape_paterno" => $usuario_apepat_editar,
									"ape_materno" => $usuario_apemat_editar
								];
								Persona::ActualizarRegistro($idpersona_editar,$data_persona);
								$data_empleado = [
									"id_unidad_organica" => $usuario_unidadorganica_editar,
									"cod_planilla" => $usuario_codplanilla_editar,
									"correo_institucional" => $usuario_correoinsti_editar
								];
								Empleado::ActualizarRegistro($idempleado_editar,$data_empleado);
								$mensaje = "Datos Actualizados correctamente.";
								$estado = true;
							}
						} else {
							$data_persona = [
								"nombres" => $usuario_tipodoc_editar,
								"ape_paterno" => $usuario_apepat_editar,
								"ape_materno" => $usuario_apemat_editar
							];
							Persona::ActualizarRegistro($idpersona_editar,$data_persona);
							$data_empleado = [
								"id_unidad_organica" => $usuario_unidadorganica_editar,
								"cod_planilla" => $usuario_codplanilla_editar,
								"correo_institucional" => $usuario_correoinsti_editar
							];
							Empleado::ActualizarRegistro($idempleado_editar,$data_empleado);
							$mensaje = "Datos Actualizados correctamente.";
							$estado = true;
						}
					}
				} else {
					if ($tmp_a["data"]["id_tipo_doc"] == $usuario_tipodoc_editar && $tmp_a["data"]["num_doc"] == $usuario_numdoc_editar) {
						$data_persona = [
							"nombres" => $usuario_nombres_editar,
							"ape_paterno" => $usuario_apepat_editar,
							"ape_materno" => $usuario_apemat_editar
						];
						Persona::ActualizarRegistro($idpersona_editar,$data_persona);
						$data_empleado = [
							"id_unidad_organica" => $usuario_unidadorganica_editar,
							"cod_planilla" => $usuario_codplanilla_editar,
							"correo_institucional" => $usuario_correoinsti_editar
						];
						Empleado::ActualizarRegistro($idempleado_editar,$data_empleado);
						$mensaje = "Datos Actualizados correctamente.";
						$estado = true;
					} else {
						$tmp_d = Persona::BuscarNumDoc($usuario_tipodoc_editar,$usuario_numdoc_editar);
						if ($tmp_d["success"]) {
							$mensaje = "El TIPO DOC. y el Nº DOC. ingresados ya existen, ingrese uno diferente.";
							$estado = false;
						} else {
							$data_persona = [
								"tipo_doc" => $usuario_tipodoc_editar,
								"num_doc" => $usuario_numdoc_editar,
								"nombres" => $usuario_nombres_editar,
								"ape_paterno" => $usuario_apepat_editar,
								"ape_materno" => $usuario_apemat_editar
							];
							Persona::ActualizarRegistro($idpersona_editar,$data_persona);
							$data_empleado = [
								"id_unidad_organica" => $usuario_unidadorganica_editar,
								"cod_planilla" => $usuario_codplanilla_editar,
								"correo_institucional" => $usuario_correoinsti_editar
							];
							Empleado::ActualizarRegistro($idempleado_editar,$data_empleado);
							$mensaje = "Datos Actualizados correctamente.";
							$estado = true;
						}
					}
				}
			}
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al cambiar la contraseña.";
			$estado = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		return UsuarioControlador::RespuestaJSON($response,$datin);
	}

	// CAMBIAR ESTADO DEL USUARIO
	public function CambiarEstadoUsuario(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idusuario = $datos["idusuario"];
			$val = $datos["val"];
			switch ($val) {
				case 'true':
					$estado = 1;
					$mensaje = "USUARIO Habilitado.";
				break;
				case 'false':
					$estado = 0;
					$mensaje = "USUARIO Deshabilitado.";
				break;
			}
			$data = ["estado" => $estado];
			Usuario::ActualizarRegistro($idusuario,$data);
			$success = true;
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al camibar el estado al Usuario -> ".$e;
			$success = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $success;
		return UsuarioControlador::RespuestaJSON($response,$datin);
	}

	// CAMBIAR ESTADO DEL USUARIO-ROL
	public function CambiarEstadoUsuarioRol(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idusuario = $datos["idusuario"];
			$idrol = $datos["idrol"];
			$val = $datos["val"];
			switch ($val) {
				case 'true':
					$estado = 1;
					$mensaje = "ROL Habilitado para el Usuario.";
				break;
				case 'false':
					$estado = 0;
					$mensaje = "ROL Deshabilitado para el Usuario.";
				break;
			}
			$data = ["estado" => $estado];
			UsuarioRol::ActualizarRegistro($idusuario,$idrol,$data);
			$success = true;
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al camibar el estado al Usuario-Rol -> ".$e;
			$success = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $success;
		return UsuarioControlador::RespuestaJSON($response,$datin);
	}

	// REINICIAR EL PASSWORD DE UN USUARIO
	public function ReiniciarPassword(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idusuario = $datos["idusuario"];
			$data = [
				"password" => Hash::hash(123),
				"primera_conexion" => 1
			];
			Usuario::ActualizarRegistro($idusuario,$data);
			$mensaje = "Contraseña Reiniciada Correctamente.";
			$estado = true;
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al reiniciar la contraseña al Usuario -> ".$e;
			$estado = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		return UsuarioControlador::RespuestaJSON($response,$datin);
	}

	// DAR DE BAJA A UN USUARIO
	public function BajaUsuario(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idusuario = $datos["idusuario"];
			$baja = $datos["baja"];
			switch ($baja) {
				case '0':
					$mensaje = "USUARIO Activado.";
				break;
				case '1':
					$mensaje = "USUARIO dado de Baja.";
				break;
			}
			$data = ["baja" => $baja];
			Usuario::ActualizarRegistro($idusuario,$data);
			$estado = true;
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al Activar o dar de Baja a un Usuario -> ".$e;
			$estado = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		return UsuarioControlador::RespuestaJSON($response,$datin);
	}

	// ASIGNAR UN ROL A UN USUARIO
	public function AsignarRolUsuario(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idusuario = $datos["idusuario"];
			$idrol = $datos["idrol"];
			
			$tmp = UsuarioRol::VerificarUsuarioRol($idusuario,$idrol);
			if ($tmp["success"]) {
				$mensaje = "El Rol ya se encuentra Asignado a este Usuario.";
				$estado = false;
			} else {
				$data = [
					"idusuario" => $idusuario,
					"idrol" => $idrol
				];
				UsuarioRol::NuevoRegistro($data);
				$mensaje = "El ROL fue Asignado Correctamente.";
				$estado = true;
			}
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al asignar el Rol al Usuario -> ".$e;
			$estado = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		return UsuarioControlador::RespuestaJSON($response,$datin);
	}

// ******************************************************************************* MANTENIMIENTO DE ROLES *****************************************************************************
	// INICIAR VISTA DE MANTENIMIENTO DE ROLES
	public function indexRoles(Request $request, Response $response, $args){
		$idrol = $this->session::get("idrol");
		$id_item_modulo = 2; // ID EN LA TABLA -> item_modulos
		$titulo = "MANTENIMIENTO DE ROLES";
		$acciones = Acciones::DatosAccionesByIdItemModulo($id_item_modulo);
		$tmp_acceso = Accesos::DatosAccesoByIds($idrol,$id_item_modulo);
		$idacceso = $tmp_acceso["data"]["idacceso"];
		$acceso_acciones = AccesoAcciones::DatosByIdAcceso($idacceso);
		$datos = [
			"titulo" => $titulo,
			"acciones" => $acciones,
			"acceso_acciones" => $acceso_acciones
		];
		$this->view->render($response, 'cms/usuarios/roles.twig', $datos);
		return $response;
	}

	// LISTAR ROLES
	public function ListarRoles(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$pag = $datos["pag"];
		$tipo = $datos["tipo"];
		$data = Rol::ListarRoles($pag,$tipo);
		return UsuarioControlador::RespuestaJSON($response,$data);
	}

	// FILTRAR ROLES
	public function FiltrarRoles(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$pag = $datos["pag"];
		$params = [
			"nombre_rol" => $datos["rol_nombre_buscar"],
			"estado" => $datos["rol_estado_buscar"]
		];
		$data = Rol::FiltrarRoles($pag,$params);
		return UsuarioControlador::RespuestaJSON($response,$data);
	}

	// NUEVO ROL
	public function NuevoRol(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$rol_nombre_nuevo = $datos["rol_nombre_nuevo"];
			$tmp = Rol::DatosRolByNombre($rol_nombre_nuevo);
			if ($tmp["success"]) {
				$mensaje = "El NOMBRE de ROL ya existe, ingrese uno diferente.";
				$estado = false;
			} else {
				$data = ["nombre_rol" => $rol_nombre_nuevo];
				Rol::NuevoRegistro($data);
				$mensaje = "ROL creado correctamente.";
				$estado = true;
			}
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al camibar el estado al Rol -> ".$e;
			$estado = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		return UsuarioControlador::RespuestaJSON($response,$datin);
	}

	// EDITAR ROL
	public function EditarRol(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idrol_editar = $datos["idrol_editar"];
			$rol_nombre_editar = $datos["rol_nombre_editar"];
			$tmp_a = Rol::DatosRolById($idrol_editar);
			if ($tmp_a["success"]) {
				if ($tmp_a["data"]["nombre_rol"] == $rol_nombre_editar) {
					$mensaje = "No Hubo Cambios";
					$estado = true;
				} else {
					$tmp_b = Rol::DatosRolByNombre($rol_nombre_editar);
					if ($tmp_b["success"]) {
						$mensaje = "El NOMBRE de ROL ya existe, ingrese uno diferente.";
						$estado = false;
					} else {
						$data = ["nombre_rol" => $rol_nombre_editar];
						Rol::ActualizarRegistro($idrol_editar,$data);
						$mensaje = "Datos Actualizados Correctamente.";
						$estado = true;
					}
				}
			} else {
				$mensaje = "No se pudo extraer información del ROL.";
				$estado = false;
			}
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al editar el Rol -> ".$e;
			$estado = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		return UsuarioControlador::RespuestaJSON($response,$datin);
	}

	// CAMBIAR ESTADO DEL ROL
	public function CambiarEstadoRol(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idrol = $datos["idrol"];
			$valor = $datos["valor"];
			switch ($valor) {
				case 'true':
					$estado = 1;
					$mensaje = "ROL Habilitado.";
				break;
				case 'false':
					$estado = 0;
					$mensaje = "ROL Deshabilitado.";
				break;
			}
			$data = ["estado" => $estado];
			Rol::ActualizarRegistro($idrol,$data);
			$success = true;
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al camibar el estado al Rol -> ".$e;
			$success = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $success;
		return UsuarioControlador::RespuestaJSON($response,$datin);
	}

	// SETEAR EL IDROL Y EL NOMBRE EN UNA VARIABLE DE SESION
	public function SetIdRol(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idrol = $datos["idrol"];
			$nombre_rol = $datos["nombre_rol"];
			$this->session::set("idrol-accesos",$idrol.'||'.$nombre_rol);
			$mensaje = "ID seteada correctamente.";
			$estado = true;
		} catch (ErrorException $e) {
			$mensaje = "Ocurrió un error al Setear el ID -> ".$e;
			$estado = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		return UsuarioControlador::RespuestaJSON($response,$datin);
	}

// ******************************************************************************* MANTENIMIENTO DE ACCESOS *****************************************************************************
	// INICIAR VISTA DE MANTENIMIENTO DE ACCESOS
	public function indexAccesos(Request $request, Response $response, $args){
		$var = $this->session::get("idrol-accesos");
		$tmp = explode("||",$var);
		$idrol = $tmp[0];
		$nombre_rol = $tmp[1];
		$titulo = "MANTENIMIENTO DE ACCESOS POR ROL";
		$datos = [
			'titulo'=> $titulo,
			'idrol' => $idrol,
			'nombre_rol' => $nombre_rol
		];
		$this->view->render($response, 'cms/usuarios/accesos.twig', $datos);
		return $response;
	}

	// LISTAR MODULOS
	public function ListarModulos(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$data = Modulos::ListarModulos();
		return UsuarioControlador::RespuestaJSON($response,$data);
	}

	// LISTAR ITEM CON ACCESO POR ROL
	public function ListarAccesoByIdRol(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$idrol_actual = $datos["idrol_actual"];
		$data = ItemModulo::ListarItemModuloByAcceso($idrol_actual);
		return UsuarioControlador::RespuestaJSON($response,$data);
	}

	// ASIGNAR UN ACCESO A UN ROL
	public function AsignarAccesoRol(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idrol = $datos["idrol"];
			$id_item_modulo = $datos["id_item_modulo"];
			$valor = $datos["valor"];
			switch ($valor) {
				case 'true':
					$estado = 1;
					$mensaje = "ACCESO Asginado al ROL.";
				break;
				case 'false':
					$estado = 0;
					$mensaje = "ACCESO Removido del ROL.";
				break;
			}

			$tmp = Accesos::DatosAccesoByIds($idrol,$id_item_modulo);
			if ($tmp["success"]) {
				$idacceso = $tmp["data"]["idacceso"];
				$data = ["estado" => $estado];
				Accesos::ActualizarRegistro($idacceso,$data);
				$success = true;
			} else {
				$data = [
					"idrol" => $idrol,
					"id_item_modulo" => $id_item_modulo
				];
				Accesos::NuevoRegistro($data);
				$success = true;
			}
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al asignar el Acceso al Rol -> ".$e;
			$estado = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $success;
		return UsuarioControlador::RespuestaJSON($response,$datin);
	}

	// VALIDAR SI EL ACCESO ESTÁ ASIGNADO O HABILITADO EN UN ROL
	public function ValidarAccesoHabilitado(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idrol_actual = $datos["idrol_actual"];
			$id_item_modulo = $datos["id_item_modulo"];

			$tmp = Accesos::DatosAccesoByIds($idrol_actual,$id_item_modulo);
			if ($tmp["success"]) {
				$estado = $tmp["data"]["estado"];
				if ($estado == 1) {
					$success = true;
				} else {
					$success = false;
				}
			} else {
				$success = false;
			}
		} catch (ErrorException $e) {
			$estado = false;
		}

		$datin["success"] = $success;
		return UsuarioControlador::RespuestaJSON($response,$datin);
	}

	// LISTAR ACCIONES DE CADA ITEM MODULO POR ROL
	public function ListarAccionesByIdAcceso(Request $request, Response $response, $args){
		$datos = $request->getParsedBody();
		$idrol_actual = $datos["idrol_actual"];
		$id_item_modulo = $datos["id_item_modulo"];
		$tmp_acceso = Accesos::DatosAccesoByIds($idrol_actual,$id_item_modulo);
		$idacceso = $tmp_acceso["data"]["idacceso"];
		$data = Acciones::ListarAccionesByIdAcceso($idacceso,$id_item_modulo);
		return UsuarioControlador::RespuestaJSON($response,$data);
	}

	// ASIGNAR UNA ACCION A UN ACCESO
	public function AsignarAccionAcceso(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idrol = $datos["idrol"];
			$id_item_modulo = $datos["id_item_modulo"];
			$idaccion = $datos["idaccion"];
			$valor = $datos["valor"];
			switch ($valor) {
				case 'true':
					$estado = 1;
					$mensaje = "ACCIÓN Asginado al ACCESO.";
				break;
				case 'false':
					$estado = 0;
					$mensaje = "ACCIÓN Removido del ACCESO.";
				break;
			}

			$tmp_a = Accesos::DatosAccesoByIds($idrol,$id_item_modulo);
			if ($tmp_a["success"]) {
				$idacceso = $tmp_a["data"]["idacceso"];
				$tmp_b = AccesoAcciones::VerificarAccesoAccion($idacceso,$idaccion);
				if ($tmp_b["success"]) {
					$data = ["estado" => $estado];
					AccesoAcciones::ActualizarRegistro($idacceso,$idaccion,$data);
					$success = true;
				} else {
					$data = [
						"idacceso" => $idacceso,
						"idaccion" => $idaccion
					];
					AccesoAcciones::NuevoRegistro($data);
					$success = true;
				}
			} else {
				$mensaje = "No se pudo obtener el ID Acceso.";
				$success = false;
			}
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al asignar la Acción al Acceso -> ".$e;
			$estado = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $success;
		return UsuarioControlador::RespuestaJSON($response,$datin);
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