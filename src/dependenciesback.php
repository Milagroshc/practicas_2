<?php
use App\Helper\Constante;
// DIC configuration
$container = $app->getContainer();
$container['upload_directory'] = __DIR__ . Constante::REPOSITORIO_FILE;

// view renderer
$container['renderer'] = function ($c) {
	$settings = $c->get('settings')['renderer'];
	return new Slim\Views\PhpRenderer($settings['template_path']);
};

/* referencia a redes sociales */
$container['instance'] = function ($container) {
	$instance = $container->get('settings')['redes'];
	return new Hybrid_Auth($instance);
};

/* registrando el container de TWIG */
// Register component on container
$container['view'] = function ($container) {
	$settings = $container->get('settings')['view'];
	$view = new \Slim\Views\Twig($settings['template_path'], $settings['twig']);

	// Instantiate and add Slim specific extension
	$basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
	$view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));
	$view->addExtension(new Twig_Extension_Debug());
	$view->addExtension (new App\Helper\JsonDecode());
	
	/* pasando parametros globales */
	$view->getEnvironment()->addGlobal("constantes", Constante::DEFAULT_PARAMETROS);
	
	/* se agrega este codigo para que todas las sessiones creadas pasen al TWIG */
	$view->getEnvironment()->addGlobal("session", $_SESSION);
	return $view;
};

// ------------------ configuracion de la base de datos ----------
// Service factory for the ORM
$container['db'] = function ($container) {
	$capsule = new \Illuminate\Database\Capsule\Manager;
	$capsule->addConnection($container['settings']['db']);
	$capsule->setAsGlobal();
	$capsule->bootEloquent();
	return $capsule;
};

$container['jsonRender'] = function($c){
	$view = new App\Helper\JsonRenderer();
	return $view;
};

$container['jsonRequest'] = function ($c) {
	$jsonRequest = new App\Helper\JsonRequest();
	return $jsonRequest;
};

$container['notAllowedHandler'] = function ($c) {
	return function ($request, $response, $methods) use ($c) {
		$view = new App\Helper\JsonRenderer();
		return $view->render($response, 405,
			['error_code' => 'not_allowed', 'error_message' => 'Method must be one of: ' . implode(', ', $methods)]
		);
	};
};

$container['notFoundHandler'] = function ($c) {
	return function ($request, $response) use ($c) {
		$view = new App\Helper\JsonRenderer();
		return $view->render($response, 404, ['error_code' => 'not_found', 'error_message' => 'Not Found']);
	};
};

$container['errorHandler'] = function ($c) {
	return function ($request, $response, $exception) use ($c) {
		$settings = $c->settings;
		$view = new App\Helper\JsonRenderer();
		$errorCode = 500;

		if (is_numeric($exception->getCode()) && $exception->getCode() > 300  && $exception->getCode() < 600) {
			$errorCode = $exception->getCode();
		}

		if ($settings['displayErrorDetails'] == true) {
			$data = [
				'error_code' => $errorCode,
				'error_message' => $exception->getMessage(),
				'file' => $exception->getFile(),
				'line' => $exception->getLine(),
				'trace' => explode("\n", $exception->getTraceAsString()),
			];
		} else {
			$data = [
				'error_code' => $errorCode,
				'error_message' => $exception->getMessage()
			];
		}
		return $view->render($response, $errorCode, $data);
	};
};

$container['csrf'] = function ($c) {
	$guard = new \Slim\Csrf\Guard();
	$guard->setFailureCallable(function ($request, $response, $next) {
		$request = $request->withAttribute("csrf_status", false);
		return $next($request, $response);
	});
	return $guard;
};

// monolog
$container['logger'] = function ($c) {
	$settings = $c->get('settings');
	$logger = new \Monolog\Logger($settings['logger']['name']);
	$logger->pushProcessor(new \Monolog\Processor\UidProcessor());
	$logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['logger']['path'], \Monolog\Logger::DEBUG));
	return $logger;
};

$container['hash'] = function($c) {
	return new App\Helper\Hash($c->get('app'));
};

//session
$container['session'] = function($c){
	return new App\Helper\Session;
};

// ****************************************************************************************************** CONTROLADORES ***************************************************************************************************
/*agregando nuevos controladores para la web de emmsa*/
$container['App\Controlador\EmmsaControlador'] = function ($c) {
    $view = $c->get('view');
   $logger = $c->get('logger');
   $table = $c->get('db')->table('consulta');
       $jsonRequest= $c->get('jsonRequest');
   $hash=$c->get('hash');
   $auth = $c->get('auth');
    return new App\Controlador\EmmsaControlador($view, $logger, $table, $jsonRequest, $hash,$auth );
};


// ------------------------------------------------------------------------------------------------------------------
$container['App\Controlador\PruebaControlador'] = function ($c) {
	$view = $c->get('view');
	$logger = $c->get('logger');
	$table = $c->get('db')->table('consulta');
	$jsonRequest = $c->get('jsonRequest');
	$hash = $c->get('hash');
	$auth = $c->get('auth');
	return new App\Controlador\PruebaControlador($view,$logger,$table,$jsonRequest,$hash,$auth);
};
// ------------------------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------------------------
$container['App\Controlador\LoginControlador'] = function ($c) {
	$view = $c->get('view');
	$logger = $c->get('logger');
	$table = $c->get('db')->table('consulta');
	$jsonRequest = $c->get('jsonRequest');
	$hash = $c->get('hash');
	$auth = $c->get('auth');
	return new App\Controlador\LoginControlador($view,$logger,$table,$jsonRequest,$hash,$auth);
};
// ------------------------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------------------------
$container['App\Controlador\UsuarioControlador'] = function ($c) {
	$view = $c->get('view');
	$logger = $c->get('logger');
	$table = $c->get('db')->table('consulta');
	$jsonRequest = $c->get('jsonRequest');
	$hash = $c->get('hash');
	$auth = $c->get('auth');
	return new App\Controlador\UsuarioControlador($view,$logger,$table,$jsonRequest,$hash,$auth);
};
// ------------------------------------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------------------------------------
$container['App\Controlador\ContenidoControlador'] = function ($c) {
	$view = $c->get('view');
	$logger = $c->get('logger');
	$table = $c->get('db')->table('consulta');
	$jsonRequest = $c->get('jsonRequest');
	$hash = $c->get('hash');
	$auth = $c->get('auth');
	return new App\Controlador\ContenidoControlador($view,$logger,$table,$jsonRequest,$hash,$auth);
};
// ------------------------------------------------------------------------------------------------------------------