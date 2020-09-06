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
var_dump($resultado);
exit;


	$findme   = 'ajax';
	$pos = strpos($nombre, $findme);

if ($pos==1) {
	//todos las url que tienen ajax pasaran si existe el route
}else{
	switch ($request->getUri()->getPath()) {

		case '/ingresar':
			//$response->write(' Please Insert Username and password ');
		break;
		case '/crear-usuario':
			//$response->write(' Please Insert Username and password ');
		break;
		case '/salir':
			//$response->write(' logout ');
		break;
		/*case '/admin':
		    if(!Acl::isLogged() || intval(Session::get("idRol"))!==2){
		        // return $response->withRedirect('login');
		        Session::destroy();
		        return $response->withRedirect('ingresar');
		        break;
		    }
		    break;
		case '/usuario':
		    if(!Acl::isLogged() || intval(Session::get("idRol"))!==1){
		        // return $response->withRedirect('login');
		        Session::destroy();
		        return $response->withRedirect('ingresar');
		        break;
		    }
		    break;

		
		case 'estado':
			//$response->write(' logout ');
			break;
		case 'puntos':
			break;//al darle break hago que pase directamente sin logearse
		case 'htmllogin':
			return $this->view->render($response, 'index.twig',['title' => 'Error: sin acceso '.$request->getUri()->getPath() ] );
			break;
		case 'pago/tributos':
			if(!Acl::isLogged() && Session::get("idRol")==1){
		       return $response->withRedirect('inicio');
			break;
		    }
			break;
		case 'consulta/expediente':
			/*if(!App\Helper\Acl::isLogged()){
		       // return $response->withRedirect('login');
			return $this->view->render($response, 'index.twig',['titulo' => 'Usted no tiene permiso a '.$request->getUri()->getPath(), "conteo"=> $_SESSION['pagecount'] ] );
			exit;
			break;
		    }
			break;
		case 'consulta/getExpediente':
			//pas directo ya que no necesita login
		break;
*/
		default:

		if(!App\Helper\Acl::isLogged()){
			//var_dump("sin logear solo al iniciar");
			//echo "no tiene token logeado";
			return $response->withRedirect(Constante::DOMAINSITE.'ingresar');
		}

		break;

	}
}
	
	$response = $next($request, $response);
	return $response;
});