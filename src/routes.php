<?php
use Slim\Http\Request;
use Slim\Http\Response;
use App\Helper\Constante;
use App\Modelo\Sistema;
/*extraer ruta de contenidos*/
//use App\Modelo\Contenido;


$app->getContainer()->get("db");
//$app->get('/', 'App\Controlador\PruebaControlador:index')->setName('index');

//$app->redirect('/books', $app->router->pathFor('/games.markets', array('game' => 1)), 301);
$app->get('/contactito/{id}',function ($request,$response){
    $data = $request->getParams();

    //return $response->withRedirect($this->router->pathFor('tabla-modulos', ['data' => $data]));//https://github.com/slimphp/Slim/issues/1933
	return $response->withRedirect($this->router->pathFor('tabla-modulos', [], [
		'key1' => 1,
		'key2' => 2
	]));
})->setName('contactito');
/* RUTAS NEGOCIO */
//ruta para pagos
$app->post('/pagar', 'App\Controlador\CulqiControlador:index')->setName('pagar');
//exit;
$app->get('/ingresar', 'App\Controlador\LoginControlador:index')->setName('ingresar');
$app->get('/crear-usuario', 'App\Controlador\UsuarioControlador:crearUsuario')->setName('crear-usuario');

$app->group('/ajax', function () use ($app) {
	$app->post('/login', 'App\Controlador\LoginControlador:Login')->setName('login');
	$app->post('/registrar', 'App\Controlador\UsuarioControlador:NuevoUsuario')->setName('registrar');
	$app->post('/valida-persona', 'App\Controlador\PersonaControlador:validaPersona')->setName('valida-persona');
	$app->post('/app-file', 'App\Controlador\PruebaControlador:appFile')->setName('app-file');
  $app->post('/valida-servidor', 'App\Controlador\PruebaControlador:validaServidor')->setName('valida-servidor');
  $app->post('/params-operativo', 'App\Controlador\AjaxControlador:paramsOperativo')->setName('params-operativo');
  $app->post('/app-empresas', 'App\Controlador\AjaxControlador:appEmpresas')->setName('app-empresas');
  $app->post('/app-personas', 'App\Controlador\AjaxControlador:appPersonas')->setName('app-personas');
  $app->post('/app-requicitos-inspeccion', 'App\Controlador\AjaxControlador:appRequicitosInspeccion')->setName('app-requicitos-inspeccion');
});


$app->get('/dashboard', 'App\Controlador\LoginControlador:Dashboard')->setName('dashboard');
$app->get('/salir', 'App\Controlador\LoginControlador:CerrarSesion')->setName('salir');

$sistemas = Sistema::select()->with('modulos')->get()->toArray();

if (!empty($sistemas)) {
	foreach ($sistemas as $sistema) {
		//echo $sistema["RUTA"]."/";
		$app->group('/'.$sistema["RUTA"], function () use ($app, $sistema) {
			//var_dump($sistema["modulos"]);
			$modulos=$sistema["modulos"];
			if (!empty($modulos)) {
				foreach ($modulos as $modulo) {
					//echo $modulo["RUTA"]."/";
					$app->group('/'.$modulo["RUTA"], function () use ($app, $modulo) {
						//var_dump($modulo["objacceso"]);
						$objaccesos=$modulo["objacceso"];
						if (!empty($objaccesos)) {
							foreach ($objaccesos as $objacceso) {
								//echo $objacceso["RUTA"]."/";
								$controlador = "App\Controlador\\".$objacceso["CONTROLADOR"].":index";
								$app->get('/'.$objacceso["RUTA"], $controlador)->setName($objacceso["RUTA"]);
								//$app->post('/'.$objacceso["RUTA"].'/[{id}]', $controlador)->setName($objacceso["RUTA"].'post');
								$app->group('/'.$objacceso["RUTA"], function () use ($app, $objacceso) {
									//var_dump($objacceso);

									$menus=$objacceso["menus"];
									if (!empty($menus)) {
										foreach ($menus as $menu) {
											//echo $menu["URL"]."\n";
											$route =Array($menu["ROUTE"]);
											if ($menu["NROPARAMETROS"]==0) {
												# code...
												$app->map($route,'/'.$menu["URL"], 'App\Controlador\\'.$objacceso["CONTROLADOR"].':'.$menu["FUNCION"])->setName($menu["URL"]);
											}else{
												$param = "/";
												for ($i=0; $i < $menu["NROPARAMETROS"]; $i++) {
													if($i == $menu["NROPARAMETROS"]-1){
														$param = $param."{param".$i."}";
													}else{
														$param = $param."{param".$i."}/";
													}
												}
												$app->map($route,'/'.$menu["URL"].$param, 'App\Controlador\\'.$objacceso["CONTROLADOR"].':'.$menu["FUNCION"])->setName($menu["URL"]);
											}
											/* var_dump($param);
											var_dump($menu);
											exit; */
										}
									}
								});
							}
						}
					});
				}
			}
		});
	}
}
//exit;
