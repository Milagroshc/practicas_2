<?php
namespace App\Controlador;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Illuminate\Database\Query\Builder;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Html;
//use \Firebase\JWT\JWT;

use Carlosocarvalho\SimpleInput\Input\Input;
/*modelos necesario*/
//use App\Modelo\User;
use App\Modelo\Usuario;
use App\Modelo\Persona;
use App\Modelo\Mensaje;
use App\Modelo\Empleador;
use App\Modelo\Departamento;

//use App\Validation\Validator;
use App\Helper\Hash;
use App\Helper\Acl;
use App\Helper\JsonRequest;
use App\Helper\JsonRenderer;
use App\Helper\Session;
use App\Helper\Constante;
use App\Modelo\Nivel;
use App\Modelo\Testimonio;
use App\Modelo\Docadjunto;
use App\Modelo\Servicio;
use App\Modelo\Pais;
use App\Modelo\Requisitos;
use App\Modelo\Paso;
use App\Modelo\Pariente;
use App\Modelo\Estudia;
use App\Modelo\Parametros;

use App\Modelo\ReporteVolPrecDiario;
use App\Modelo\ReporteTiempoLugarVolumen;



//modelos necesarios
use App\Modelo\Tematica;
use App\Modelo\Indicador;
use App\Modelo\Distrito;


/*modelos para APP miraflores */
use App\Modelo\GestContenido;

/*controladores para APP miraflores */
use App\Controlador\ContenidoControlador;


use RKA\Middleware\IpAddress;


use App\Modelo\Contenido;
use App\Modelo\RegistroIndicador;
use App\modelo\Territorio;
use App\modelo\Entidad;
use App\modelo\Documento;
use App\modelo\Catalogotablas;
use App\modelo\VinculoEmpresa;
use App\modelo\RequicitoInspeccion;

class AjaxControlador
{
    private $view;
    private $logger;
    private $hash;
    private $auth;
    private $session;
    private $jsonRequest;
    protected $table;
    protected $carpetaUpload;
    public function __construct(
        Twig $view,
        LoggerInterface $logger,
        Builder $table,
        JsonRequest $jsonRequest,
        Hash $hash,
        $auth,
        $carpetaUpload
    )
    {
        $this->hash     = $hash;
        $this->auth     = $auth;
        $this->session  = new \App\Helper\Session;
        $this->jsonRequest  = new JsonRequest();
        $this->JsonRender   = new JsonRenderer();

        $this->view = $view;
        $this->logger = $logger;
        $this->table = $table;
        $this->carpetaUpload= $carpetaUpload;
    }

    /*funciones*/
    /****************************/

    
    public  function appRequicitosInspeccion(Request $request, Response $response, $args) {
        try {
            //var_dump($filename);
            $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
            $tipoDocumentos= RequicitoInspeccion::getAllRequicitoInspeccion();
    
            $mensaje ="Su indicador fue correctamente guardado";
            $estado=true;
            $datin["datos"]=$tipoDocumentos;
        } catch (\ErrorException $e) {
            $mensaje="Algo no salio muy bien";
            $estado=false;
        }
        $datin["mensaje"]= $mensaje;
        $datin["success"] = $estado;
        $obj1 = JsonRenderer::render($response,200,$datin);
        return $obj1;
    }

public  function paramsOperativo(Request $request, Response $response, $args) {
    try {
        //var_dump($filename);
        $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
        $tipoDocumentos= Catalogotablas::getCatalogoTablas();

        $mensaje ="Su indicador fue correctamente guardado";
        $estado=true;
        $datin["datos"]=$tipoDocumentos;
    } catch (\ErrorException $e) {
        $mensaje="Algo no salio muy bien";
        $estado=false;
    }
    $datin["mensaje"]= $mensaje;
    $datin["success"] = $estado;
    $obj1 = JsonRenderer::render($response,200,$datin);
    return $obj1;
}

public  function appEmpresas(Request $request, Response $response, $args) {
    try {
        //var_dump($filename);
        $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
        $proveedores= Persona::getEmpresas();
        $mensaje ="Se lista correctamente las empresas";
        $estado=true;
        $datin["datos"]=$proveedores;
    } catch (\ErrorException $e) {
        $mensaje="Algo no salio muy bien";
        $estado=false;
    }
    $datin["mensaje"]= $mensaje;
    $datin["success"] = $estado;
    $obj1 = JsonRenderer::render($response,200,$datin);
    return $obj1;
}

public  function appPersonas(Request $request, Response $response, $args) {
    try {
        //var_dump($filename);
        $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
        $proveedores= Persona::getPersonas();
        $mensaje ="Se lista correctamente las personas";
        $estado=true;
        $datin["datos"]=$proveedores;
    } catch (\ErrorException $e) {
        $mensaje="Algo no salio muy bien";
        $estado=false;
    }
    $datin["mensaje"]= $mensaje;
    $datin["success"] = $estado;
    $obj1 = JsonRenderer::render($response,200,$datin);
    return $obj1;
}




    public  function getTipoDocumentobyCatalogo(Request $request, Response $response, $args) {
        try {
            //var_dump($filename);
            $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
            $tipoDocumentos= Parametros::getParametros($datos["idParametro"]);
            $responde=Array();
            if($tipoDocumentos["success"]){
                $responde=$tipoDocumentos["data"];
            }
            $mensaje ="Su indicador fue correctamente guardado";
            $estado=true;
            $datin["datos"]=$responde;
        } catch (\ErrorException $e) {
            $mensaje="Algo no salio muy bien";
            $estado=false;
        }
        $datin["mensaje"]= $mensaje;
        $datin["success"] = $estado;
        $obj1 = JsonRenderer::render($response,200,$datin);
        return $obj1;
    }


    public  function getTerritoriobyTipo(Request $request, Response $response, $args) {
        try {
            //var_dump($filename);
            $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
            $territorios= Territorio::getTerritoriosByIdParametro($datos["idParametro"]);
            $responde=Array();
            if($territorios["success"]){
                $responde=$territorios["data"];
            }
            $mensaje ="Su indicador fue correctamente guardado";
            $estado=true;
            $datin["datos"]=$responde;
        } catch (\ErrorException $e) {
            $mensaje="Algo no salio muy bien";
            $estado=false;
        }
        $datin["mensaje"]= $mensaje;
        $datin["success"] = $estado;
        $obj1 = JsonRenderer::render($response,200,$datin);
        return $obj1;
    }

    public  function getEntidadbyFuenteRepresentacion(Request $request, Response $response, $args) {
        try {
            //var_dump($filename);
            $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
            $territorios= Entidad::getEntidadByIdParametro($datos["idParametro"]);
            $responde=Array();
            if($territorios["success"]){
                $responde=$territorios["data"];
            }
            $mensaje ="Se listo correctamente las entidades";
            $estado=true;
            $datin["datos"]=$responde;
        } catch (\ErrorException $e) {
            $mensaje="Algo no salio muy bien";
            $estado=false;
        }
        $datin["mensaje"]= $mensaje;
        $datin["success"] = $estado;
        $obj1 = JsonRenderer::render($response,200,$datin);
        return $obj1;
    }
    public  function getContactobyEntidad(Request $request, Response $response, $args) {
        try {
            //var_dump($filename);
            $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
            $contactos= Entidad::getContactosByEntidad($datos["idParametro"]);
            $responde=Array();
            if($contactos["success"]){
                $responde=$contactos["data"];
            }
            $mensaje ="Se listo correctamente las entidades";
            $estado=true;
            $datin["datos"]=$responde;
        } catch (\ErrorException $e) {
            $mensaje="Algo no salio muy bien";
            $estado=false;
        }
        $datin["mensaje"]= $mensaje;
        $datin["success"] = $estado;
        $obj1 = JsonRenderer::render($response,200,$datin);
        return $obj1;
    }

    public  function getTematicabyDescriptor(Request $request, Response $response, $args) {
        try {
            //var_dump($filename);
            $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
            $tematicas= Tematica::getTematicaByDescriptor($datos["idParametro"]);
            $responde=Array();
            if($tematicas["success"]){
                $responde=$tematicas["data"];
            }
            $mensaje ="Se listo correctamente las entidades";
            $estado=true;
            $datin["datos"]=$responde;
        } catch (\ErrorException $e) {
            $mensaje="Algo no salio muy bien";
            $estado=false;
        }
        $datin["mensaje"]= $mensaje;
        $datin["success"] = $estado;
        $obj1 = JsonRenderer::render($response,200,$datin);
        return $obj1;
    }

    public  function getDocumento(Request $request, Response $response, $args) {
        try {
            //var_dump($filename);
            $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
            $documento= Documento::DatosDocumentoById($datos["idDocumento"]);
            $responde=Array();
            if($documento["success"]){
                $responde=$documento["data"];
            }
            $mensaje ="Se listo correctamente las entidades";
            $estado=true;
            $datin["datos"]=$responde;
        } catch (\ErrorException $e) {
            $mensaje="Algo no salio muy bien";
            $estado=false;
        }
        $datin["mensaje"]= $mensaje;
        $datin["success"] = $estado;
        $obj1 = JsonRenderer::render($response,200,$datin);
        return $obj1;
    }

    public  function getPopUp(Request $request, Response $response, $args) {
        try {
            //var_dump($filename);
            $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
            $documento= Contenido::getPopUp(Constante::ID_SECCION_HOME_POPUP);
            $responde=Array();
            if($documento["success"]){
                $responde=$documento["data"];
            }
            $mensaje ="Se listo correctamente las entidades";
            $estado=true;
            $datin["datos"]=$responde;
        } catch (\ErrorException $e) {
            $mensaje="Algo no salio muy bien";
            $estado=false;
        }
        $datin["mensaje"]= $mensaje;
        $datin["success"] = $estado;
        $obj1 = JsonRenderer::render($response,200,$datin);
        return $obj1;
    }



    /****************************/

    /* Estadistica de EMMSA */
    public  function getVolumenPrecio(Request $request, Response $response, $args) {
        try {
            //var_dump($filename);
            $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
            $fecha = Acl::fechaStringArray($datos["fecha"]);
            $reporte= ReporteVolPrecDiario::getVolumenPrecio($fecha);
            $responde=Array();
            if($reporte["success"]){
                $responde=$reporte["data"];
            }
            $mensaje ="Se listo correctamente las entidades";
            $estado=true;
            $datin["data"]=$responde;
        } catch (\ErrorException $e) {
            $mensaje="Algo no salio muy bien";
            $estado=false;
        }
        //$datin["mensaje"]= $mensaje;
        //$datin["success"] = $estado;
        $obj1 = JsonRenderer::render($response,200,$datin);
        return $obj1;
    }
    public  function getHistoricoVolumen(Request $request, Response $response, $args) {
        try {
            //var_dump($filename);
            $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
            $anio = $datos["anio"];
            $reporte= ReporteTiempoLugarVolumen::getHistoricoVolumen($anio);
            $responde=Array();
            if($reporte["success"]){
                $responde=$reporte["data"];
            }
            $mensaje ="Se listo correctamente las entidades";
            $estado=true;
            $datin["data"]=$responde;
        } catch (\ErrorException $e) {
            $mensaje="Algo no salio muy bien";
            $estado=false;
        }
        //$datin["mensaje"]= $mensaje;
        //$datin["success"] = $estado;
        $obj1 = JsonRenderer::render($response,200,$datin);
        return $obj1;
    }

    public  function getProcedencia(Request $request, Response $response, $args) {
        try {
            //var_dump($filename);
            $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
            $anio = $datos["anio"];
            $reporte= ReporteTiempoLugarVolumen::getProcedencia($anio);
            $responde=Array();
            if($reporte["success"]){
                $responde=$reporte["data"];
            }
            $mensaje ="Se listo correctamente las entidades";
            $estado=true;
            $datin["data"]=$responde;
        } catch (\ErrorException $e) {
            $mensaje="Algo no salio muy bien";
            $estado=false;
        }
        //$datin["mensaje"]= $mensaje;
        //$datin["success"] = $estado;
        $obj1 = JsonRenderer::render($response,200,$datin);
        return $obj1;
    }

    public  function getVariedad(Request $request, Response $response, $args) {
        try {
            //var_dump($filename);
            $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
            $producto = $datos["producto"];
            $reporte= ReporteTiempoLugarVolumen::getVariedad($producto);
            $responde=Array();
            if($reporte["success"]){
                $responde=$reporte;
            }
            $mensaje ="Se listo correctamente las entidades";
            $estado=true;
            $datin["data"]=$responde;
        } catch (\ErrorException $e) {
            $mensaje="Algo no salio muy bien";
            $estado=false;
        }
        $datin["mensaje"]= $mensaje;
        $datin["success"] = $estado;
        $obj1 = JsonRenderer::render($response,200,$datin);
        return $obj1;
    }

    public  function getVariacionProcedencia(Request $request, Response $response, $args) {
        try {
            //var_dump($filename);
            $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
            $reporte= ReporteTiempoLugarVolumen::getVariacionProcedencia($datos);
            $responde=Array();
            if($reporte["success"]){
                $responde=$reporte["data"];
            }
            $mensaje ="Se listo correctamente las entidades";
            $estado=true;
            $datin["data"]=$responde;
        } catch (\ErrorException $e) {
            $mensaje="Algo no salio muy bien";
            $estado=false;
        }
        $datin["mensaje"]= $mensaje;
        $datin["success"] = $estado;
        $obj1 = JsonRenderer::render($response,200,$responde);
        return $obj1;
    }

    public  function getAniosProducto(Request $request, Response $response, $args) {
        try {
            $reporte= ReporteTiempoLugarVolumen::getDistinctAniosTable();
            $responde=Array();
            if($reporte["success"]){
                $responde=$reporte["data"];
            }
            $mensaje ="Se listo correctamente las entidades";
            $estado=true;
            $datin["data"]=$responde;
        } catch (\ErrorException $e) {
            $mensaje="Algo no salio muy bien";
            $estado=false;
        }
        $datin["mensaje"]= $mensaje;
        $datin["success"] = $estado;
        $obj1 = JsonRenderer::render($response,200,$datin["data"]);
        return $obj1;
    }


    /* fin estadistica */


    public  function guardarContactenos(Request $request, Response $response, $args) {

        try {
            //var_dump($filename);
            $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/

            //verificar si ya existe este correo de contacto
            $verificaCorreo = Persona::verificaCorreo($datos["correo_personal"]);

            $mensaje = new Mensaje;
               $mensaje->asunto = $datos["asunto"];
               $mensaje->mensaje = $datos["mensaje"];
               $mensaje->estado = 0; // enviado

            if($verificaCorreo["success"]){
                $mensaje->idpersona = $verificaCorreo["data"]["idpersona"];
            }else{
               // echo "no existe"; //se guarda el registro de persona con los datos que crea
               $persona = new Persona;
                $persona->nombres = $datos["nombres"];
                $persona->correo_personal = $datos["correo_personal"];
                $persona->telefono_celular = $datos["telefono_celular"];
                $persona->save();
                $idpersona = $persona->id;
               // se guarda el mensaje
               $mensaje->idpersona = $idpersona;

            }
            $mensaje->save();

            $mensaje ="Se envio correctamente el mensaje de contacto";
            $estado=true;
        } catch (\ErrorException $e) {
            $mensaje="Algo no salio muy bien";
            $estado=false;
        }
        $datin["mensaje"]= $mensaje;
        $datin["success"] = $estado;
        $obj1 = JsonRenderer::render($response,200,$datin);
        return $obj1;
    }


}
