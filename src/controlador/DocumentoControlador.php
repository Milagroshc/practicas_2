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
use App\Modelo\CatalogoParametros;



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
use App\Modelo\Documento;
use App\Modelo\EntidadDocumento;
use App\Modelo\DocumentoRepresentaTerritorio;


class DocumentoControlador
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

    	/***********ESTADO DEL indicador******************/
    
        public function guardarDocumento(Request $request, Response $response, $args) {

            try {
                /*recibo los parametros del login*/
                $directory= Constante::REPOSITORIO_FILE."documento/";
                
                $uploadedFiles = $request->getUploadedFiles();
                $archivos =Array("archivo", "caratula");

                for ($i=0; $i < count($archivos) ; $i++) { 
                    // handle single input with single file upload
                        $uploadedFile = $uploadedFiles[$archivos[$i]];
                        //echo $uploadedFile["name"];
                    // var_dump(get_object_vars($uploadedFile));
                        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                            $filename = Acl::moveUploadedFile($directory, $uploadedFile);
                            //$response->write('uploaded ' . $filename . '<br/>');
                        }
                        //var_dump($filename);
                        $docAdjunto=[
                            "nombre"=>$uploadedFile->getClientFilename(),
                            "urlAdjunto"=>$directory.$filename,
                            "tamanio"=>$uploadedFile->getSize(),
                            "formato"=>$uploadedFile->getClientMediaType(),
                            "estado"=>1
                        ];
                        $idDocAdjunto = Docadjunto::insertGetId($docAdjunto);
                        $idDocAdjuntos[$i]=Array("nombre"=>$archivos[$i], "idDocAdjunto"=>$idDocAdjunto);
                }

                $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
               
                $documento = new Documento;
                $documento->tipoDocumento = $datos["tipodocumento"];
                $documento->titulo = $datos["titulo"];
                if (!empty($datos["ambitoAplicacion"])) {
                    $documento->numero = $datos["numero"];
                }
                if (!empty($datos["numero_paginas"])) {
                    $documento->numero_pagina = $datos["numero_paginas"];
                }
                if (!empty($datos["ambitoAplicacion"])) {
                    $documento->ambitoAplicacion = $datos["ambitoAplicacion"];
                }

                $documento->descripcion = $datos["descripcion"];
                $documento->fecha_publicacion = $datos["fechaPublicacion"];
                $documento->representacionTerritorial = $datos["representacionTerritorial"];
                $documento->fuenteInformacion = $datos["fuenteInformacion"];
                $documento->estado = 0;
                $documento->descriptorTematico = $datos["descriptorTematico"];
                $documento->idDocAdjunto = $idDocAdjuntos[0]["idDocAdjunto"];
                $documento->caratula = $idDocAdjuntos[1]["idDocAdjunto"];
                $documento->nodo = $datos["nodo"];
                $documento->idEmpleado = $this->session::get("idempleado");
                $documento->idContacto = $datos["contacto"];
                

                $documento->url_audiovisual = $datos["url_audiovisual"];
                if (!empty($datos["isbn"])) {
                    $documento->isbn = $datos["isbn"];
                }

                $documento->save();
                $idDocumento = $documento->id;

                if (!empty($datos["territorio"])) {
                    foreach ($datos["territorio"] as $key => $value) {
                        /* guardar datos territorio */
                        $documentoRepresentaTerritorio = new DocumentoRepresentaTerritorio;
                        $documentoRepresentaTerritorio->idDocumento =$idDocumento;
                        $documentoRepresentaTerritorio->idTerritorio =$value;
                        $documentoRepresentaTerritorio->save();
                    }
                }

                /*guardar datos entidad_documento*/
                $entidadDocumento = new EntidadDocumento;
                $entidadDocumento->idEntidad =$datos["entidad"];
                $entidadDocumento->idDocumento = $idDocumento;
                $entidadDocumento->estado = 1;
                $entidadDocumento->save();
                $documentoDatos = Documento::DatosDocumentoById($idDocumento);
                //$listaDocumentos = Documento::listaDocumentos($idDocumento);
                $datin["datos"]= $documentoDatos["data"];
                //$datin["listaDocumentos"] =$listaDocumentos;
                
               
                $mensaje ="Su documento fue correctamente guardado";
                $estado=true;
            
        } catch (\ErrorException $e) {
            $mensaje="Algo no salio muy bien";
            $estado=false;
        }
            
           $datin["mensaje"]= $mensaje;
           $datin["success"] = $estado;
    
            /*[{"team": ""},]*/
            $obj1 = JsonRenderer::render($response,200,$datin);
    
        }


        public static function eliminarIndicador(Request $request, Response $response, $args) { 
            try {
                $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
                Indicador::where('idIndicador', $datos["idIndicador"])
                ->delete();
                $mensaje ="se elimino el puesto correctamente";
                $estado = true;
                
            } catch (\ErrorException $e) {
                $mensaje="Algo no salio muy bien";
                $estado=false;
            }
            
            $datin["mensaje"]= $mensaje;
            $datin["success"] = $estado;
            
            
            /*[{"team": ""},]*/
            $obj1 = JsonRenderer::render($response,200,$datin);
            
            return $obj1;
            
        }

        public static function estadoIndicador(Request $request, Response $response, $args) {
	    
	    
            try {
                $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
                Indicador::where('idIndicador', $datos["idIndicador"])	   
                ->update(['estado' => $datos["estado"]]);
                $mensaje ="se cambio de estado correctamente";
                $estado = true;
                    
            } catch (\ErrorException $e) {
                $mensaje="Algo no salio muy bien";
                $estado=false;
                }
                
               $datin["mensaje"]= $mensaje;
               $datin["success"] = $estado;
                
                
                /*[{"team": ""},]*/
               $obj1 = JsonRenderer::render($response,200,$datin);
                
               return $obj1;
                
            }

            public  function actualizarIndicador(Request $request, Response $response, $args) {
        
                try {
                   
                    
                    //var_dump($filename);
                    $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
                   
                        
                        $indicador=[
                            "nombre"=>$datos["nombre"],
                            "unidad"=>$datos["unidad"],
                            "descripcion"=>$datos["descripcion"],
                            "prioridad"=>$datos["prioridad"],
                            "periodo_generacion"=>$datos["periodogeneracion"],
                            "periodo_sinia"=>$datos["periodosinia"]
                        ];


                    Indicador::where('idIndicador', $datos["idIndicador"])
                    ->update($indicador);

                    
                    $periodogeneracion= Parametros::getParametrosById($datos["periodogeneracion"]);
                    $periodosinia= Parametros::getParametrosById($datos["periodosinia"]);
    

                    $indicadorDatos=[
                        "idIndicador"=>$datos["idIndicador"],
                        "nombre"=>$datos["nombre"],
                        "descripcion"=>$datos["descripcion"],
                        "unidad"=>$datos["unidad"],
                        "prioridad"=>$datos["prioridad"],
                        "periodo_generacion"=>$datos["periodogeneracion"],
                        "nombre_periodo_generacion"=>$periodogeneracion["data"]["nombre_parametro"],
                        "periodo_sinia"=>$datos["periodosinia"],
                        "nombre_periodo_sinia"=>$periodosinia["data"]["nombre_parametro"]
                    ];


                    $mensaje ="El puesto fue correctamente actualizado";
                    $estado=true;
                } catch (\ErrorException $e) {
                    $mensaje="Algo no salio muy bien";
                    $estado=false;
                }
                
                $datin["mensaje"]= $mensaje;
                $datin["success"] = $estado;
                $datin["datos"]=$indicadorDatos;
                
                /*[{"team": ""},]*/
                $obj1 = JsonRenderer::render($response,200,$datin);
                
            }

            public function guardarRegistroIndicador(Request $request, Response $response, $args) {

                try {
                    /*recibo los parametros del login*/
                    $directory=$this->carpetaUpload;
                    $uploadedFiles = $request->getUploadedFiles();
                    // handle single input with single file upload
                    $uploadedFile = $uploadedFiles['archivo'];
                    //echo $uploadedFile["name"];
                   // var_dump(get_object_vars($uploadedFile));
                    
                    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                        $filename = Acl::moveUploadedFile($directory, $uploadedFile);
                        //$response->write('uploaded ' . $filename . '<br/>');
                    }
                    //var_dump($filename);
                    $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
                    $docAdjunto=[
                        "nombre"=>$uploadedFile->getClientFilename(),
                        "urlAdjunto"=>Constante::DOMAINSITE."uploads/".$filename,
                        "tamanio"=>$uploadedFile->getSize(),
                        "formato"=>$uploadedFile->getClientMediaType(),
                        "estado"=>1
                    ];
                    $idDocAdjunto = Docadjunto::insertGetId($docAdjunto);
                    $datinos=Array(
                        "idEmpleado"=> $this->session::get("idempleado"),
                        "idIndicador" => $datos["idIndicador"],
                        "urlArchivocargado" => "uploads/".$filename
                    );
                    //DocumentoControlador::uploadIndicadores($datinos); //se generará un enlace con datin
                    $mensaje ="Su archivo fue correctamente guardado";
                    $estado=true;
            } catch (\ErrorException $e) {
                $mensaje="Algo no salio muy bien";
                $estado=false;
            }
               $obj0 = json_encode($datinos);
            
          /*      $urlencode =urlencode($obj0);
               var_dump($urlencode);
               var_dump(urldecode($urlencode));
               var_dump(base64_encode($obj0));
               $urlencode = base64_encode($obj0);
               var_dump(base64_decode($encode));
 */
               $urlencode = base64_encode($obj0);
               $datin["mensaje"]= $mensaje;
               $datin["success"] = $estado;
               $datin["datos"]=$urlencode;
        
                /*[{"team": ""},]*/
                $obj1 = JsonRenderer::render($response,200,$datin);
        
            }

            public function validaRegistroIndicador(Request $request, Response $response, $args)
            {   
                $datos=json_decode(base64_decode($args["parametros"]));
                //var_dump($datos->idEmpleado);
                //exit;

            $idEmpleado=$datos->idEmpleado;
            $idIndicador=$datos->idIndicador;
            //$datos = $request->getParsedBody();
            
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(TRUE);
            $spreadsheet = $reader->load($datos->urlArchivocargado);
            
            $worksheet = $spreadsheet->getActiveSheet();
            // Get the highest row and column numbers referenced in the worksheet
            $highestRow = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5
            $cabecera=Array();
            $idDistrito="";
            $valor=0.0;
            $anio=0.0;
            $i=0;
            $registrosIndicador=Array();
            for ($row = 1; $row <= $highestRow; ++$row) {
            
                for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                    
                    if($row==1){
                        $cabecera[$col-1]=$worksheet->getCellByColumnAndRow($col, $row)->getValue();
                    }else{
                        switch ($col) {
                            case 1:
                                $idDistrito=$worksheet->getCellByColumnAndRow($col, $row)->getValue();
                                break; 
                            case 2:
                            break;     
                            default:
                                $valor=$worksheet->getCellByColumnAndRow($col, $row)->getValue();
                                $anio=$cabecera[$col-1];
            
                                $registroIndicador = new RegistroIndicador;
                                $registroIndicador->idIndicador = $idIndicador;
                                $registroIndicador->idEmpleado = $idEmpleado;
                                $registroIndicador->idDistrito = $idDistrito;
                                $registroIndicador->valor = $valor;
                                $registroIndicador->anio = $anio;

            
                                $indicadores[$row-1][$col-2]=Array(
                                    "idIndicador"=>$idIndicador,
                                    "idEmpleado"=>$idEmpleado,
                                    "idDistrito" =>$idDistrito,
                                    "valor" =>$valor,
                                    "anio" =>$anio,
                                ); 
                                $estado=RegistroIndicador::isExist($indicadores[$row-1][$col-2]);
                                //validar si existe 
                                //if($estado["estado"]==false){
                                    $registrosIndicador[$i]=array_merge($indicadores[$row-1][$col-2], $estado);
                                    //$registrosIndicadorEstado[$i]=$estado;
                                    $i++;
                                //}
                                //$registroIndicador->save();
                            
                            break;
                        }
                    }
                    
            
                   // $value = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                }
                
                
                
            }
            DocumentoControlador::crearExcelTablaHTML($registrosIndicador);
            //var_dump($registrosIndicador);
            //exit;
        }
     
        public static function crearExcelTablaHTML($datos){

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            $spreadsheet->getActiveSheet()->getStyle('A1:G1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('1A4A84');


         


            $sheet->setCellValue('A1', 'Nro');
            $sheet->setCellValue('B1', 'Id Indicador');
            $sheet->setCellValue('C1', 'UBIGEO');
            $sheet->setCellValue('D1', 'valor');
            $sheet->setCellValue('E1', 'Periodo');
            $sheet->setCellValue('F1', 'estado');
            $sheet->setCellValue('G1', 'mensaje');

            for ($i=0; $i < count($datos) ; $i++) {
                $j=$i+2;
                $sheet->setCellValue('A'.$j, $i);
                $sheet->setCellValue('B'.$j, $datos[$i]["idIndicador"]);
                $sheet->setCellValue('C'.$j, $datos[$i]["idDistrito"]);
                $sheet->setCellValue('D'.$j, $datos[$i]["valor"]);
                $sheet->setCellValue('E'.$j, $datos[$i]["anio"]);
                $sheet->setCellValue('F'.$j, $datos[$i]["estado"]);
                $sheet->setCellValue('G'.$j, $datos[$i]["mensaje"]);
            }

            $spreadsheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(22);

            $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(42);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
            $spreadsheet->getActiveSheet()->setAutoFilter('A1:G1');

            $conditional1 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
$conditional1->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
$conditional1->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_LESSTHAN);
$conditional1->addCondition('0');
$conditional1->getStyle()->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
$conditional1->getStyle()->getFont()->setBold(true);

$conditional2 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
$conditional2->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
$conditional2->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_GREATERTHANOREQUAL);
$conditional2->addCondition('0');
$conditional2->getStyle()->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
$conditional2->getStyle()->getFont()->setBold(true);

$conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('A1:G1')->getConditionalStyles();
$conditionalStyles[] = $conditional1;
$conditionalStyles[] = $conditional2;

$spreadsheet->getActiveSheet()->getStyle('A1:G1')->setConditionalStyles($conditionalStyles);


            /* Generate the Excel File */
            $time = time();
            $namefile = "Validar".date("dmYH:i:s", $time).".xlsx";
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$namefile.'"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
            header ('Cache-Control: cache, must-revalidate');
            header ('Pragma: public');
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;


        }


        /***********fin indicador******************/
	


    	/***********ESTADO DEL TEMATICA******************/
	
	public static function estado(Request $request, Response $response, $args) {
	    
	    
        try {
            $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
            Tematica::where('idTematica', $datos["idTematica"])	   
            ->update(['estado' => $datos["estado"]]);
            $mensaje ="se cambio de estado correctamente";
            $estado = true;
                
        } catch (\ErrorException $e) {
            $mensaje="Algo no salio muy bien";
            $estado=false;
            }
            
           $datin["mensaje"]= $mensaje;
           $datin["success"] = $estado;
            
            
            /*[{"team": ""},]*/
           $obj1 = JsonRenderer::render($response,200,$datin);
            
           return $obj1;
            
        }

        public static function eliminarTematica(Request $request, Response $response, $args) {
        
        
            try {
                $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
                Tematica::where('idTematica', $datos["idTematica"])
                ->delete();
                $mensaje ="se elimino correctamente";
                $estado = true;
                
            } catch (\ErrorException $e) {
                $mensaje="Algo no salio muy bien";
                $estado=false;
            }
            
            $datin["mensaje"]= $mensaje;
            $datin["success"] = $estado;
            
            
            /*[{"team": ""},]*/
            $obj1 = JsonRenderer::render($response,200,$datin);
            
            return $obj1;
            
        }


           
    public  function actualizarTematica(Request $request, Response $response, $args) {
        
        try {
            /*recibo los parametros del login*/
            $directory=$this->carpetaUpload;
            
            $uploadedFiles = $request->getUploadedFiles();
            
            // handle single input with single file upload
            $uploadedFile = $uploadedFiles['archivo'];
            
            /*verifico que exista idLogo*/
            $file1=$uploadedFile;
            
            //var_dump($filename);
            $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
            
            $ruta=Acl::crear_url($datos["nombre"]);
            
            if ($uploadedFile->getError() === UPLOAD_ERR_OK) {

                $filename = Acl::moveUploadedFile($directory, $uploadedFile);
                //$response->write('uploaded ' . $filename . '<br/>');
                $docAdjunto=[
                    "nombre"=>$uploadedFile->getClientFilename(),
                    "urlAdjunto"=>Constante::DOMAINSITE."uploads/".$filename,
                    "tamanio"=>$uploadedFile->getSize(),
                    "formato"=>$uploadedFile->getClientMediaType(),
                    "estado"=>1
                ];
                $tematicaDatos=[
                    "idTematica"=>$datos["idTematica"],
                    "nombre"=>$datos["nombre"],
                    "descripcion"=>$datos["descripcion"],
                    "orden"=>$datos["orden"],
                    "url"=>$ruta,
                    "token_url"=>$this->hash->hash($ruta),
                    "urlAdjunto"=>$docAdjunto["urlAdjunto"]
                ];
                $file1=true;
                
            }else{
                $docAdjunto=[
                    "nombre"=>$uploadedFile->getClientFilename()
                ];
                $tematicaDatos=[
                    "idTematica"=>$datos["idTematica"],
                    "nombre"=>$datos["nombre"],
                    "descripcion"=>$datos["descripcion"],
                    "orden"=>$datos["orden"],
                    "url"=>$ruta
                ];
                
                $file1=false;
            }
            
           
            Docadjunto::where('idDocAdjunto', $datos["idDocAdjunto"])
            ->update($docAdjunto);
            $tematica=[
                "nombre"=>$datos["nombre"],
                "descripcion"=>$datos["descripcion"],
                "orden"=>$datos["orden"],
                "url"=>$ruta,
                "token_url"=>$this->hash->hash($ruta),
            ];
            
            
            Tematica::where('idTematica', $datos["idTematica"])
            ->update($tematica);
            
           
            $mensaje ="La temática fue correctamente actualizado";
            $estado=true;
            
        } catch (\ErrorException $e) {
            $mensaje="Algo no salio muy bien";
            $estado=false;
        }
        
        $datin["mensaje"]= $mensaje;
        $datin["success"] = $estado;
        $datin["datos"]=$tematicaDatos;
        $datin["file"]=$file1;
        
        /*[{"team": ""},]*/
        $obj1 = JsonRenderer::render($response,200,$datin);
        
    }

        	/*----------INSERTAR TEMATICA---------------*/
	public function guardarTematica(Request $request, Response $response, $args) {

	    try {
	        /*recibo los parametros del login*/
	        $directory=$this->carpetaUpload;
	        
	        $uploadedFiles = $request->getUploadedFiles();
	        
	        // handle single input with single file upload
	        $uploadedFile = $uploadedFiles['archivo'];
	        //echo $uploadedFile["name"];
	       // var_dump(get_object_vars($uploadedFile));
	        
	        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
	            $filename = Acl::moveUploadedFile($directory, $uploadedFile);
	            //$response->write('uploaded ' . $filename . '<br/>');
	        }
	        //var_dump($filename);
	        $datos = $request->getParsedBody(); /*se utiliza para recibir parametros post*/
	        $docAdjunto=[
	            "nombre"=>$uploadedFile->getClientFilename(),
	            "urlAdjunto"=>Constante::DOMAINSITE."uploads/".$filename,
	            "tamanio"=>$uploadedFile->getSize(),
	            "formato"=>$uploadedFile->getClientMediaType(),
	            "estado"=>1
	        ];
            $idDocAdjunto = Docadjunto::insertGetId($docAdjunto);
            $ruta=Acl::crear_url($datos["nombre"]);
	        $tematica=[
                "nombre"=>$datos["nombre"],
                "orden"=>$datos["orden"],
                "descripcion"=>$datos["descripcion"],
                "url"=>$ruta,
                "token_url"=>$this->hash->hash($ruta),
	            "estado"=>0,
	            "idDocAdjunto"=>$idDocAdjunto
	        ];
	        
	        
	        $idTematica = Tematica::insertGetId($tematica);
	       
	        $tematicaDatos=[
	            "idTematica"=>$idTematica,
                "nombre"=>$datos["nombre"],
                "orden"=>$datos["orden"],
                "descripcion"=>$datos["descripcion"],
                "url"=>$ruta,
                'urlAdjunto' => $docAdjunto["urlAdjunto"],
                "token_url"=>$this->hash->hash($ruta),
	            "estado"=>0,
	            "idDocAdjunto"=>$idDocAdjunto
	        ];
	        $mensaje ="Su temática fue correctamente guardado";
	        $estado=true;
	    
	} catch (\ErrorException $e) {
	    $mensaje="Algo no salio muy bien";
	    $estado=false;
	}
	    
	   $datin["mensaje"]= $mensaje;
	   $datin["success"] = $estado;
	   $datin["datos"]=$tematicaDatos;

		/*[{"team": ""},]*/
		$obj1 = JsonRenderer::render($response,200,$datin);

	}

        /* gestionar tematicas */
        public function gestionTematica(Request $request, Response $response, $args)
{
  
    try {

        $url = $args['nombreTematica'];
        $datin["tematica"]=Tematica::getTematicaByUrl($url);

        $datin["listaTematicas"]=Tematica::ListarTematica();

        $idTematica = $datin["tematica"]["data"]["idTematica"];
        $datin["indicadores"]=Indicador::ListarIndicadorByIdTematica($idTematica);

        //parametros sobre periodo
        $datin["periodos"] = Parametros::getParametros(Constante::CAT_PERIODICIDAD);

        //extraer data de los indicadores del agua
        //$datin["meses"] = Parametros::getParametros(3);

        $mensaje ="se listo correctamente los indicadores";
        $estado = true;

          
      } catch (\ErrorException $e) {
          $mensaje="Algo no salio muy bien";
          $estado=false;
      }

      $datin["mensaje"]= $mensaje;
      $datin["success"] = $estado;
      $datin["parametros"] = ['title'=>"Estadísticas :: emmsa", 'titulo' => "Estadísticas Ambientales" ];
      //$obj1 = JsonRenderer::render($response,200,$datin);
      //var_dump($datin);
      //exit;
      $this->view->render($response, "cms/contenidos/tematica.twig", $datin);
      return $response;
}
  



    /* inicio de la pagina web*/
    public function index(Request $request, Response $response, $args)
    {

/*         $result = array();

		$query = Contenido::query()
		->selectRaw("contenido.*")
		->get()
        ->toArray();
        $datin["data"] = $query; */


       /*  phpinfo();
        exit; */
/*         $uri = $request->getUri();
        $query = $request->getUri()->getQuery();
        $frag = $request->getUri()->getFragment();
        $base = $request->getUri()->getBaseUrl(); */
        $ruta = $request->getAttribute('route')->getName();
/*         var_dump($uri);
        var_dump($query);
        var_dump($frag);
        var_dump($base); */
        //validar por token la ruta y extraer toda la información
/*         var_dump($ruta);
        $ruta1 = $this->hash->password($ruta);
        $ruta2 = $this->hash->hash($ruta);
        
        echo $ruta1;
        echo "<br>";
        echo $ruta2;

        var_dump($this->hash->passwordCheck($ruta,$ruta1)); */
        //echo password_hash($ruta, PASSWORD_DEFAULT)."\n";
       // exit;
        try {

            /*EXTRAYENDO TEMÁTICAS*/
            $datin["tematicas"]=Tematica::ListarTematica();

            //echo Acl::limpiar_string("Gestión de Riesgos y Desastres");
            //echo Acl::crear_url("Gestión de Riesgos y Desastres");
           $url='https://aplicativos.munlima.gob.pe/intranet/aplicativo-movil/noticias/wsNoticiasMML';
           $dato=array('tipo' => 'tipo');
           //Acl::validaSSL();
           var_dump(Acl::get_web_page($url));
           //var_dump(Acl::curlPOST($url,$dato));
           // $url = 'http://www.munlima.gob.pe/';
           // return $response->withRedirect($url);


           // $popup = ContenidoControlador::getContenidoPopups();
           // var_dump($popup);
           //exit;

           // $nroRes=Constante::NOTIC_NRO_RESULTADOS_DEFAULT;
            // $datin["contenidos"] = ContenidoControlador::getListadoNoticiasPortada($nroRes);

           // $datin["contenidos"] = GestContenido::getListadoNoticiasPortada($nroRes);


           /* $datin["popups"] = ContenidoControlador::getContenidoPopups();
            $datin["novedades"] = ContenidoControlador::getNovedadesMiraflores();
            $datin["agenda"] = ContenidoControlador::getAgendaPortada(3);
            $datin["pathmunlima"]=Constante::PATHMUNLIMA;
 */
            $mensaje ="se elimino correctamente el usuario y la persona";
              $estado = true;
  
              
          } catch (\ErrorException $e) {
              $mensaje="Algo no salio muy bien";
              $estado=false;
          }

          $datin["mensaje"]= $mensaje;
          $datin["success"] = $estado;
          // $datin["parametros"] = ['title'=>"Bienvenido :: Overseas Perú", 'titulo' => "BIENVENIDO A LOS SERVICIOS DE ATENCIÓN VIRTUAL" ];
          //$obj1 = JsonRenderer::render($response,200,$datin);
          //var_dump($datin);
          //exit;
          $this->view->render($response, "emmsa/inicio.twig", $datin);
          return $response;

    }

    /* emmsa - documento */
    
    /* servicio y opciones*/


public function documentoGestion(Request $request, Response $response, $args)
{
    $idusuario = $this->session::get("idusuario");
    $idempleado = $this->session::get("idempleado");
    $id_unidad_organica = $this->session::get("id_unidad_organica");
    $idrol = $this->session::get("idrol");
    $nombres = $this->session::get("nombres");
    $ape_paterno = $this->session::get("ape_paterno");
    $ape_materno = $this->session::get("ape_materno");

    /*lista de parametros*/
    $datin["catalogoDocumentos"] = CatalogoParametros::getCatalogoByCatalogo(Constante::CATALOGO_DOCUMENTO);
    $datin["tipoDocumentos"] = Parametros::getParametros(Constante::TIPO_DOCUMENTO);
    $datin["tipoNormas"] = Parametros::getParametros(Constante::TIPO_NORMA);
    $datin["ambitosAplicacion"] = Parametros::getParametros(Constante::AMBITO_APLICACION);
    $datin["representacionTerritoriales"] = Parametros::getParametros(Constante::REPRESENTACION_TERRITORIAL);
    $datin["descriptoresTematicos"] = Parametros::getParametros(Constante::DESCRIPTORES_TEMATICOS);
    $datin["nodos"] = Parametros::getParametros(Constante::NODO);
    $datin["fuentesInformacion"] = Parametros::getParametros(Constante::FUENTE_INFORMACION);

    /* fin lista */

    $datin["documentos"]=Documento::listaDocumentos();

    $datos = [
        "title" => "CMS",
        "saludo" => "Bienvenido ".$nombres." ".$ape_paterno." ".$ape_materno,
        "titulo" => "BIENVENIDO A LA ADMINISTRACIÓN DE DOCUMENTO",
        "idusuario" => $idusuario,
        "idempleado" => $idempleado,
        "id_unidad_organica" => $id_unidad_organica
    ];
    $datin["datos"]=$datos;
    $this->view->render($response, 'cms/contenidos/documentos.twig', $datin);
    return $response;
}

public function NormaTematica(Request $request, Response $response, $args)
{
  
    try {

        $url = $args['tematica'];
        $datin["tematica"]=Tematica::getTematicaByUrl($url);
        $idTematica = $datin["tematica"]["data"]["idTematica"];
        $datin["indicadores"]=Indicador::ListarIndicadorByIdTematica($idTematica);


        //extraer data de los indicadores del agua
        //$datin["meses"] = Parametros::getParametros(3);

        $mensaje ="se listo correctamente los indicadores";
        $estado = true;

          
      } catch (\ErrorException $e) {
          $mensaje="Algo no salio muy bien";
          $estado=false;
      }

      $datin["mensaje"]= $mensaje;
      $datin["success"] = $estado;
      $datin["parametros"] = ['title'=>"Estadísticas :: emmsa", 'titulo' => "Estadísticas Ambientales" ];
      //$obj1 = JsonRenderer::render($response,200,$datin);
      //var_dump($datin);
      //exit;
      $this->view->render($response, "emmsa/Norma.twig", $datin);
      return $response;
}


public function NormaReporte(Request $request, Response $response, $args) {
    try {
        $datosParam = $request->getQueryParams();

        $datos=RegistroIndicador::NormaReporte($datosParam);

        $j=0; //distrito
        $k=0;
        $mes=$datosParam["anio"];
        $idDistritos=$datosParam["distrito"];

        $resumen=Array();
        $etiquetas=Array();
        $idDistrito=Array();
        $unidad="";
            foreach($datos["data"] as $dato){
                $keys      = array_keys($dato);
                $arraySize = count($dato); //12 columnas
                if (! in_array($dato["idDistrito"],$idDistrito)) {
                    $etiquetas[$j]=$dato["distrito"];
                    $idDistrito[$j]=$dato["idDistrito"];
                }
                $unidad=$dato["unidad"];
                for( $i=0; $i < $arraySize; $i++ ) {
                    if (in_array($dato["idDistrito"],$idDistritos)) {
                        $porciones = explode("-", $keys[$i]); //valida por anio
                        if(count($porciones)>1){
                            if (in_array($porciones[1],$mes)) {
                                $nombre=$porciones[1];
                                $datito[$j][$keys[$i]]=Array(intval($dato[$keys[$i]]));
                            }
                        }
                    }
                }
                
                $j++;
            }
          
            $resultados=Array();

            for ($r=0; $r < count($datito); $r++) { 
                # code...
                $resultados = array_merge_recursive($resultados, $datito[$r]);
            }
  
            $datazo=Array();
                $keys      = array_keys($resultados);

                $arraySize = count($resultados);
                $k=0;

                for( $i=0; $i < $arraySize; $i++ ) {

                    $porciones = explode("-", $keys[$i]);
                    
                    if(count($porciones)>1){
                        if (in_array($porciones[1],$mes)) {
                            $datazo[$k]=Array(
                                "name"=> $keys[$i],
                                "data"=> $resultados[$keys[$i]]
                            );
                            $k++;
                        }
                    }
                }
        $datin["etiquetas"]= $etiquetas;
        $datin["unidad"]= $unidad;
        $datin["datos"]= $datazo;  
        $estado = true;
    } catch (ErrorException $e) {
        $mensaje = "Error -> ".$e;
        $estado = false;
        $accion = false;
    }

    return Acl::RespuestaJSON($response,$datin);
}

public function NormaReporteParametros(Request $request, Response $response, $args) {
    try {
        $idIndicador = $request->getQueryParam('idIndicador');

        //listar los distritos de acuerdo al indicador
        $datin["distritos"]=Distrito::ListarDistritosbyReporteIndicador($idIndicador);
        $datin["anio"]=RegistroIndicador::ListarAniobyReporteIndicador($idIndicador);
        $estado = true;
        $mensaje = "Se listo correctamente";
    } catch (ErrorException $e) {
        $mensaje = "Error -> ".$e;
        $estado = false;
        $accion = false;
    }
    $datin["success"]=$estado;
    $datin["message"]=$mensaje;

    return Acl::RespuestaJSON($response,$datin);
}


/* fin emmsa */

    public function contenido(Request $request, Response $response, $args)
    {
  
        try {


           // $url = 'http://www.munlima.gob.pe/';
           // return $response->withRedirect($url);


           // $popup = ContenidoControlador::getContenidoPopups();
           // var_dump($popup);
           //exit;

 
            $mensaje ="se elimino correctamente el usuario y la persona";
              $estado = true;
  
              
          } catch (\ErrorException $e) {
              $mensaje="Algo no salio muy bien";
              $estado=false;
          }

          $datin["mensaje"]= $mensaje;
          $datin["success"] = $estado;
          $datin["parametros"] = ['title'=>"Plantilla Interna :: emmsa", 'titulo' => "Plantilla Interna" ];
          //$obj1 = JsonRenderer::render($response,200,$datin);
          //var_dump($datin);
          //exit;
          $this->view->render($response, "emmsa/contenido.twig", $datin);
          return $response;

    }


    public function normas(Request $request, Response $response, $args)
    {
  
        try {


           // $url = 'http://www.munlima.gob.pe/';
           // return $response->withRedirect($url);


           // $popup = ContenidoControlador::getContenidoPopups();
           // var_dump($popup);
           //exit;

            $nroRes=Constante::NOTIC_NRO_RESULTADOS_DEFAULT;
            // $datin["contenidos"] = ContenidoControlador::getListadoNoticiasPortada($nroRes);

            $datin["contenidos"] = GestContenido::getListadoNoticiasPortada($nroRes);


            $datin["popups"] = ContenidoControlador::getContenidoPopups();
            $datin["novedades"] = ContenidoControlador::getNovedadesMiraflores();
            $datin["agenda"] = ContenidoControlador::getAgendaPortada(3);
            $datin["pathmunlima"]=Constante::PATHMUNLIMA;
 
            $mensaje ="se elimino correctamente el usuario y la persona";
              $estado = true;
  
              
          } catch (\ErrorException $e) {
              $mensaje="Algo no salio muy bien";
              $estado=false;
          }

          $datin["mensaje"]= $mensaje;
          $datin["success"] = $estado;
          $datin["parametros"] = ['title'=>"Normas :: emmsa", 'titulo' => "Normas Ambientales" ];
          //$obj1 = JsonRenderer::render($response,200,$datin);
          //var_dump($datin);
          //exit;
          $this->view->render($response, "emmsa/normas.twig", $datin);
          return $response;

    }

    public function Norma(Request $request, Response $response, $args)
    {
  
        try {

 
            $mensaje ="se elimino correctamente el usuario y la persona";
              $estado = true;
  
              
          } catch (\ErrorException $e) {
              $mensaje="Algo no salio muy bien";
              $estado=false;
          }

          $datin["mensaje"]= $mensaje;
          $datin["success"] = $estado;
          $datin["parametros"] = ['title'=>"Estadísticas :: emmsa", 'titulo' => "Estadísticas Ambientales" ];
          //$obj1 = JsonRenderer::render($response,200,$datin);
          //var_dump($datin);
          //exit;
          $this->view->render($response, "emmsa/Norma.twig", $datin);
          return $response;

    }



        /* inicio de la pagina web*/
        public function geolocalizacion(Request $request, Response $response, $args)
        {
      
            try {
                
                $ruc="20131380951";
                $token="8c076ab2d795c792c8dd3749ebe29df1";
                $fecha="20190227";
                $path1 = $ruc.$token.$fecha;
                $clave=md5($path1);
        
                $url="http://190.102.145.252:8001/api/";
        
                //$url="https://digital.miraflores.gob.pe:8443/miraflores/getDocTramiteByWeb.muni";
                /*recibo los parametros del login*/    
                $dato = array(
                    "token" => $clave,
                    "format" => "json"
                );
            

            $datosM= Acl::curlGET($url,$dato);
        
            //var_dump($datosM);
            //exit;
        



               // $popup = ContenidoControlador::getContenidoPopups();
               // var_dump($popup);
               // exit;
    
                $nroRes=Constante::NOTIC_NRO_RESULTADOS_DEFAULT;
                // $datin["contenidos"] = ContenidoControlador::getListadoNoticiasPortada($nroRes);
                $datin["contenidos"] = GestContenido::getListadoNoticiasPortada($nroRes);
                $datin["popups"] = ContenidoControlador::getContenidoPopups();
                $datin["novedades"] = ContenidoControlador::getNovedadesMiraflores();
                $datin["pathmunlima"]=Constante::PATHMUNLIMA;
     
                $mensaje ="se elimino correctamente el usuario y la persona";
                  $estado = true;
      
                  
              } catch (\ErrorException $e) {
                  $mensaje="Algo no salio muy bien";
                  $estado=false;
              }
              
              $datin["mensaje"]= $mensaje;
              $datin["success"] = $estado;
              // $datin["parametros"] = ['title'=>"Bienvenido :: Overseas Perú", 'titulo' => "BIENVENIDO A LOS SERVICIOS DE ATENCIÓN VIRTUAL" ];
              //$obj1 = JsonRenderer::render($response,200,$datin);
              //var_dump($datin);
              //exit;
              $this->view->render($response, "weblima/plantilla-map.twig", $datin);
              return $response;
    
        }





        /*Totem publicitario*/
public function totempublicitario(Request $request, Response $response, $args){
   //$datin["conten_audio"]=$conten_audio;
   $datin["c_noticia"]=["contenido"=>"asi queda"];
   $this->view->render($response, "app/totempublicidad.twig", $datin);
   return $response;
}

/*Totem publicitario*/
public function toteminfo(Request $request, Response $response, $args){
    //$datin["conten_audio"]=$conten_audio;
    $datin["c_noticia"]=["contenido"=>"asi queda"];
    $this->view->render($response, "app/marcototeminfo.twig", $datin);
    return $response;
 }

 /*Totem publicitario*/
public function totemslider(Request $request, Response $response, $args){
    //$datin["conten_audio"]=$conten_audio;
    $datin["c_noticia"]=["contenido"=>"asi queda"];
    $this->view->render($response, "app/totemslider.twig", $datin);
    return $response;
 }

 /*Totem publicitario*/
public function totemvideo(Request $request, Response $response, $args){
    //$datin["conten_audio"]=$conten_audio;
    $datin["c_noticia"]=["contenido"=>"asi queda"];
    $this->view->render($response, "app/totemvideo.twig", $datin);
    return $response;
 }


/* Agenda Cultural (ACTIVIDAD) en detalle */

public function agendacultural(Request $request, Response $response, $args)
{

    if(isset($args['idContenido'])) $idContenido = $args['idContenido'];

    $dataAgenda = NoticiaControlador::getVerAgendaDetalle($idContenido);
    $datin["dataAgenda"]=$dataAgenda;

    $dataMedia = NoticiaControlador::getListadoMediaAgenda($idContenido);
    $datin["dataMedia"]=$dataMedia;
    
    $dataFileAgenda = NoticiaControlador::AllFileContenido($datin["dataAgenda"]["data"]["N_ID_CONTENIDO"]);
    $datin["dataFileAgenda"]=$dataFileAgenda;

    $dataAgendaAll = NoticiaControlador::getListadoAgendaAll(4);
    $datin["dataAgendaAll"]=$dataAgendaAll;

    $datin["pathmunlima"] = Constante::PATHMUNLIMA;

    $datin["c_agenda"]=utf8_encode($dataAgenda["data"]["C_CONTENIDO"]);
  
    $this->view->render($response, "weblima/agendacultural.twig", $datin);
    return $response;
}

 
/* noticia en detalle */

public function noticia(Request $request, Response $response, $args)
{

    if(isset($args['idContenido'])) $idContenido = $args['idContenido'];

    // $dataNoticias = NoticiaControlador::getListadoNoticiasDetalle($idContenido);
    // $datin["dataNoticias"]=$dataNoticias;

    $dataNoticia = NoticiaControlador::getVerNoticiaDetalle($idContenido);
    $datin["dataNoticia"]=$dataNoticia;

    // $dataPublicidad = NoticiaControlador::getVerPublicidad();
    // $datin["dataPublicidad"]=$dataPublicidad;

    $dataMedia = NoticiaControlador::getListadoMediaNoticia($idContenido);
    $datin["dataMedia"]=$dataMedia;
    
    $dataFileNoticia = NoticiaControlador::AllFileContenido($datin["dataNoticia"]["data"]["N_ID_CONTENIDO"]);
    $datin["dataFileNoticia"]=$dataFileNoticia;

    $dataNoticiasAll = NoticiaControlador::getListadoNoticiasAll(4);
    $datin["dataNoticiasAll"]=$dataNoticiasAll;

    $datin["pathmunlima"] = Constante::PATHMUNLIMA;

   //$datin["conten_audio"]=$conten_audio;
    $datin["c_noticia"]=utf8_encode($dataNoticia["data"]["C_CONTENIDO"]);


//$datin["algo"]="algo";    
$this->view->render($response, "weblima/noticia.twig", $datin);
    return $response;
}


/* obra en detalle */

public function obra(Request $request, Response $response, $args)
{

    if(isset($args['idContenido'])) $idContenido = $args['idContenido'];

    $dataObra = NoticiaControlador::getVerObraDetalle($idContenido);
    $datin["dataNoticia"]=$dataObra;

    $dataMedia = NoticiaControlador::getListadoMediaObra($idContenido);
    $datin["dataMedia"]=$dataMedia;

    $dataFileObra = NoticiaControlador::AllFileContenido($datin["dataNoticia"]["data"]["N_ID_CONTENIDO"]);
    $datin["dataFileNoticia"]=$dataFileObra;
    
    $dataObraAll = NoticiaControlador::getListadoObrasAll(4);
    $datin["dataNoticiasAll"]=$dataObraAll;

    $datin["pathmunlima"] = Constante::PATHMUNLIMA;

   //$datin["conten_audio"]=$conten_audio;
    $datin["c_noticia"]=utf8_encode($dataObra["data"]["C_CONTENIDO"]);

    $this->view->render($response, "weblima/obra.twig", $datin);
    return $response;
}

/* municipalidad y opciones*/

public function municipalidad(Request $request, Response $response, $args)
{
    //Variables
    $datin=Array();
    /*validando los argumentos que recibimos*/
    if(isset($args['idPadre'])) $idPadre = $args['idPadre'];
	if(isset($args['idContenido'])) $idContenido = $args['idContenido'];
	if(isset($args['idHijo'])) $idHijo = $args['idHijo'];
    
    $servicio="municipalidad";
    $tituloservicio="MUNICIPALIDAD";
    $datin=MunlimaControlador::plantillaUno($args,$servicio,$tituloservicio);

    $this->view->render($response, "weblima/municipalidad.twig", $datin);
    return $response;
}


/* ciudad y opciones */
public function ciudad(Request $request, Response $response, $args)
{
    //Variables
    $datin=Array();
    /*validando los argumentos que recibimos*/
    if(isset($args['idPadre'])) $idPadre = $args['idPadre'];
    if(isset($args['idContenido'])) $idContenido = $args['idContenido'];
    if(isset($args['idHijo'])) $idHijo = $args['idHijo'];
    
    $servicio="ciudad";
    $tituloservicio="LIMA";
    $datin=MunlimaControlador::plantillaUno($args,$servicio,$tituloservicio);

    $this->view->render($response, "weblima/ciudad.twig", $datin);
    return $response;
}

/* trámites y opciones */
public function tramites(Request $request, Response $response, $args)
{
    //Variables
    $datin=Array();
    /*validando los argumentos que recibimos*/
    if(isset($args['idPadre'])) $idPadre = $args['idPadre'];
    if(isset($args['idContenido'])) $idContenido = $args['idContenido'];
    if(isset($args['idHijo'])) $idHijo = $args['idHijo'];
    
    $servicio="tramites";
    $tituloservicio="TRÁMITES";
    $datin=MunlimaControlador::plantillaUno($args,$servicio,$tituloservicio);

    $this->view->render($response, "weblima/tramites.twig", $datin);
    return $response;
}

/* extras y opciones */
public function extras(Request $request, Response $response, $args)
{
    //Variables
    $datin=Array();
    /*validando los argumentos que recibimos*/
    if(isset($args['idPadre'])) $idPadre = $args['idPadre'];
    if(isset($args['idContenido'])) $idContenido = $args['idContenido'];
    if(isset($args['idHijo'])) $idHijo = $args['idHijo'];
    
    $servicio="extras";
    $tituloservicio="EXTRAS";
    $datin=MunlimaControlador::plantillaUno($args,$servicio,$tituloservicio);

    $this->view->render($response, "weblima/extras.twig", $datin);
    return $response;
}

/* extras y opciones */
public function atencion(Request $request, Response $response, $args)
{
    //Variables
    $datin=Array();
    /*validando los argumentos que recibimos*/
    if(isset($args['idPadre'])) $idPadre = $args['idPadre'];
    if(isset($args['idContenido'])) $idContenido = $args['idContenido'];
    if(isset($args['idHijo'])) $idHijo = $args['idHijo'];
    
    $servicio="atencion";
    $tituloservicio="ATENCIÓN AL CIUDADANO";
    $datin=MunlimaControlador::plantillaUno($args,$servicio,$tituloservicio);

    $this->view->render($response, "weblima/extras.twig", $datin);
    return $response;
}


/* cultura y opciones */
public function cultura(Request $request, Response $response, $args)
{
    //Variables
    $datin=Array();
    /*validando los argumentos que recibimos*/
    if(isset($args['idPadre'])) $idPadre = $args['idPadre'];
    if(isset($args['idContenido'])) $idContenido = $args['idContenido'];
    if(isset($args['idHijo'])) $idHijo = $args['idHijo'];
    
    $servicio="cultura";
    $tituloservicio="CULTURA";
    $datin=MunlimaControlador::plantillaUno($args,$servicio,$tituloservicio);

    $this->view->render($response, "weblima/cultura.twig", $datin);
    return $response;
}

/* libro de reclamaciones y opciones */
public function libroreclamaciones(Request $request, Response $response, $args)
{
    //Variables
    $datin=Array();
    /*validando los argumentos que recibimos*/
    if(isset($args['idPadre'])) $idPadre = $args['idPadre'];
    if(isset($args['idContenido'])) $idContenido = $args['idContenido'];
    if(isset($args['idHijo'])) $idHijo = $args['idHijo'];
    
    $servicio="libroreclamaciones";
    $tituloservicio="LIBRO DE RECLAMACIONES";
    $datin=MunlimaControlador::plantillaUno($args,$servicio,$tituloservicio);

    $this->view->render($response, "weblima/libroreclamaciones.twig", $datin);
    return $response;
}

/* lima TV y opciones */
public function limatv(Request $request, Response $response, $args)
{
    //Variables
    $datin=Array();
    /*validando los argumentos que recibimos*/
    if(isset($args['idPadre'])) $idPadre = $args['idPadre'];
    if(isset($args['idContenido'])) $idContenido = $args['idContenido'];
    if(isset($args['idHijo'])) $idHijo = $args['idHijo'];
    
    $servicio="limatv";
    $tituloservicio="LIMA TV";
    $datin=MunlimaControlador::plantillaUno($args,$servicio,$tituloservicio);

    $this->view->render($response, "weblima/limatv.twig", $datin);
    return $response;
}

/* lima TV y opciones */
public function consultasenlinea(Request $request, Response $response, $args)
{
    //Variables
    $datin=Array();
    /*validando los argumentos que recibimos*/
    if(isset($args['idPadre'])) $idPadre = $args['idPadre'];
    if(isset($args['idContenido'])) $idContenido = $args['idContenido'];
    if(isset($args['idHijo'])) $idHijo = $args['idHijo'];
    
    $servicio="consultasenlinea";
    $tituloservicio="CONSULTAS EN LÍNEA";
    $datin=MunlimaControlador::plantillaUno($args,$servicio,$tituloservicio);

    $this->view->render($response, "weblima/consultasenlinea.twig", $datin);
    return $response;
}

/* catalogonoticias */
public function catalogonoticias(Request $request, Response $response, $args)
{
    //Variables
    $datin = Array();

    if(isset($args['limite'])) $limite = $args['limite'];
    
    $servicio = "catalogonoticias";
    $tituloservicio = "CATÁLOGOS DE NOTICIAS";
    $datin = MunlimaControlador::PlantillaTres($args,$servicio,$tituloservicio);

    $this->view->render($response, "weblima/catalogonoticias.twig", $datin);
    return $response;
}

/* catalogoobrass */
public function catalogoobras(Request $request, Response $response, $args)
{
    //Variables
    $datin = Array();

    if(isset($args['limite'])) $limite = $args['limite'];
    
    $servicio = "catalogoobras";
    $tituloservicio = "CATÁLOGO DE OBRAS";
    $datin = MunlimaControlador::PlantillaTres($args,$servicio,$tituloservicio);

    $this->view->render($response, "weblima/catalogoobras.twig", $datin);
    return $response;
}

public static function PlantillaTres($args,$servicio,$tituloservicio)
{   
    //Variables
    $datin = Array();
    $limite = "";
    $curTotal = "";
    $curGrupo = "";

    /*validando los argumentos que recibimos*/
    if(isset($args['limite'])) $limite = $args['limite'];

    if ($servicio == "catalogonoticias") {
        $curTotal = NoticiaControlador::NumeroTotalNoticias();
        $curGrupo = NoticiaControlador::getCatalogoNoticias($limite);
    } else if ($servicio == "catalogoobras") {
        $curTotal = NoticiaControlador::NumeroTotalObras();
        $curGrupo = NoticiaControlador::getCatalogoObras($limite);
    }

    $datin["curTotal"] = $curTotal;
    $datin["curGrupo"] = $curGrupo;
    $datin["limite"] = $limite;
    $datin["servicio"] = $servicio;
    $datin["tituloservicio"] = $tituloservicio;
    $datin["pathmunlima"] = Constante::PATHMUNLIMA;

    return $datin;
}

/* servicio y opciones*/

public function servicio(Request $request, Response $response, $args)
{
    //Variables
    $datin=Array();
    /*validando los argumentos que recibimos*/
    if(isset($args['idPadre'])) $idPadre = $args['idPadre'];
	if(isset($args['idContenido'])) $idContenido = $args['idContenido'];
	if(isset($args['idHijo'])) $idHijo = $args['idHijo'];
    
    $servicio="servicio";
    $datin=MunlimaControlador::plantillaDos($args,$servicio);

    $this->view->render($response, "weblima/servicio.twig", $datin);
    return $response;
}


public static function plantillaUno($args,$servicio,$tituloservicio)
{   
    //Variables
    $datin=Array();
	$idPadre = "";
	$idContenido = "";
	$idConten = "";
	$idHijo = "";
	$idUrl="";
	$titleVentana = "Servicios";
    $titleLogo = "";
    $idTmpFile = "";

    /*validando los argumentos que recibimos*/
    if(isset($args['idPadre'])) $idPadre = $args['idPadre'];
	if(isset($args['idContenido'])) $idContenido = $args['idContenido'];
	if(isset($args['idHijo'])) $idHijo = $args['idHijo'];
	$f_fecha = date('d-m-Y');
	$anio = date('Y');
	$mes = date('m');
	$dia = date('d');
	$conten_audio = "off";
    $c_contenido="";
    $datin["idPadre"]=$idPadre;
    $datin["idContenido"]=$idContenido;
    $datin["idHijo"]=$idHijo;
    $datin["tituloservicio"]=$tituloservicio;
    $datin["pathmunlima"]=Constante::PATHMUNLIMA;


    if($idPadre==5723){
		header("Location: https://digital.miraflores.gob.pe:8443/seh/login");
	}

    /* ################################### */
	if($idContenido==8351){
		header("Location: _sesionesdeconcejo.php");
	}
	/* ################################### */

    	/* PAGINA PARA BUSQUEDAS */

	if($idPadre != "" || $idContenido != ""){
		/*urlbusqueda = "http://" & request.servervariables("SERVER_NAME") & request.servervariables("SCRIPT_NAME") & "?" &request.querystring()
		urlbusqueda = replace(urlbusqueda,"'","")*/
		
		if($idPadre != "")  $idUrl = $idPadre;
		if($idContenido != "") $idUrl = $idContenido;
		/*conexSQL.execute("update GEST_CONTENIDOS set C_URL_BUSQUEDA = '"& urlbusqueda &"', C_IP = '"& ip &"', F_FECHA_VISITANTE = GETDATE() WHERE N_ID_CONTENIDO = '"& idurl &"' ")*/
		$idConten = $idUrl;
	}

    /*==========================================*/
    
    $curPadre = ContenidoControlador::getCurPadreTmpl1($idPadre);
    
   
    //  $contenido->getCurPadreTmpl1($idPadre);
	//print_r($curPadre);
	if($curPadre["success"]){
		$titleLogo = $curPadre["titleLogo"];
		$titleVentana = $curPadre["titleVentana"];
        $c_contenido = $curPadre["c_contenido"];
        $c_titulo = $curPadre["c_titulo"];
        $datin["curPadre"]=$curPadre;
        $datin["menuNav"]=$curPadre["data"]["C_NOMBRE"];
    }
    
    //var_dump($curPadre);
    
	//print_r($curPadre);
    $curLogo = ContenidoControlador::getCurLogo($idPadre);
    //servicio
    //$servicio="municipalidad";
    
    $curContenMenu = ContenidoControlador::getMenu1($idPadre,$idContenido,$idHijo,$servicio);
    // var_dump($curContenMenu);

    $template="";
    $menuNav="";
    if ($idPadre==4958) {
        
        if ($servicio=="servicio") {
            $template=Constante::TEMPLATE2;
        } else {
            $template=Constante::TEMPLATE1;
        }

    } else {
        if ($servicio=="servicio") {
            if ($idPadre==7186) {
                $template=Constante::TEMPLATE1;
            } else {
                $template=Constante::TEMPLATE2;
            }
        } else {
            $template=Constante::TEMPLATE1;
        }
    }

   
    $tmp = "";
    $tmphijo = "";

	//$curContenido = ContenidoControlador::getCurContenido($idPadre, $idContenido, $curContenMenu["success"], ($idPadre==4958?Constante::TEMPLATE2:Constante::TEMPLATE1));
    $curContenido = ContenidoControlador::getCurContenido($idPadre, $idContenido, $curContenMenu["success"], $template);
    //var_dump($curContenido);
   

    $datin["curLogo"]=$curLogo;
    $datin["curContenMenu"]=$curContenMenu;
    


    if($curContenido["success"] && $idContenido==""){
		$idContenido = $curContenido["idContenido"];
        $idConten = $idContenido;
        $datin["curContenido"]=$curContenido;
        $tmp = $datin["curContenido"]['idContenido'];
	}
	

    $curConten = ContenidoControlador::getCurConten($idContenido);
	if($curConten["success"]){
		$titleVentana = $titleVentana.": ".$curConten["titleVentana"];
        $c_contenido = $curConten["c_contenido"];
        $c_titulo = $curConten["titleVentana"];
        $datin["curConten"]=$curConten;
        $tmp = $datin["curConten"]["data"]['N_ID_CONTENIDO'];
    }
    
    $curHijo = ContenidoControlador::getCurHijo($idHijo);

	if($curHijo["success"] && !empty($curHijo["data"])){
        $titleVentana = $curHijo["data"][0]["C_TITULO"];
        $datin["curHijo"]=$curHijo;
        $c_contenido = $curHijo["data"][0]["C_CONTENIDO"];
        $c_titulo = $titleVentana;
        $tmphijo = $curHijo["data"][0]['N_ID_CONTENIDO'];
    }


	$coveriAudio = ContenidoControlador::getCurveriAudio($idConten);
	if($coveriAudio["success"]){		
		$urlAudio = $coveriAudio["data"]["C_RUTA"].$coveriAudio["data"]["C_NOMBRE"];
        $conten_audio = "on";
        $datin["coveriAudio"]=$coveriAudio;
    }

	$fecha = $dia." de ".$mes." ".$anio;
    $datin["fecha"]=$fecha;
    $datin["conten_audio"]=$conten_audio;
    $datin["c_contenido"]=utf8_encode($c_contenido);
    $datin["c_titulo"]=utf8_encode($c_titulo);
    $datin["tmp"]=$tmp;
    $datin["tmphijo"]=$tmphijo;
    

    if ($datin["idPadre"] == 34493) {
        $datin["contenido_null"] = GestContenido::curContenidoNull($datin["idPadre"]);
    } else{
        $datin["contenido_null"] = GestContenido::curContenidoNull($tmp);
    }

    if ($tmp == 11097) {
        for ($i=0; $i < sizeof($curContenMenu["data"]); $i++) {
            if ($curContenMenu["data"][$i]["ID_CONTENIDO"] == 11097) {
                $curContenMenuNuevo[0] = ContenidoControlador::getMenu1(11097,'','',$servicio);
            }
        }

        $datin["nuevo_menu"] = $curContenMenuNuevo[0]["data"];

        for ($i=0; $i < sizeof($curContenMenuNuevo[0]["data"]); $i++) { 
            if ($curContenMenuNuevo[0]["data"][$i]["ID_CONTENIDO"] == 32568) {
                $vrtmp = ContenidoControlador::getMenu1(32568,'','',$servicio);
                $datin["nuevo_submenu_32568"] = array_reverse($vrtmp["data"]);
            }
            if ($curContenMenuNuevo[0]["data"][$i]["ID_CONTENIDO"] == 27849) {
                $vrtmp = ContenidoControlador::getMenu1(27849,'','',$servicio);
                $datin["nuevo_submenu_27849"] = array_reverse($vrtmp["data"]);
            }
            if ($curContenMenuNuevo[0]["data"][$i]["ID_CONTENIDO"] == 11093) {
                $vrtmp = ContenidoControlador::getMenu1(11093,'','',$servicio);
                $datin["nuevo_submenu_11093"] = array_reverse($vrtmp["data"]);
            }
        }
    }

    /*-----------------------------------*/
    if ($datin["idPadre"] != "") {
        $idTmpFile = $datin["idPadre"];
    } 
    /*-----------------------------------*/
    if ($datin["idContenido"] != "") {
        $idTmpFile = $datin["idContenido"];
    } 
    /*-----------------------------------*/
    if ($datin["idHijo"] != "") {
        $idTmpFile = $datin["idHijo"];
    }
    /*-----------------------------------*/
    if ($servicio == "limatv") {
        $abc = "";
        if ($datin["idPadre"] == 11123 && $datin["idContenido"] == "" && $datin["idHijo"] == "") {
            $abc = (int)$datin["curContenMenu"]["data"][0]["ID_CONTENIDO"];
            $dataFile = NoticiaControlador::VideosLimaTv($abc);
            $datin["dataFile"] = $dataFile;
        } else{
            $dataFile = NoticiaControlador::VideosLimaTv($idTmpFile);
            $datin["dataFile"] = $dataFile;
        }
        
    } else{
        $dataFile = NoticiaControlador::AllFileContenido($idTmpFile);
        $datin["dataFile"] = $dataFile;
    }
    /*-----------------------------------*/
    if ($idTmpFile == 11092) {
        $datin["menu_ciudad"] = ContenidoControlador::getMenu1(12,'','','municipalidad');
    }
    /*-----------------------------------*/
    
    /*-----------------------------------*/
    $datin["idTmpFile"] = $idTmpFile;
    
    return $datin;
}


public function plantillaDos($args,$servicio)
{



    $datin=Array();

    if(isset($_GET["idcontenido"]) && ($_GET["idcontenido"]==9530 || $_GET["idcontenido"]==9963 || $_GET["idcontenido"]==9978)){
		session_start();
		include("includes/simple-php-captcha.php");
		$_SESSION['captcha'] = simple_php_captcha(array(
		    'min_length' => 5,
		    'max_length' => 5,
		    'characters' => 'ABCDEFGHJKLMNPRSTUVWXYZabcdefghjkmnprstuvwxyz23456789',
		    'min_font_size' => 18,
		    'max_font_size' => 18,
		    'color' => '#666',
		    'angle_min' => 0,
		    'angle_max' => 10,
		    'shadow' => true,
		    'shadow_color' => '#fff',
		    'shadow_offset_x' => -1,
		    'shadow_offset_y' => 1
		));
	}


	//Filtro de codigo malicioso
	$dato = $_SERVER['QUERY_STRING'];
	Acl::filtraCodigoMalicioso($dato);

	//Variables
	$idPadre = 0;
	$idContenido = 0;
	$idConten = 0;
	$idHijo = 0;

	$titleVentana = "Municipalidad ";
	$titleLogo = "";

  

    if(isset($args['idPadre'])) $idPadre = $args['idPadre'];
	if(isset($args['idContenido'])) $idContenido = $args['idContenido'];
	if(isset($args['idHijo'])) $idHijo = $args['idHijo'];
	$f_fecha = date('d-m-Y');
	$anio = date('Y');
	$mes = date('m');
	$dia = date('d');
    $conten_audio = "off";
    




    $datin["idPadre"]=$idPadre;
    $datin["idContenido"]=$idContenido;
    $datin["idHijo"]=$idHijo;
    $datin["servicio"]=$servicio;

	//$contenido = new ContenidoController();
   
	$mesDato = 0;

	if($idContenido==9639){
		$mesDato = 6;
	}else if($idContenido==9681){
		$mesDato = 7;
	}


	//$fotos = $contenido->getContenidoFotosFormMujer($mesDato);
	
    //$curPadre = $contenido->getCurPadreTmpl2($idPadre);
    $curPadre = ContenidoControlador::getCurPadreTmpl2($idPadre);
    $datin["curPadre"]=$curPadre;
    //exit;
	if($curPadre["success"]){
		$titleLogo = $curPadre["titleLogo"];
		$titleVentana = $curPadre["titleVentana"];
		$idPadreAtras = $curPadre["idPadreAtras"];
	}
	
    $curLogo = ContenidoControlador::getCurLogo($idPadre);
    $datin["curLogo"]=$curLogo;
    //$curContenMenu = $contenido->getCurContenMenu($idPadre);


    //$curContenMenu = ContenidoControlador::getCurContenMenu($idPadre);

    $curContenMenu = ContenidoControlador::getMenu1($idPadre,$idContenido,$idHijo,$servicio);

    $datin["curContenMenu"]=$curContenMenu;


 
	
    //$curContenido = $contenido->getCurContenido($idPadre, $idContenido, $curContenMenu["success"], Constante::TEMPLATE2);
    $curContenido = ContenidoControlador::getCurContenido($idPadre, $idContenido, $curContenMenu["success"], Constante::TEMPLATE2);
    $datin["curContenido"]=$curContenido;

	if($curContenido["success"] && $idContenido==""){
		$idContenido = $curContenido["idContenido"];
		$idConten = $idContenido;
	}
	
    //$curHijo = $contenido->getCurHijo($idHijo);
    $curHijo = ContenidoControlador::getCurHijo($idHijo);
    $datin["curHijo"]=$curHijo;
   
	if($curHijo["success"]){
		$titleVentana = $curHijo["titleVentana"];
	}

    //$curConten = $contenido->getCurConten($idContenido);
    $curConten = ContenidoControlador::getCurConten($idContenido);
    $datin["curConten"]=$curConten;
	if($curConten["success"]){
		$titleVentana = $titleVentana.": ".$curConten["titleVentana"];
        $c_contenido = $curConten["c_contenido"];
        $datin["c_contenido"]=utf8_encode($c_contenido);
	}

    //$coveriAudio = $contenido->getCurveriAudio($idConten);
    $coveriAudio = ContenidoControlador::getCurveriAudio($idConten);
    $datin["coveriAudio"]=$coveriAudio;
	if($coveriAudio["success"]){		
		$urlAudio = $coveriAudio["data"]["C_RUTA"].$coveriAudio["data"]["C_NOMBRE"];
        $conten_audio = "on";
        $datin["conten_audio"]=$conten_audio;
	}

    $datin["titleVentana"]=$titleVentana;

    $fecha = $dia." de ".$mes." ".$anio;

    $datin["fecha"]=$fecha;
   
    return $datin;

    
}






public function planos(Request $request, Response $response, $args)
{
    $datin["data"]=["titulo"=>"Edwin"];
    $this->view->render($response, "app/planos.twig", $datin);
    return $response;

}

public function regidores(Request $request, Response $response, $args)
{
    $datin["data"]=["titulo"=>"Edwin"];
    $this->view->render($response, "app/regidores.twig", $datin);
    return $response;

}

public function leyes(Request $request, Response $response, $args)
{
    $datin["data"]=["titulo"=>"Edwin"];
    $this->view->render($response, "app/leyes.twig", $datin);
    return $response;

}

public function publicaciones(Request $request, Response $response, $args)
{
    //Variables
    $datin=Array();
	$idPadre = "";
	$idContenido = "";
	$idConten = "";
	$idHijo = "";
	$idUrl="";
	$titleVentana = "Municipalidad ";
    $titleLogo = "";

    /*validando los argumentos que recibimos*/
    if(isset($args['idPadre'])) $idPadre = $args['idPadre'];
	if(isset($args['idContenido'])) $idContenido = $args['idContenido'];
	if(isset($args['idHijo'])) $idHijo = $args['idHijo'];
	$f_fecha = date('d-m-Y');
	$anio = date('Y');
	$mes = date('m');
	$dia = date('d');
	$conten_audio = "off";

    $datin["idPadre"]=$idPadre;
    $datin["idContenido"]=$idContenido;
    $datin["idHijo"]=$idHijo;


    if($idPadre==5723){
		header("Location: https://digital.miraflores.gob.pe:8443/seh/login");
	}

    /* ################################### */
	if($idContenido==8351){
		header("Location: _sesionesdeconcejo.php");
	}
	/* ################################### */

    	/* PAGINA PARA BUSQUEDAS */

	if($idPadre != "" || $idContenido != ""){
		/*urlbusqueda = "http://" & request.servervariables("SERVER_NAME") & request.servervariables("SCRIPT_NAME") & "?" &request.querystring()
		urlbusqueda = replace(urlbusqueda,"'","")*/
		
		if($idPadre != "")  $idUrl = $idPadre;
		if($idContenido != "") $idUrl = $idContenido;
		/*conexSQL.execute("update GEST_CONTENIDOS set C_URL_BUSQUEDA = '"& urlbusqueda &"', C_IP = '"& ip &"', F_FECHA_VISITANTE = GETDATE() WHERE N_ID_CONTENIDO = '"& idurl &"' ")*/
		$idConten = $idUrl;
	}

    /*==========================================*/
    
    $curPadre = ContenidoControlador::getCurPadreTmpl1($idPadre);
    
    //  $contenido->getCurPadreTmpl1($idPadre);
	//print_r($curPadre);
	if($curPadre["success"]){
		$titleLogo = $curPadre["titleLogo"];
		$titleVentana = $curPadre["titleVentana"];
        $c_contenido = $curPadre["c_contenido"];
        $datin["curPadre"]=$curPadre;
    }
    

	//print_r($curPadre);
    $curLogo = ContenidoControlador::getCurLogo($idPadre);
    //servicio
    $servicio="municipalidad";
    $curContenMenu = ContenidoControlador::getMenu1($idPadre,$idContenido,$idHijo,$servicio);
    //var_dump($curContenMenu);
	$curContenido = ContenidoControlador::getCurContenido($idPadre, $idContenido, $curContenMenu["success"], ($idPadre==4958?Constante::TEMPLATE2:Constante::TEMPLATE1));
    
    $datin["curLogo"]=$curLogo;
    $datin["curContenMenu"]=$curContenMenu;
    


    if($curContenido["success"] && $idContenido==""){
		$idContenido = $curContenido["idContenido"];
        $idConten = $idContenido;
        $datin["curContenido"]=$curContenido;
	}
	
    $curHijo = ContenidoControlador::getCurHijo($idHijo);


	if($curHijo["success"] && !empty($res)){
        $titleVentana = $curHijo["titleVentana"];
        $datin["curHijo"]=$curHijo;
    }


	$curConten = ContenidoControlador::getCurConten($idContenido);
	if($curConten["success"]){
		$titleVentana = $titleVentana.": ".$curConten["titleVentana"];
        $c_contenido = $curConten["c_contenido"];
        $datin["curConten"]=$curConten;
    }
    

	$coveriAudio = ContenidoControlador::getCurveriAudio($idConten);
	if($coveriAudio["success"]){		
		$urlAudio = $coveriAudio["data"]["C_RUTA"].$coveriAudio["data"]["C_NOMBRE"];
        $conten_audio = "on";
        $datin["coveriAudio"]=$coveriAudio;
    }
    




	$fecha = $dia." de ".$mes." ".$anio;
    $datin["fecha"]=$fecha;
    $datin["conten_audio"]=$conten_audio;
    $datin["c_contenido"]=utf8_encode($c_contenido);
    
    $this->view->render($response, "app/publicaciones.twig", $datin);
    return $response;

}

public function agenda(Request $request, Response $response, $args)
{
    $datin["data"]=["titulo"=>"Edwin"];
    $this->view->render($response, "app/agenda.twig", $datin);
    return $response;

}

public function tupa(Request $request, Response $response, $args)
{
    $datin["data"]=["titulo"=>"Edwin"];
    $this->view->render($response, "app/tupa.twig", $datin);
    return $response;

}

public function munibus(Request $request, Response $response, $args)
{
    $datin["data"]=["titulo"=>"Edwin"];
    $this->view->render($response, "app/munibus.twig", $datin);
    return $response;

}



    /* pagina de nosotros */
        public function nosotros(Request $request, Response $response, $args)
    {


$empleadores=Empleador::getEmpleadores();
        $datos = ['title'=>"Nosotros :: Overseas Perú", 'titulo' => "NOSOTROS", "empleadores"=>$empleadores];
        $this->view->render($response, 'web/nosotros.twig', $datos);
        return $response;
    }
 public function servicios(Request $request, Response $response, $args)
    {
        $datos = ['title'=>"Servicios", 'titulo' => "SERVICIOS" ];
        $this->view->render($response, 'web/servicios.twig', $datos);
        return $response;
    }

 public function workandtravel(Request $request, Response $response, $args)
    {

        /*extrayendo los mensajes de W&T*/
        $comentarios=Mensaje::getMensajeWTPublic(1, 1);
        $cantidad=Mensaje::getMensajeWTCount(1, 1);
        $empleadores=Empleador::getEmpleadores();

        $datos = ['title'=>"Work & Travel :: Overseas Perú", 'titulo' => "WORK & TRAVEL", "comentarios"=>$comentarios,"cantidad"=>$cantidad, "empleadores"=>$empleadores];
        $this->view->render($response, 'web/workandtravel.twig', $datos);
        return $response;
    }

     public function internshiptrainee(Request $request, Response $response, $args)
    {
        /*extrayendo los mensajes de W&T*/
        $comentarios=Mensaje::getMensajeWTPublic(1, 2);
        $cantidad=Mensaje::getMensajeWTCount(1, 2);
        $empleadores=Empleador::getEmpleadores();

        $datos = ['title'=>"Internship Trainee :: Overseas Perú", 'titulo' => "WORK & TRAVEL", "comentarios"=>$comentarios,"cantidad"=>$cantidad, "empleadores"=>$empleadores];
        $this->view->render($response, 'web/internshiptrainee.twig', $datos);
        return $response;
    }
      public function conversationclub(Request $request, Response $response, $args)
    {
        /*extrayendo los mensajes de W&T*/
        $comentarios=Mensaje::getMensajeWTPublic(1, 3);
        $cantidad=Mensaje::getMensajeWTCount(1, 3);
        $empleadores=Empleador::getEmpleadores();

        $datos = ['title'=>"Conversation club :: Overseas Perú", 'titulo' => "CONVERSATION CLUB", "comentarios"=>$comentarios,"cantidad"=>$cantidad, "empleadores"=>$empleadores];
        $this->view->render($response, 'web/conversationclub.twig', $datos);
        return $response;
    }
      public function taxes(Request $request, Response $response, $args)
    {
        /*extrayendo los mensajes de W&T*/
        $comentarios=Mensaje::getMensajeWTPublic(1, 4);
        $cantidad=Mensaje::getMensajeWTCount(1, 4);
        $empleadores=Empleador::getEmpleadores();

        $datos = ['title'=>"Taxes :: Overseas Perú", 'titulo' => "TAXES", "comentarios"=>$comentarios,"cantidad"=>$cantidad, "empleadores"=>$empleadores];
        $this->view->render($response, 'web/taxes.twig', $datos);
        return $response;
    }




     public function promociones(Request $request, Response $response, $args)
    {
        $datos = ['title'=>"Promociones :: Overseas Perú", 'titulo' => "PROMOCIONES" ];
        $this->view->render($response, 'web/promociones.twig', $datos);
        return $response;
    }

         public function testimonios(Request $request, Response $response, $args)
    {
           
        $testimonios = Testimonio::all()->toArray(); 
        $informacion = array();
  
        for ($i = 0; $i < count($testimonios); $i++) {
            for ($j = 0; $j < count($testimonios[$i]); $j++) {

                $nombres= Persona::where(["idUsuario"=>$testimonios[$i]["idUsuario"]])
                ->join('usuario', 'usuario.idPersona', '=', 'persona.idPersona')
                ->select("persona.nombres","persona.apellidos")
                ->get()->toArray();
                $nombrescompletos=$nombres[0]["nombres"]. " " .$nombres[0]["apellidos"];
                

                $url=Docadjunto::where(["idDocAdjunto"=>$testimonios[$i]["idDocAdjunto"]])
                ->select("docadjunto.urlAdjunto")
                ->get()->toArray();
                
                $informacion[$i]=[
                    "descripcion"=>$testimonios[$i]["descripcion"],
                    "nombres"=>$nombrescompletos,
                    "url"=>$url[0]["urlAdjunto"],
                    "calificacion"=>$testimonios[$i]["calificacion"],
                    "fechaViaje"=>$testimonios[$i]["fechaViaje"]
                ];
            }
        }
        
        
        $datos = ['title'=>"Testimonios :: Overseas Perú", 'titulo' => "TESTIMONIOS","testimonios"=>$informacion];
        $this->view->render($response, 'web/testimonios.twig', $datos);
        return $response;
    }

             public function galeria(Request $request, Response $response, $args)
    {
        $datos = ['title'=>"Galeria :: Overseas Perú", 'titulo' => "GALERIA DE FOTOS" ];
        $this->view->render($response, 'web/galeria.twig', $datos);
        return $response;
    }
                 public function contactenos(Request $request, Response $response, $args)
    {
        $datos = ['title'=>"Contactenos :: Overseas Perú", 'titulo' => "CONTACTENOS" ];
        $this->view->render($response, 'web/contactenos.twig', $datos);
        return $response;
    }

/*VISTAS PARA EL PORTAL INTERNO OVERSEAS*/

    public function formTestimonio(Request $request, Response $response, $args)
    {
        $datos = ['title'=>"Publicar Testimonio", 'titulo' => "FORMULARIO PARA PUBLICAR UN TESTIMONIO" ];
        $this->view->render($response, 'intranet/testimonio.twig', $datos);
        return $response;
    }

    public function formEmpleador(Request $request, Response $response, $args)
    {
        $empleadores=Empleador::getEmpleadores();
        $datos = ['title'=>"Registrar empleador", 'titulo' => "FORMULARIO PARA GESTIONAR EMPLEADORES","empleadores"=>$empleadores ];

        $this->view->render($response, 'intranet/empleador.twig', $datos);
        return $response;
    }
    
    public function formServicios(Request $request, Response $response, $args)
    {
        $idServicio=$args["idServicio"];
        $empleadores=Empleador::getEmpleadores();
        $servicios=Servicio::select("servicio.idServicio","servicio.nombre","pais.nombre as pais")
        ->join('pais', 'servicio.idPais', '=', 'pais.idPais')
        ->where(["idServicio"=>$idServicio])
        ->orderBy('servicio.idServicio', 'DESC')
        ->get()
        ->toArray();
        $requisitos=Requisitos::all()->toArray();
        $paises=Pais::all()->toArray();
        $catalogopasos=Catalogopaso::all()->toArray();
        
        $servicios1=Servicio::select("servicio.idServicio","servicio.nombre","pais.nombre as pais")
        ->join('pais', 'servicio.idPais', '=', 'pais.idPais')
        ->orderBy('servicio.idServicio', 'DESC')
        ->get()
        ->toArray();
        
     
        $condicion=["servicio.idServicio"=>$idServicio];
        $requisitosxservicio=Array();
       
       
            $requisitosxservicio=  Paso::select(
                "paso.idPaso",
                "servicio.nombre as servicio",
                "requisitos.nombre as requisito",
                "paso.orden",
                "paso.nombreDocumento",
                "docadjunto.urlAdjunto",
                "docadjunto.idDocAdjunto",
                "paso.estado",
                "requisitos.idRequisitos",
                "catalogopaso.*")
                ->join('servicio', 'paso.idServicio', '=', 'servicio.idServicio')
                ->join('requisitos', 'paso.idRequisitos', '=', 'requisitos.idRequisitos')
                ->join('catalogopaso', 'paso.idCatalogoPaso', '=', 'catalogopaso.idCatalogoPaso')
                ->leftjoin('docadjunto', 'paso.idDocAdjunto', '=', 'docadjunto.idDocAdjunto')
                ->where(["paso.idServicio"=>$idServicio])
                ->orderBy('catalogopaso.numero', 'ASC')
                ->orderBy('paso.orden', 'ASC')
                ->get()
                ->toArray();
        
        
                $datos = ['title'=>"Registrar servicios", 'titulo' => "AGREGAR Y MODIFICAR REQUISITOS DE ".$servicios[0]["nombre"],"empleadores"=>$empleadores, "servicios"=>$servicios, "paises"=>$paises, "requisitos"=>$requisitos
                    ,"listarequisitos"=>$requisitosxservicio, "idServicio"=>$idServicio, "servicios1"=>$servicios1,"catalogopasos"=>$catalogopasos
        ];
        
        $this->view->render($response, 'intranet/servicios-requisito.twig', $datos);
        return $response;
    }
    
    
    public function serviciosRequisitos(Request $request, Response $response, $args)
    {
        
        
        
        $servicios=Servicio::select("servicio.idServicio","servicio.nombre","pais.nombre as pais","pais.iso","pais.idPais")
        ->join('pais', 'servicio.idPais', '=', 'pais.idPais')
        ->orderBy('servicio.idServicio', 'DESC')
        ->get()
        ->toArray();
        $requisitos=Requisitos::all()->toArray();
        $paises=Pais::all()->toArray();
        
    
            
            $datos = ['title'=>"Lista de servicios", 'titulo' => "GESTIONAR SERVICIOS", "servicios"=>$servicios
            ];
            
            $this->view->render($response, 'intranet/servicios.twig', $datos);
            return $response;
    }

    public function formApplication(Request $request, Response $response, $args)
    {

        
        $correo = $this->session::get("correo");
        $idPersona = $this->session::get("idPersona");
        
        $parientes=Pariente::getParientes($idPersona);
        
        $estudios=Estudia::getEstudios($idPersona);
        
        
        $perfil= Usuario::query()
        ->join('persona', 'usuario.idPersona', '=', 'persona.idPersona')
        ->join('direccion', 'persona.idDireccion', '=', 'direccion.idDireccion')
        ->join('distrito', 'direccion.idDistrito', '=', 'distrito.idDistrito')
        ->join('provincia', 'distrito.idProvincia', '=', 'provincia.idProvincia')
        ->join('departamento', 'provincia.idDepartamento', '=', 'departamento.idDepartamento')
        ->join('pais', 'departamento.idPais', '=', 'pais.idPais')
        ->where( 'usuario.correo',$correo)
        ->select('usuario.*','persona.*','direccion.*','distrito.idDistrito',"distrito.nombre as distrito",'provincia.idProvincia','provincia.nombre as provincia','departamento.idDepartamento','departamento.nombre as departamento','pais.idPais','pais.nombre as pais')
        ->get()->toArray(); 
        
        $profesion= Usuario::query()
        ->join('persona', 'usuario.idPersona', '=', 'persona.idPersona')
        ->join('estudia', 'estudia.idPersona', '=', 'persona.idPersona')
        ->join('profesion', 'estudia.idProfesion', '=', 'profesion.idProfesion')
        ->join('institucion', 'profesion.idInstitucion', '=', 'institucion.idInstitucion')
        ->join('carrera', 'profesion.idCarrera', '=', 'carrera.idCarrera')
        ->join('nivel', 'profesion.idNivel', '=', 'nivel.idNivel')
        ->where( 'usuario.correo',$correo)
        ->select('usuario.*','persona.*','profesion.*','institucion.nombre as institucion','institucion.tipo','carrera.nombre as carrera','nivel.nombre as nivel')
        ->distinct()
        ->get()->toArray(); 
        
        
        $servicios=Servicio::select("servicio.idServicio","servicio.nombre","pais.nombre as pais")
        ->join('pais', 'servicio.idPais', '=', 'pais.idPais')
        ->orderBy('servicio.idServicio', 'DESC')
        ->get()
        ->toArray();
        $departamentos=Departamento::getDepartamentos(173);

        $departamentosUS=Departamento::select("departamento.idDepartamento", "departamento.nombre", "pais.iso")
        ->join("pais","departamento.idPais","=","pais.idPais")
        ->where(["departamento.idPais"=>75])
        ->get()
        ->toArray();
        
        $empleadores=Empleador::getEmpleadores();
        $niveles=Nivel::all()->toArray();
        $titulo="FORM APPLICATION ".(Date("Y"))." - ".(Date("Y")+1);
        $datos = ['title'=>"Form Application", 'titulo' => $titulo, "empleadores"=>$empleadores, "departamentos"=>$departamentos , "niveles"=>$niveles, "servicios"=>$servicios,
            "departamentosUS"=>$departamentosUS, "correo"=>$correo,"perfil"=>$perfil[0], "profesion"=>$profesion,"parientes"=>$parientes,"estudios"=>$estudios
        ];
        $this->view->render($response, 'intranet/formApplication.twig', $datos);
        return $response;
    }

    public function agendaCharlas(Request $request, Response $response, $args)
    {
        
        
        
        $datosCharlas=[
            "usuario.correo",
            "persona.nombres",
            "persona.apellidos",
            "persona.celular",
            "persona.dni",
            "asiste.fechaAsistencia",
            "asiste.estadoTramite",
            "asiste.estadoAsistencia",
            "asiste.estadoReunion",
            "asiste.fechaReprogramacion",
            "reunion.idReunion",
            "reunion.nombre",
            "reunion.fechaRegistro",
            "reunion.fechaEntrevista",
            "reunion.estado",
            "reunion.fechaProgramada",
            "reunion.horaProgramada",
            "reunion.horaEntrevista",
            "reunion.idCatalogoReunion",
            "reunion.idSolicita",
            "catalogoreunion.nombre as tipoReunion"];
        
        $programacionCharlas = Usuario::select($datosCharlas)
        ->join('persona', 'usuario.idPersona', '=', 'persona.idPersona')
        ->join('asiste', 'asiste.idPersona', '=', 'persona.idPersona')
        ->join('reunion', 'asiste.idReunion', '=', 'reunion.idReunion')
        ->join('catalogoreunion', 'reunion.idCatalogoReunion', '=', 'catalogoreunion.idCatalogoReunion')
        ->orderBy('reunion.fechaEntrevista', 'DESC')
        ->get()
        ->toArray();
        
        $datos = ['title'=>"Lista Programación de Charlas", 'titulo' => "LISTA DE PROGRAMACIÓN DE CHARLAS", "charlas"=>$programacionCharlas
        ];
        $this->view->render($response, 'intranet/agenda-charlas.twig', $datos);
        return $response;
        
    }
    public function correoSuscritos(Request $request, Response $response, $args)
    {


$suscripciones = Usuario::select("usuario.*","persona.*","rol.nombre as rol")
        ->leftJoin('persona', 'usuario.idPersona', '=', 'persona.idPersona')
        ->join('rol', 'usuario.idRol', '=', 'rol.idRol')
        ->orderBy('usuario.fechaCreacion', 'DESC')
        ->get()
        ->toArray();

            $datos = ['title'=>"Lista de correos suscritos", 'titulo' => "LISTA DE CORREOS SUSCRITOS", "suscripciones"=>$suscripciones
            ];
            $this->view->render($response, 'intranet/correo-suscritos.twig', $datos);
            return $response;

}


}