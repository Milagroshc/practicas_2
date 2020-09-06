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
use App\Modelo\Usuario;
use App\Modelo\UsuarioRol;
use App\Modelo\Modulos;
use App\Modelo\Accesos;
use App\Modelo\Login;
use App\Modelo\Sistema;
use App\Modelo\Objacceso;
use App\Modelo\Control;
use App\Modelo\Docadjunto;
use App\Modelo\LogConexion;

// -----------------------------------------

class LoginControlador{
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

	// CARGAR EL FORMULARIO DE INGRESO
	public function index(Request $request, Response $response, $args) {
		$this->view->render($response, 'negocio/login.twig');
		return $response;
	}

	// VERFIFICAR LAS CREDENCIALES DE ACCESO
	public function Login(Request $request, Response $response, $args) {
		$datos = $request->getParsedBody();
		$usuario = $datos["usuario"];
		$password = $datos["password"];

		$password = Hash::hash($password);

		$vrf_usuario = ["USUARIO" => $usuario];
		$tmp = Usuario::VerificarLogin($vrf_usuario);

		if ($tmp["success"]) {
			$vrf_password = [
				"USUARIO" => $usuario,
				"CLAVE" => $password
			];
			$tmp2 = Usuario::VerificarLogin($vrf_password);

			if ($tmp2["success"]) {
				$vrf_baja = [
					"USUARIO" => $usuario,
					"CLAVE" => $password,
					"BAJA" => 0
				];
				$tmp3 = Usuario::VerificarLogin($vrf_baja);

				if ($tmp3["success"]) {
					$vrf_estado = [
						"USUARIO" => $usuario,
						"CLAVE" => $password,
						"ESTADO" => 1
					];
					$tmp4 = Usuario::VerificarLogin($vrf_estado);

					if ($tmp4["success"]) {
						$datin["IDUSUARIO"] = $tmp4["data"]["IDUSUARIO"];
						//CREAR TOKEND Y ENVIAR
						$codificar=$tmp4["data"]["USUARIO"].$tmp4["data"]["CLAVE"].date("d/m/y");
						$token = Hash::hash($codificar);
						//c2356069e9d1e79ca924378153cfbbfb4d4416b1f99d41a2940bfdb66c5319db
						$hash="daf8362006f1d5843533349a0f3786201af5be4ba7d629048259ab705a467a4c";
						$v=Hash::hash_equals($token, $hash);
						//verifica si hay sesiones abiertas
						$sesiones = Login::verificaSesionAbierta($tmp4["data"]["IDUSUARIO"]);

						//guardar el login

						if ($sesiones["success"]) {
							# sesion activa no es necesario crea
							$login = Login::find($sesiones["data"][0]["IDLOGIN"]);
							$login->NRO_INGRESOS=$login->NRO_INGRESOS+1;
							$login->update();
						}else{
							$login = new Login;
							$login->ACCESS_TOKEN=$token;
							$login->EXPIRES_IN=3600;
							$login->ESTADO=1;
							$login->TIPO_ACCESO="Normal";
							$login->IDUSUARIO=$tmp4["data"]["IDUSUARIO"];
							$login->save();
						}


//se crea las sesiones
LoginControlador::loginSesion($tmp4["data"]["IDUSUARIO"]);
//fin sesion
	if (array_key_exists('tipo', $datos)) {
		if ($datos["tipo"]=='app') {
			$datin["loginApp"]=LoginControlador::loginDatosApp($tmp4["data"]["IDUSUARIO"]);
		}
	}
						$datin["IDUSUARIO"] = $tmp4["data"]["IDUSUARIO"];
						$mensaje = "Acceso Correcto";
						$estado = true;
						$clase = "alert-success";
					} else {
						$mensaje = "El USUARIO se encuentra DESHABILITADO. Contacte con el Administrador General del Sistema.";
						$estado = false;
						$clase = "alert-warning";
					}
				} else {
					$mensaje = "USUARIO DADO DE BAJA. Contacte con el Administrador General del Sistema.";
					$estado = false;
					$clase = "alert-warning";
				}
			} else {
				$mensaje = "La CONTRASEÑA ingresada es INCORRECTA.";
				$estado = false;
				$clase = "alert-danger";
			}
		} else {
			$mensaje = "El USUARIO ingresado NO EXISTE.";
			$estado = false;
			$clase = "alert-danger";
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		$datin["clase"] = $clase;
		return LoginControlador::RespuestaJSON($response,$datin);
	}








	// INICIAR LA VARIABLE DE SESION DEL USUARIO
	public static function loginSesion($idUsuario) {

		$tmp = Usuario::getDatosSesion($idUsuario);

		if ($tmp["success"]) {
			$session = new \App\Helper\Session;
			$session::set('USUARIO', $tmp["data"]["USUARIO"]);
			$session::set('TOKEN', $tmp["data"]["ACCESS_TOKEN"]);
			$session::set('NOMBRES', $tmp["data"]["NOMBRES"]);
			$session::set('FOTO', $tmp["data"]["FOTO"]);
			$mensaje = "Sesión Iniciada Correctamente.";
			$estado = true;
		} else {
			$mensaje = "Error al crear la Variable de Sesión.";
			$estado = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		return $datin;
	}

	// INICIAR LA VARIABLE DE SESION DEL USUARIO
	public static function loginDatosApp($idUsuario) {
		return Usuario::getDatosApp($idUsuario);
	}



	// OBTENER LOS ROLES DEL USUARIO
	public function getRolesPorUsuario(Request $request, Response $response, $args) {
		$datos = $request->getParsedBody();
		$idusuario = $datos["idusuario"];
		$tipo = $datos["tipo"];
		$pag = $datos["pag"];
		$data = UsuarioRol::getRolesPorUsuario($idusuario,$tipo,$pag);
		return LoginControlador::RespuestaJSON($response,$data);
	}




	// INICIAR LA VARIABLE DE SESION DEL USUARIO
	public function IniciarSesion(Request $request, Response $response, $args) {
		$datos = $request->getParsedBody();
		$idusuario = $datos["idusuario"];
		$idrol = $datos["idrol"];

		$tmp = Usuario::getDatosSesion($idusuario);
		if ($tmp["success"]) {
			$session = new \App\Helper\Session;
			$session::set('idrol', $idrol);
			$session::set('idusuario', $tmp["data"]["idusuario"]);
			$session::set('username', $tmp["data"]["username"]);
			$session::set('password', $tmp["data"]["password"]);
			$session::set('primera_conexion', $tmp["data"]["primera_conexion"]);
			$session::set('idempleado', $tmp["data"]["idempleado"]);
			$session::set('cod_planilla', $tmp["data"]["cod_planilla"]);
			$session::set('foto', $tmp["data"]["foto"]);
			$session::set('correo_institucional', $tmp["data"]["correo_institucional"]);
			$session::set('id_unidad_organica', $tmp["data"]["id_unidad_organica"]);
			$session::set('nombre_unidad_organica', $tmp["data"]["nombre_unidad_organica"]);
			$session::set('siglas', $tmp["data"]["siglas"]);
			$session::set('idpersona', $tmp["data"]["idpersona"]);
			$session::set('id_tipo_doc', $tmp["data"]["id_tipo_doc"]);
			$session::set('tipo_doc', $tmp["data"]["tipo_doc"]);
			$session::set('num_doc', $tmp["data"]["num_doc"]);
			$session::set('nombres', $tmp["data"]["nombres"]);
			$session::set('ape_paterno', $tmp["data"]["ape_paterno"]);
			$session::set('ape_materno', $tmp["data"]["ape_materno"]);
			$session::set('menucolap', false);
			$session::set('menu', LoginControlador::menuIzquierda());
			$datin["baseurl"] = Constante::DOMAINSITE;
			$mensaje = "Sesión Iniciada Correctamente.";
			$estado = true;
		} else {
			$mensaje = "Error al crear la Variable de Sesión.";
			$estado = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["success"] = $estado;
		return LoginControlador::RespuestaJSON($response,$datin);
	}

	// INICIAR LOS MODULOS PARA EL USUARIO Y ROL
	public function ListarModulos(Request $request, Response $response, $args) {
		$idusuario = $this->session::get("idusuario");
		$idrol = $this->session::get("idrol");
		$modulos = Modulos::getModulos($idusuario,$idrol);
		return LoginControlador::RespuestaJSON($response,$modulos);
	}

	// INICIAR LOS ACCESOS PARA EL USUARIO, ROL Y MODULO
	public function ListarAccesos(Request $request, Response $response, $args) {
		$datos = $request->getParsedBody();
		$idusuario = $this->session::get("idusuario");
		$idrol = $this->session::get("idrol");
		$idmodulo = $datos["idmodulo"];
		$accesos = Accesos::getAccesos($idusuario,$idrol,$idmodulo);
		return LoginControlador::RespuestaJSON($response,$accesos);
	}

		// CREAR MENÚ
		public function menuIzquierda() {
			$idusuario = $this->session::get("idusuario");
			$idrol = $this->session::get("idrol");
			$modulos = Modulos::getModulos($idusuario,$idrol);
			$menu=Array();
			foreach ($modulos["data"] as $key => $modulo) {
				$idmodulo=$modulo["idmodulo"];
				$accesos = Accesos::getAccesos($idusuario,$idrol,$idmodulo);
				$modulo["accesos"]=$accesos["data"];
				$menu[$key]=$modulo;
			}
			return $menu;
		}

	// INICIAR VISTA DEL DARSHBOARD PRINCIPAL
	public function Dashboard(Request $request, Response $response, $args) {


		//var_dump(Modulos::sistemas());
		//$modulos = Modulos::sistemas();
		//$modulos = Modulos::where('IDMODULO', 7)->with('sistemas')->get()->toArray();
		$datos = [1,3];
		$obj = Objacceso::whereIn('IDOBJACCESO', $datos)->with('modulos')->get()->toArray();
		$sis = Sistema::select()->with('modulos')->get()->toArray();
		$mod = Sistema::whereIn('IDSISTEMA', $datos)->with('moduls')->get()->toArray();
		//extrae el modulo con su padre sistemas
		//var_dump($modulos->toSql());
		//var_dump($modulos);
		//var_dump(Sistema::modulos());
		//$sistemas = Sistema::modulos();
		//$questions = Sistema::find(1)->with('modulos')->get()->toArray();
		//var_dump($sistemas->toSql());
		//var_dump($sis[0]["modulos"]);
		//var_dump($mod[0]["moduls"]);
		//var_dump($sis[0]["modulos"][5]);
	//exit;
	/*	foreach($sistemas as $sistema){
			echo "sss";
		echo $sistema->IDSISTEMA;
		echo "algo mas";
		 }
exit;*/
//extraer los datos de control y las imagesetthickness

$datos["controles"] = Control::ListarControl()["data"];
$datos["loges"] = LogConexion::ListarLogConexion()["data"];
foreach ($datos["controles"] as $key => $control) {
	$fotos = json_decode($control["FOTOS"]);
	$idFotos = Array();
	foreach ($fotos as $keye => $foto) {
		$idFotos[$keye] = $foto->idDocAdjunto;
	}
	$datos["controles"][$key] += [ "IMAGENES" =>  Docadjunto::getFotosApp($idFotos)["data"] ];

}


		$datos["datos"] = [
			"title" => "CMS",
			//"saludo" => "Bienvenido ".$nombres." ".$ape_paterno." ".$ape_materno,
			"titulo" => "BIENVENIDO A LA ADMINISTRACIÓN DEL CMS",
			//"idusuario" => $idusuario,
			//"idempleado" => $idempleado,
			//"id_unidad_organica" => $id_unidad_organica
		];
		$this->view->render($response, 'negocio/dashboard.twig', $datos);
		return $response;
	}

	// CERRAR SESION
	public function CerrarSesion(Request $request, Response $response, $args) {
		$this->session->destroy();
		return $response->withRedirect("ingresar");
	}

// ***************************************************************************** ACCIONES EXTRAS *****************************************************************************
	// CAMBIAR LA CONTRASEÑA DEL USUARIO
	public function CambiarPassword(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idusuario = $this->session::get("idusuario");
			$tipo = $datos["tipo"];
			$pass = $datos["pass"];
			$pass = Hash::hash($pass);
			Usuario::CambiarPassword($idusuario,$pass);

			$mensaje = "La contraseña se cambio correctamente.";
			if ($tipo == "primeraconexion") {
				Usuario::UpdatePrimeraConexion($idusuario);
				$mensaje = "El primer ingreso se completó correctamente.";
			}
			$estado = true;
		} catch (ErrorException $e) {
			$mensaje = "Hubo un error al cambiar la contraseña.";
			$estado = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["estado"] = $estado;
		return LoginControlador::RespuestaJSON($response,$datin);
	}

	// MENU COLAPSADO
	public function btnMenu(Request $request, Response $response, $args) {
		$menucolap = $this->session::get("menucolap");
		if ($menucolap) {
			$menucolap = false;
		} else {
			$menucolap = true;
		}
		$this->session::set('menucolap',$menucolap);
		return LoginControlador::RespuestaJSON($response,$menucolap);
	}

	// CAMBIAR IDROL DE LA VARIABLE DE SESION
	public function CambiarSesionRol(Request $request, Response $response, $args) {
		try {
			$datos = $request->getParsedBody();
			$idusuario = $this->session::get("idusuario");
			$newidrol  = $datos["newidrol"];
			$oldidrol = $this->session::get("idrol");

			if ($oldidrol != $newidrol) {
				// ACTUALIZAMOS LAS VARIABLES DE SESION
				$this->session::set("idrol",$newidrol);
				$mensaje = "Se cambio el ROL de la sesión.";
				$accion = true;
			} else {
				$mensaje = "No se cambio el ROL de la sesión.";
				$accion = false;
			}
			$estado = true;
		} catch (ErrorException $e) {
			$mensaje = "Error -> ".$e;
			$estado = false;
			$accion = false;
		}

		$datin["mensaje"] = $mensaje;
		$datin["estado"] = $estado;
		$datin["accion"] = $accion;
		return LoginControlador::RespuestaJSON($response,$datin);
	}

// ****************************************************************************************************************************************************************************
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
