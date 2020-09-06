<?php
use Slim\Http\Request;
use Slim\Http\Response;
use App\Helper\Constante;

use Psr\Log\LoggerInterface;
use Illuminate\Database\Query\Builder;
use App\Helper\Hash;
use App\Helper\Acl;
use App\Helper\JsonRequest;
use App\Helper\JsonRenderer;
use App\Helper\Session;


/*extraer ruta de contenidos*/
use App\Modelo\Contenido;

$app->getContainer()->get("db");



$app->group('/cms', function () use ($app) {
	$app->group('/noticias', function () use ($app) {
		$data = Contenido::ListarRutasContenido();
$routes = $data["data"];
//var_dump($routes);
		foreach ($routes as $route) {
			$nombre = $route["url"];
			//var_dump($nombre);
			$app->get('/'.$nombre, 'App\Controlador\EmmsaControlador:index')->setName($nombre);
		}
});
});
/*fin*/

/*Rutas para la Web emmsa*/
$app->get('/', 'App\Controlador\EmmsaControlador:index')->setName('index');
$app->get('/normas', 'App\Controlador\EmmsaControlador:normas')->setName('normas');
$app->get('/nosotros', 'App\Controlador\EmmsaControlador:contenido')->setName('nosotros');
$app->get('/estadistica', 'App\Controlador\EmmsaControlador:estadistica')->setName('estadistica');
$app->get('/estadistica-gestion/{accion}', 'App\Controlador\EmmsaControlador:estadisticaGestion')->setName('estadistica');
/*Fin emmsa*/

//$app->get('/', 'App\Controlador\PruebaControlador:index')->setName('index');
// $app->get('/{url}', 'App\Controlador\PruebaControlador:indexPagina')->setName('{url}');

// ******************************************************************** RUTAS DE ACCESO A LA PLATAFORMA *************************************************************************
$app->get('/ingresar', 'App\Controlador\LoginControlador:index')->setName('ingresar');
$app->post('/login', 'App\Controlador\LoginControlador:Login')->setName('login');
$app->post('/get-roles-usuario', 'App\Controlador\LoginControlador:getRolesPorUsuario')->setName('get-roles-usuario');
$app->post('/iniciar-sesion', 'App\Controlador\LoginControlador:IniciarSesion')->setName('iniciar-sesion');
$app->get('/dashboard', 'App\Controlador\LoginControlador:Dashboard')->setName('dashboard');
$app->post('/get-modulos', 'App\Controlador\LoginControlador:ListarModulos')->setName('get-modulos');
$app->post('/get-menu', 'App\Controlador\LoginControlador:ListarAccesos')->setName('get-menu');
$app->post('/cambiarPass', 'App\Controlador\LoginControlador:CambiarPassword')->setName('cambiarPass');
$app->post('/cambiarSesionRol', 'App\Controlador\LoginControlador:CambiarSesionRol')->setName('cambiarSesionRol');
$app->post('/btnMenu', 'App\Controlador\LoginControlador:btnMenu')->setName('btnMenu');
$app->get('/salir', 'App\Controlador\LoginControlador:CerrarSesion')->setName('salir');
// ****************************************************************************************************************************************************************************************************************************

// ********************************************************************************************* GRUPO PRINCIPAL PARA EL CMS **************************************************************************************************
$app->group('/cms', function () use ($app) {
	$app->group('/usuarios', function () use ($app) {
		// VISTA USUARIOS
		$app->get('/', 'App\Controlador\UsuarioControlador:indexUsuarios')->setName('');
		$app->post('/listar-usuarios', 'App\Controlador\UsuarioControlador:ListarUsuarios')->setName('listar-usuarios');
		$app->post('/filtrar-usuarios', 'App\Controlador\UsuarioControlador:FiltrarUsuarios')->setName('filtrar-usuarios');
		$app->post('/verificar-existencia-usuario', 'App\Controlador\UsuarioControlador:DatosUsuarioByUsername')->setName('verificar-existencia-usuario');
		$app->post('/verificar-existencia-numdoc', 'App\Controlador\UsuarioControlador:DatosPersonaByTipoNumDocumento')->setName('verificar-existencia-numdoc');
		$app->post('/verificar-existencia-empleado', 'App\Controlador\UsuarioControlador:DatosEmpleadoByIdPersona')->setName('verificar-existencia-empleado');
		$app->post('/nuevo-usuario', 'App\Controlador\UsuarioControlador:NuevoUsuario')->setName('nuevo-usuario');
		$app->post('/usuario-by-id', 'App\Controlador\UsuarioControlador:DatosUsuarioById')->setName('usuario-by-id');
		$app->post('/editar-usuario', 'App\Controlador\UsuarioControlador:EditarUsuario')->setName('editar-usuario');
		$app->post('/cambiar-estado-usuario', 'App\Controlador\UsuarioControlador:CambiarEstadoUsuario')->setName('cambiar-estado-usuario');
		$app->post('/reiniciar-password', 'App\Controlador\UsuarioControlador:ReiniciarPassword')->setName('reiniciar-password');
		$app->post('/baja-usuario', 'App\Controlador\UsuarioControlador:BajaUsuario')->setName('baja-usuario');
		$app->post('/cambiar-estado-usuario-rol', 'App\Controlador\UsuarioControlador:CambiarEstadoUsuarioRol')->setName('cambiar-estado-usuario-rol');
		$app->post('/asignar-rol', 'App\Controlador\UsuarioControlador:AsignarRolUsuario')->setName('asignar-rol');
	});

	$app->group('/roles', function () use ($app) {
		// VISTA ROLES
		$app->get('/', 'App\Controlador\UsuarioControlador:indexRoles')->setName('');
		$app->post('/listar-roles', 'App\Controlador\UsuarioControlador:ListarRoles')->setName('listar-roles');
		$app->post('/filtrar-roles', 'App\Controlador\UsuarioControlador:FiltrarRoles')->setName('filtrar-roles');
		$app->post('/nuevo-rol', 'App\Controlador\UsuarioControlador:NuevoRol')->setName('nuevo-rol');
		$app->post('/rol-by-id', 'App\Controlador\UsuarioControlador:DatosRolById')->setName('rol-by-id');
		$app->post('/editar-rol', 'App\Controlador\UsuarioControlador:EditarRol')->setName('editar-rol');
		$app->post('/cambiar-estado-rol', 'App\Controlador\UsuarioControlador:CambiarEstadoRol')->setName('cambiar-estado-rol');
		$app->post('/set-id-rol', 'App\Controlador\UsuarioControlador:SetIdRol')->setName('set-id-rol');
	});

	$app->group('/accesos', function () use ($app) {
		// VISTA ACCESOS
		$app->get('/', 'App\Controlador\UsuarioControlador:indexAccesos')->setName('');
		$app->post('/listar-modulos', 'App\Controlador\UsuarioControlador:ListarModulos')->setName('listar-modulos');
		$app->post('/listar-acceso-by-id-rol', 'App\Controlador\UsuarioControlador:ListarAccesoByIdRol')->setName('listar-acceso-by-id-rol');
		$app->post('/asignar-acceso', 'App\Controlador\UsuarioControlador:AsignarAccesoRol')->setName('asignar-acceso');
		$app->post('/validar-acceso-habilitado', 'App\Controlador\UsuarioControlador:ValidarAccesoHabilitado')->setName('validar-acceso-habilitado');
		$app->post('/listar-acciones-by-acceso', 'App\Controlador\UsuarioControlador:ListarAccionesByIdAcceso')->setName('listar-acciones-by-acceso');
		$app->post('/aginar-accion', 'App\Controlador\UsuarioControlador:AsignarAccionAcceso')->setName('aginar-accion');
	});

	$app->group('/menu-superior', function () use ($app) {
		// VISTA MENU SUPERIOR
		$app->get('/', 'App\Controlador\ContenidoControlador:indexMenuSuperior')->setName('');
		$app->post('/listar-contenido', 'App\Controlador\ContenidoControlador:ListarContenido')->setName('listar-contenido');
	});
});