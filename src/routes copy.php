<?php
use Slim\Http\Request;
use Slim\Http\Response;
use App\Helper\Constante;

/*extraer ruta de contenidos*/
use App\Modelo\Contenido;


$app->getContainer()->get("db");
//$app->get('/', 'App\Controlador\PruebaControlador:index')->setName('index');


/* RUTAS NEGOCIO */
//exit;
$app->get('/ingresar', 'App\Controlador\LoginControlador:index')->setName('ingresar');
$app->post('/login', 'App\Controlador\LoginControlador:Login')->setName('login');


/*fin*/

/*Rutas para la Web emmsa*/
$app->get('/', 'App\Controlador\EmmsaControlador:index')->setName('index');
//$app->get('/normas', 'App\Controlador\EmmsaControlador:normas')->setName('normas');
$app->get('/documentos-publicacion', 'App\Controlador\EmmsaControlador:publicacion')->setName('documentos-publicacion');
$app->get('/documentos-publicacion/{anio}/{mes}', 'App\Controlador\EmmsaControlador:publicacion')->setName('documentos-publicacion-anio');

//$app->get('/nosotros', 'App\Controlador\EmmsaControlador:contenido')->setName('nosotros');
$app->get('/estadistica', 'App\Controlador\EmmsaControlador:estadistica')->setName('estadistica');
//$app->get('/estadistica/{tematica}', 'App\Controlador\EmmsaControlador:estadisticaTematica')->setName('estadisticaTematica');

$app->group('/estadistica', function () use ($app) {
	$app->get('/volumen-precios', 'App\Controlador\EstadisticaControlador:estadisticaVolumenPrecios')->setName('volumen-precios');
	$app->get('/historico-volumen', 'App\Controlador\EstadisticaControlador:estadisticaHistoricoVolumen')->setName('historico-volumen');
	$app->get('/historico-procedencia', 'App\Controlador\EstadisticaControlador:estadisticaProcedencia')->setName('historico-procedencia');
	$app->get('/variacion-procedencia', 'App\Controlador\EstadisticaControlador:estadisticaVariacionProcedencia')->setName('variacion-procedencia');
});


$app->group('/estadistica-gestion', function () use ($app) {
	$app->get('/', 'App\Controlador\EstadisticaControlador:estadisticaGestion')->setName('estadistica');
	
	//acciones
	$app->post('/estado', 'App\Controlador\EstadisticaControlador:estado')
		->setName('estado');
	$app->post('/eliminar-tematica', 'App\Controlador\EstadisticaControlador:eliminarTematica')
		->setName('eliminar-tematica');
	$app->post('/actualizar-tematica', 'App\Controlador\EstadisticaControlador:actualizarTematica')
		->setName('actualizar-tematica');
		$app->post('/guardar-tematica', 'App\Controlador\EstadisticaControlador:guardarTematica')
		->setName('guardar-tematica');
		//acciones indicador
		$app->post('/estado-indicador', 'App\Controlador\EstadisticaControlador:estadoIndicador')
		->setName('estado-indicador');
	$app->post('/eliminar-indicador', 'App\Controlador\EstadisticaControlador:eliminarIndicador')
		->setName('eliminar-indicador');
	$app->post('/actualizar-indicador', 'App\Controlador\EstadisticaControlador:actualizarIndicador')
		->setName('actualizar-indicador');
		$app->post('/guardar-indicador', 'App\Controlador\EstadisticaControlador:guardarIndicador')
		->setName('guardar-indicador');	
		$app->post('/guardar-registro-indicador', 'App\Controlador\EstadisticaControlador:guardarRegistroIndicador')
		->setName('guardar-registro-indicador');
		$app->get('/valida-registro-indicador/{parametros}', 'App\Controlador\EstadisticaControlador:validaRegistroIndicador')
		->setName('valida-registro-indicador');	
	//acciones para indicadores tematica
	$app->get('/tematica/{nombreTematica}', 'App\Controlador\EstadisticaControlador:gestionTematica')
	->setName('gestionTematica');	
});



$app->get('/estadistica/tematica/reporte', 'App\Controlador\EmmsaControlador:estadisticaReporte')->setName('estadisticaReporte');
$app->get('/estadistica/tematica/reportetabla', 'App\Controlador\EmmsaControlador:estadisticaReporteTabla')->setName('reportetabla');
$app->get('/estadistica/tematica/reporteparametros', 'App\Controlador\EmmsaControlador:estadisticaReporteParametros')->setName('reporteparametros');


/*rutas de las normas*/


$app->group('/documento-gestion', function () use ($app) {
	$app->get('/', 'App\Controlador\DocumentoControlador:documentoGestion')->setName('documento');
	//acciones
	//acciones
	$app->post('/estado-documento', 'App\Controlador\DocumentoControlador:estado')
		->setName('estado');
	$app->post('/eliminar-documento', 'App\Controlador\DocumentoControlador:eliminarDocumento')
		->setName('eliminar-documento');
	$app->post('/actualizar-documento', 'App\Controlador\DocumentoControlador:actualizarDocumento')
		->setName('actualizar-documento');
		$app->post('/guardar-documento', 'App\Controlador\DocumentoControlador:guardarDocumento')
		->setName('guardar-documento');
		//acciones indicador
});


/* consultas ajax*/
$app->group('/ajax', function () use ($app) {
	$app->group('/estadistica', function () use ($app) {
		$app->post('/get-volumen-precio', 'App\Controlador\AjaxControlador:getVolumenPrecio')->setName('get-volumen-precio');
		$app->post('/get-historico-volumen', 'App\Controlador\AjaxControlador:getHistoricoVolumen')->setName('get-historico-volumen');
		$app->post('/get-procedencia', 'App\Controlador\AjaxControlador:getProcedencia')->setName('get-procedencia');
		$app->post('/get-variedad', 'App\Controlador\AjaxControlador:getVariedad')->setName('get-variedad');
		$app->post('/get-variacion-procedencia', 'App\Controlador\AjaxControlador:getVariacionProcedencia')->setName('get-variacion-procedencia');
		$app->post('/get-anios-producto', 'App\Controlador\AjaxControlador:getAniosProducto')->setName('get-anios-producto');
	});
	$app->post('/get-documento', 'App\Controlador\AjaxControlador:getDocumento')->setName('get-documento');
	$app->post('/get-popup', 'App\Controlador\AjaxControlador:getPopUp')->setName('get-popup');
	$app->post('/tipo-documento', 'App\Controlador\AjaxControlador:getTipoDocumentobyCatalogo')->setName('tipo-documento');
	$app->post('/territorio', 'App\Controlador\AjaxControlador:getTerritoriobyTipo')->setName('territorio');
	$app->post('/fuente-informacion', 'App\Controlador\AjaxControlador:getEntidadbyFuenteRepresentacion')->setName('fuente-informacion');
	$app->post('/entidad-contacto', 'App\Controlador\AjaxControlador:getContactobyEntidad')->setName('entidad-contacto');
	$app->post('/descriptor-tematica', 'App\Controlador\AjaxControlador:getTematicabyDescriptor')->setName('descriptor-tematica');
	$app->post('/guardar-contactenos', 'App\Controlador\AjaxControlador:guardarContactenos')->setName('guardar-contactenos');
});
/* fin consultas ajax*/

/*Fin emmsa*/


//$app->get('/', 'App\Controlador\PruebaControlador:index')->setName('index');
// $app->get('/{url}', 'App\Controlador\PruebaControlador:indexPagina')->setName('{url}');

// ******************************************************************** RUTAS DE ACCESO A LA PLATAFORMA *************************************************************************// $app->get('/ingresar', 'App\Controlador\LoginControlador:index')->setName('ingresar');
// $app->post('/login', 'App\Controlador\LoginControlador:Login')->setName('login');
// $app->post('/get-roles-usuario', 'App\Controlador\LoginControlador:getRolesPorUsuario')->setName('get-roles-usuario');
// $app->post('/iniciar-sesion', 'App\Controlador\LoginControlador:IniciarSesion')->setName('iniciar-sesion');
// $app->get('/dashboard', 'App\Controlador\LoginControlador:Dashboard')->setName('dashboard');
// $app->post('/get-modulos', 'App\Controlador\LoginControlador:ListarModulos')->setName('get-modulos');
// $app->post('/get-menu', 'App\Controlador\LoginControlador:ListarAccesos')->setName('get-menu');
// $app->post('/cambiarPass', 'App\Controlador\LoginControlador:CambiarPassword')->setName('cambiarPass');
// $app->post('/cambiarSesionRol', 'App\Controlador\LoginControlador:CambiarSesionRol')->setName('cambiarSesionRol');
// $app->post('/btnMenu', 'App\Controlador\LoginControlador:btnMenu')->setName('btnMenu');
// $app->get('/salir', 'App\Controlador\LoginControlador:CerrarSesion')->setName('salir');
// ****************************************************************************************************************************************************************************************************************************

// ********************************************************************************************* GRUPO PRINCIPAL PARA EL CMS **************************************************************************************************

/*RUTAS PADRE PARA LA WEB*/
//$data = Contenido::ListarRutasContenido();
$data = Contenido::menuTotal();
$routesH=Array();
$routes = $data["data"];

$urlNode = "";
menuMacro($routes,0,$urlNode,$app);

function menuMacro($menues, $level, $urlNodo,$app){
		foreach ($menues as $menu) {

			if (count($menu->hijosMenus)>0) {
				//echo '/'.$menu->url;
				$dato=Array("level"=>$level,"urlNodo"=>$urlNodo, "menu"=>$menu);
				
				switch ($dato["menu"]->url) {
					case Constante::RUTA_NOTICIAS:
						$app->get('/'.$menu->url, 'App\Controlador\EmmsaControlador:contenidoNoticias')->setName($menu->url);
						$app->get('/'.$menu->url.'/{anio}/{mes}', 'App\Controlador\EmmsaControlador:contenidoNoticias')->setName($menu->url."anio");
					break;
					
					default:
					$app->get('/'.$menu->url, 'App\Controlador\EmmsaControlador:contenidoNivelUno')->setName($menu->url);

						break;
				}

				$app->group('/'.$menu->url, function () use ($app,$dato) {
					//echo '/'.$dato["menu"]->url." / <br/>";
					
					menuMacro($dato["menu"]->hijosMenus,$dato["level"]++,$dato["menu"]->url,$app);
				});
			}else{
				//enlaces que no tienen hijos
				//echo $urlNodo;
				//echo '/'.$menu->url."libre <br/>";
				switch ($urlNodo) {
					case Constante::RUTA_NOTICIAS:
						$app->get('/'.$menu->url, 'App\Controlador\EmmsaControlador:contenidoNoticiasDetalle')->setName($menu->url);
					break;
					default:
						$app->get('/'.$menu->url, 'App\Controlador\EmmsaControlador:contenidoNivelUno')->setName($menu->url);
					break;
				}
				
			}
		}
}


foreach ($routes as $route) {
	$nombre = $route->url;

	
	//$app->get('/'.$nombre.'/{urlPadre}', 'App\Controlador\EmmsaControlador:contenidoNivelDos')->setName($nombre);
	//$app->get('/'.$nombre.'/{urlPadre}/{urlHijo}', 'App\Controlador\EmmsaControlador:contenidoNivelTres')->setName($nombre);
/* 	if (!empty($routesH)) {
		$app->group('/'.$nombre, function () use ($app, $routesH) {
			foreach ($routesH as $routeH) {
				$app->get('/'.$routeH["url"], 'App\Controlador\EmmsaControlador:index')->setName($routeH["url"]);
			}
		});
	} */
}

$app->group('/cms', function () use ($app) {
$app->group('/noticias', function () use ($app) {
		$data = Contenido::ListarRutasContenido();
		$routes = $data["data"];
		foreach ($routes as $route) {
			$nombre = $route["url"];
			//var_dump($nombre);
			$app->get('/'.$nombre, 'App\Controlador\EmmsaControlador:index')->setName($nombre);
		}
});
	// RUTAS PARA EL ACCESO AL CMS
//$app->get('/ingresar', 'App\Controlador\LoginControlador:index')->setName('ingresar');
//$app->post('/login', 'App\Controlador\LoginControlador:Login')->setName('login');
$app->post('/get-roles-usuario', 'App\Controlador\LoginControlador:getRolesPorUsuario')->setName('get-roles-usuario');
$app->post('/iniciar-sesion', 'App\Controlador\LoginControlador:IniciarSesion')->setName('iniciar-sesion');
$app->get('/dashboard', 'App\Controlador\LoginControlador:Dashboard')->setName('dashboard');
$app->post('/get-modulos', 'App\Controlador\LoginControlador:ListarModulos')->setName('get-modulos');
$app->post('/get-menu', 'App\Controlador\LoginControlador:ListarAccesos')->setName('get-menu');
$app->post('/cambiarPass', 'App\Controlador\LoginControlador:CambiarPassword')->setName('cambiarPass');
$app->post('/cambiarSesionRol', 'App\Controlador\LoginControlador:CambiarSesionRol')->setName('cambiarSesionRol');
$app->post('/btnMenu', 'App\Controlador\LoginControlador:btnMenu')->setName('btnMenu');
$app->get('/salir', 'App\Controlador\LoginControlador:CerrarSesion')->setName('salir');
$app->get('/menu-izquierda', 'App\Controlador\LoginControlador:menuIzquierda')->setName('menu-izquierda');
// ****************************************************************************************************************************************************************************************************************************

	$app->group('/usuarios', function () use ($app) {
		// RUTAS USUARIOS
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
		// RUTAS ROLES
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
		// RUTAS ACCESOS
		$app->get('/', 'App\Controlador\UsuarioControlador:indexAccesos')->setName('');
		$app->post('/listar-modulos', 'App\Controlador\UsuarioControlador:ListarModulos')->setName('listar-modulos');
		$app->post('/listar-acceso-by-id-rol', 'App\Controlador\UsuarioControlador:ListarAccesoByIdRol')->setName('listar-acceso-by-id-rol');
		$app->post('/asignar-acceso', 'App\Controlador\UsuarioControlador:AsignarAccesoRol')->setName('asignar-acceso');
		$app->post('/validar-acceso-habilitado', 'App\Controlador\UsuarioControlador:ValidarAccesoHabilitado')->setName('validar-acceso-habilitado');
		$app->post('/listar-acciones-by-acceso', 'App\Controlador\UsuarioControlador:ListarAccionesByIdAcceso')->setName('listar-acciones-by-acceso');
		$app->post('/aginar-accion', 'App\Controlador\UsuarioControlador:AsignarAccionAcceso')->setName('aginar-accion');
	});

	$app->group('/contenido', function () use ($app) {
		// RUTAS BANDEJA CONTENIDOS
		$app->get('/', 'App\Controlador\ContenidoControlador:index')->setName('');
		$app->post('/listar-contenido', 'App\Controlador\ContenidoControlador:ListarContenido')->setName('listar-contenido');
		$app->post('/filtrar-contenido', 'App\Controlador\ContenidoControlador:FiltrarContenido')->setName('filtrar-contenido');
		$app->post('/listar-contenido-hijo', 'App\Controlador\ContenidoControlador:ListarContenidoHijo')->setName('listar-contenido-hijo');
		$app->post('/filtrar-contenido-hijo', 'App\Controlador\ContenidoControlador:FiltrarContenidoHijo')->setName('filtrar-contenido-hijo');
		// RUTAS NUEVO CONTENIDO - PRINCIPAL
		$app->get('/nuevo', 'App\Controlador\ContenidoControlador:indexNuevoContenido')->setName('nuevo');
		$app->post('/nuevo-contenido', 'App\Controlador\ContenidoControlador:NuevoContenido')->setName('nuevo-contenido');
		// RUTAS EDITAR CONTENIDO
		$app->get('/editar', 'App\Controlador\ContenidoControlador:indexEditarContenido')->setName('editar');
		$app->post('/setear-id-contenido', 'App\Controlador\ContenidoControlador:SetearIdContenido')->setName('setear-id-contenido');
		$app->post('/contenido-by-id', 'App\Controlador\ContenidoControlador:DatosContenidoById')->setName('contenido-by-id');
		$app->post('/editar-contenido', 'App\Controlador\ContenidoControlador:EditarContenido')->setName('editar-contenido');
		$app->post('/eliminar-contenido', 'App\Controlador\ContenidoControlador:eliminarContenido')->setName('eliminar-contenido');
		
		// RUTAS NUEVO CONTENIDO - HIJO
		$app->get('/nuevo-hijo', 'App\Controlador\ContenidoControlador:indexNuevoContenidoHijo')->setName('nuevo-hijo');
		$app->post('/setear-id-padre', 'App\Controlador\ContenidoControlador:SetearIdPadre')->setName('setear-id-padre');
		$app->post('/nuevo-contenido-hijo', 'App\Controlador\ContenidoControlador:NuevoContenidoHijo')->setName('nuevo-contenido-hijo');
		// RUTAS PARA SUBIR Y ASIGNAR IMAGENES Y/O DOCUMENTOS A UN CONTENIDO
		$app->get('/archivos-contenido', 'App\Controlador\ContenidoControlador:indexArchivosContenido')->setName('archivos-contenido');
		$app->post('/setear-id-contenido-archivos', 'App\Controlador\ContenidoControlador:SetearIdContenidoArchivo')->setName('setear-id-contenido-archivos');
		$app->post('/archivos-by-idcontenido', 'App\Controlador\ContenidoControlador:ArchivosByIdContenido')->setName('archivos-by-idcontenido');
		$app->post('/cambiar-estado-archivo-contenido', 'App\Controlador\ContenidoControlador:CambiarEstadoArchivoContenido')->setName('cambiar-estado-archivo-contenido');
		$app->post('/cambiar-portada-archivo-contenido', 'App\Controlador\ContenidoControlador:CambiarPortadaArchivoContenido')->setName('cambiar-portada-archivo-contenido');
		$app->post('/subir-archivo-contenido', 'App\Controlador\ContenidoControlador:SubirArchivoContenido')->setName('subir-archivo-contenido');
		// RUTAS PARA SUBIR IMAGENES Y DOCUMENTOS
		$app->post('/subir-imagen', 'App\Controlador\ContenidoControlador:SubirImagen')->setName('subir-imagen');
		$app->post('/subir-documento', 'App\Controlador\ContenidoControlador:SubirDocumento')->setName('subir-documento');
	});

	$app->group('/secciones', function () use ($app) {
		// RUTAS ROLES
		$app->get('/', 'App\Controlador\SeccionesControlador:index')->setName('');
		$app->post('/listar-secciones', 'App\Controlador\SeccionesControlador:ListarSecciones')->setName('listar-secciones');
		$app->post('/filtrar-secciones', 'App\Controlador\SeccionesControlador:FiltrarSecciones')->setName('filtrar-secciones');
		$app->post('/nueva-seccion', 'App\Controlador\SeccionesControlador:NuevaSeccion')->setName('nueva-seccion');
		$app->post('/seccion-by-id', 'App\Controlador\SeccionesControlador:DatosSeccionById')->setName('seccion-by-id');
		$app->post('/editar-seccion', 'App\Controlador\SeccionesControlador:EditarSeccion')->setName('editar-seccion');
		$app->post('/cambiar-estado-seccion', 'App\Controlador\SeccionesControlador:CambiarEstadoSeccion')->setName('cambiar-estado-seccion');
	});

	$app->group('/plantillas', function () use ($app) {
		// RUTAS PLANTILLAS
		$app->get('/', 'App\Controlador\PlantillasControlador:index')->setName('');
		$app->post('/listar-plantillas', 'App\Controlador\PlantillasControlador:ListarPlantillas')->setName('listar-plantillas');
		$app->post('/filtrar-plantillas', 'App\Controlador\PlantillasControlador:FiltrarPlantillas')->setName('filtrar-plantillas');
		$app->post('/nueva-plantilla', 'App\Controlador\PlantillasControlador:NuevaPlantilla')->setName('nueva-plantilla');
		$app->post('/plantilla-by-id', 'App\Controlador\PlantillasControlador:DatosPlantillaById')->setName('plantilla-by-id');
		$app->post('/editar-plantilla', 'App\Controlador\PlantillasControlador:EditarPlantilla')->setName('editar-plantilla');
		$app->post('/cambiar-estado-plantilla', 'App\Controlador\PlantillasControlador:CambiarEstadoPlantilla')->setName('cambiar-estado-plantilla');
		$app->post('/listar-secciones-by-plantilla', 'App\Controlador\PlantillasControlador:SeccionesByPlantilla')->setName('listar-plantillas-by-plantilla');
		$app->post('/asignar-seccion', 'App\Controlador\PlantillasControlador:AsignarSeccionPlantilla')->setName('asignar-seccion');
		$app->post('/cambiar-estado-seccion-plantilla', 'App\Controlador\PlantillasControlador:CambiarEstadoSeccionPlantilla')->setName('cambiar-estado-seccion-plantilla');
	});

	$app->group('/menu-superior', function () use ($app) {
		// VISTA MENU SUPERIOR
		$app->get('/', 'App\Controlador\ContenidoControlador:indexMenuSuperior')->setName('');
		$app->post('/listar-contenido', 'App\Controlador\ContenidoControlador:ListarContenido')->setName('listar-contenido');
		$app->post('/filtrar-contenido', 'App\Controlador\ContenidoControlador:FiltrarContenido')->setName('filtrar-contenido');
		$app->post('/listar-contenido-hijo', 'App\Controlador\ContenidoControlador:ListarContenidoHijo')->setName('listar-contenido-hijo');
		$app->post('/filtrar-contenido-hijo', 'App\Controlador\ContenidoControlador:FiltrarContenidoHijo')->setName('filtrar-contenido-hijo');
		$app->post('/nuevo-contenido', 'App\Controlador\ContenidoControlador:NuevoContenido')->setName('nuevo-contenido');
		$app->post('/nuevo-contenido-hijo', 'App\Controlador\ContenidoControlador:NuevoContenidoHijo')->setName('nuevo-contenido-hijo');
		$app->post('/contenido-by-id', 'App\Controlador\ContenidoControlador:DatosContenidoById')->setName('contenido-by-id');
		$app->post('/editar-contenido', 'App\Controlador\ContenidoControlador:EditarContenido')->setName('editar-contenido');
	});
});