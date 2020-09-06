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
//---------------------------------
use Balping\JsonRaw\Raw;
use Balping\JsonRaw\Encoder;

// MODELOS
// -----------------------------------------
use Culqi;
// -----------------------------------------


class CulqiControlador{
	private $app;
	private $view;
	private $logger;
	private $hash;
	private $auth;
	private $session;
	private $jsonRequest;
	protected $table;
	public function __construct($app, Twig $view, LoggerInterface $logger, Builder $table, JsonRequest $jsonRequest, $hash, $auth){
		$this->hash = $hash;
		$this->auth = $auth;
		$this->app = $app;
		$this->session = new \App\Helper\Session;
		$this->jsonRequest = new JsonRequest();
		$this->JsonRender = new JsonRenderer();
		$this->view = $view;
		$this->logger = $logger;
		$this->table = $table;
	}
	public function index(Request $request, Response $response, $args){
	
		try {
			$datos = $request->getParsedBody();
			// Codigo de Comercio
			$PUBLIC_KEY = "sk_test_b2fdeff770c4be82";
			$culqi = new Culqi\Culqi(array('api_key' => $PUBLIC_KEY));
		 
			// Creando Cargo a una tarjeta
			$charge = $culqi->Charges->create(
				array(
					"amount" => 1500,
					"currency_code" => "PEN",
					"email" => $datos["correo"],
					"source_id" => $datos["token"]
				  )
			   );
			//var_dump($charge);
			// Respuesta
			//echo json_encode("Token: ".$token->id);
			$mensaje = "Se creo correctamente";
			$estado = true;
			$datin["charge"]=$charge;
			
		  } catch (\ErrorException $e) {
			$mensaje=$e->getMessage();
			$estado=false;
		  }	  
		  $datin["mensaje"]=$mensaje;
		  $datin["success"]=$estado;
		  $obj1 = JsonRenderer::render($response,200,$datin);
                
		  return $obj1;
	}

}
?>