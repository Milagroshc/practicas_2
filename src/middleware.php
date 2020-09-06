<?php

use App\Helper\Acl;
use App\Helper\Session;
use App\Helper\Constante;
use Slim\Exception\NotFoundException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RKA\Middleware\IpAddress;

use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Stream;
use Zend\Diactoros\Uri;

use App\Modelo\Menu;


$app->add($app->getContainer()->get('csrf'));

$checkProxyHeaders = true; // Note: Never trust the IP address for security processes!
$trustedProxies = ['10.0.0.1', '10.0.0.2']; // Note: Never trust the IP address for security processes!
$app->add(new RKA\Middleware\IpAddress($checkProxyHeaders, $trustedProxies));




$app->add(function($request, $response, $next){



	@$_SESSION['pagecount']++;

	//echo App\Helper\Acl::isLogged();
	$session = new \App\Helper\Session;

	$session::set('tiempoExpiracion', Constante::TIEMPOEXPIRACION);
	$tiempo = $session::get('tiempoExpiracion');

	$nombre = $request->getUri()->getPath();
	$route = $request->getAttribute('route');
	// return NotFound for non existent route
    if (empty($route)) {
        throw new NotFoundException($request, $response);
    }

    $name = $route->getName();
	$methods = $route->getMethods();
	$callable = $route->getMiddleware();
	//BUSCAR EL NAME EN LA TABLA MENU 
	$resultado = Menu::getOpcionesByName($name);
//var_dump($resultado);

switch ($resultado['success']) {
	case true:
		# code...
		if(!App\Helper\Acl::isLogged()){
			//var_dump("sin logear solo al iniciar");
			//echo "no tiene token logeado";
			return $response->withRedirect(Constante::DOMAINSITE.'ingresar');
		}
		break;
	default:
		# code...
	break;
}
	$response = $next($request, $response);
	return $response;
});