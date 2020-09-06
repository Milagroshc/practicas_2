<?php
namespace App\Helper;
use \App\Modelo\Usuario;
//use \App\Modelo\UserPermission;
//use \App\Modelo\Route;
use App\Helper\Url;
use Slim\Http\UploadedFile;

use DateTime;
use DateTimeZone;
use Braintree;


use App\Modelo\Login;
use App\Modelo\Docadjunto;

/**
*
*/
class Acl
{
	private $session;

	public function __construct()
	{
		$this->session = new \App\Helper\Session;
	}


	public static function limpiar_string($string) {
		$string = trim($string);

		$string = str_replace(
			array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
			array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
			$string
		);

		$string = str_replace(
			array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
			array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
			$string
		);

		$string = str_replace(
			array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
			array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
			$string
		);

		$string = str_replace(
			array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
			array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
			$string
		);

		$string = str_replace(
			array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
			array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
			$string
		);

		$string = str_replace(
			array('ñ', 'Ñ', 'ç', 'Ç'),
			array('n', 'N', 'c', 'C',),
			$string
		);

		//Esta parte se encarga de eliminar cualquier caracter extraño
		$string = str_replace(
			array("\\", "¨", "º", "-", "~",
				 "#", "@", "|", "!", "\"",
				 "·", "$", "%", "&", "/",
				 "(", ")", "?", "'", "¡",
				 "¿", "[", "^", "`", "]",
				 "+", "}", "{", "¨", "´",
				 ">", "< ", ";", ",", ":",
				 "."),
			'',
			$string
		);
		return $string;
	}

public static function crear_url($string){
	$string = Acl::limpiar_string($string);
		$slug = preg_replace('/[^A-Za-z0-9-]+/','-',$string);
		$slug = strtolower($slug);
		return $slug;
	}

	public static function GenerarURL($string){
		$string = Acl::limpiar_string($string);
		$slug = preg_replace('/[^A-Za-z0-9-]+/','-',$string);
		$slug = strtolower($slug);
		return $slug;
	}

	public static function creaCarpetaGuardaArchivo($uploadedFile, $directory){
		//$uploadedFiles = $request->getUploadedFiles();
							//crear por fecha para ordenar las cargas de las fotos
							$carpeta = Acl::nowInEastern();
							$root = $_SERVER["DOCUMENT_ROOT"];
							$dir = $root . '/public/uploads/'.$carpeta.'/';
							if( !file_exists($dir) ) {
									mkdir($dir, 0755, true);
							}
							/*recibo los parametros del login*/
							$directory=$directory.'/'.$carpeta;
							//var_dump($directory);

							if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
								$filename = Acl::moveUploadedFile($directory, $uploadedFile);
							}

							$docAdjunto=[
								"nombre"=>$uploadedFile->getClientFilename(),
								"urlAdjunto"=>Constante::DOMAINSITE.Constante::REPOSITORIO_FILE.$carpeta.'/'.$filename,
								"tamanio"=>$uploadedFile->getSize(),
								"formato"=>$uploadedFile->getClientMediaType(),
								"estado"=>1
							];
							$idDocAdjunto = Docadjunto::insertGetId($docAdjunto);
							return $idDocAdjunto;
	}


public static function curlGET($url,$dato){
    //$data = array('tipo' => 'tipo');
$query = http_build_query($dato);
$url=$url."?".$query;
$ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $response = curl_exec($ch);
        curl_close($ch);
        if(!$response)
        {
            return false;
        }
        else
        {
           return $response;
        }
}

public static function get_web_page( $url )
{


    $options = array(


		CURLOPT_RETURNTRANSFER => true, // return web page
CURLOPT_HEADER => false, // don’t return headers
CURLOPT_FOLLOWLOCATION => true, // follow redirects
CURLOPT_ENCODING => "", // handle all encodings
CURLOPT_USERAGENT => "spider", // who am i
CURLOPT_AUTOREFERER => true, // set referer on redirect
CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
CURLOPT_TIMEOUT => 120, // timeout on response
CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
CURLOPT_SSL_VERIFYPEER => false, // Disabled SSL Cert checks
CURLOPT_SSL_VERIFYHOST => 2 , // Disabled SSL Cert checks
CURLOPT_CAINFO => getcwd() . "\certificado\privatekey.pem", // Disabled SSL Cert checks
	);




    $ch      = curl_init( $url );
	curl_setopt_array( $ch, $options );


	$content = curl_exec( $ch );
	var_dump($content);
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header;
}

public static function validaSSL(){
	$url = 'https://aplicativos.munlima.gob.pe';

	//Initiate cURL.
	$ch = curl_init($url);

	//Disable CURLOPT_SSL_VERIFYHOST and CURLOPT_SSL_VERIFYPEER by
	//setting them to false.
	$certificate = getcwd() . "/certificado/privatekey.pem";
	echo $certificate;
	curl_setopt($ch, CURLOPT_CAINFO, $certificate);
	curl_setopt($ch, CURLOPT_CAPATH, $certificate);
	//Execute the request.


if(curl_exec($ch) === false){
    echo '<p>Curl error: ' . curl_error($ch) . "</p>";
}

    $info = curl_getinfo($ch);
    print_r($info);

$certInfo = curl_getinfo($ch, CURLINFO_CERTINFO);

echo "<p>Certificate info for $domain: ";
print_r($certInfo[0]);

echo "</p><p>Test is done</p>";
}

public static function curlPOST($url,$dato){
    //$data = array('tipo' => 'tipo');

    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_CAINFO, getcwd() . "/certificado/privatekey.pem");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $dato);
	curl_setopt($ch, CURLOPT_CERTINFO, 1);
	$response = curl_exec($ch);
	$info = curl_getinfo($ch);
  //var_dump($info);
	$certInfo = curl_getinfo($ch, CURLINFO_CERTINFO);
	//var_dump($response);
	//exit;
    curl_close($ch);


        if(!$response)
        {
            return false;
        }
        else
        {
           return $response;
        }
}

static function moveUploadedFile($directory, UploadedFile $uploadedFile)
{
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}

public static function isLogged()
{
	$token = Session::get('TOKEN');

	$valor=false;
		if(isset($token)){
				$valor = true;
				//echo "tiene sesion iniciada";
				//$valida = Login::validaToken($token);
				//var_dump($valida);
			}else{
		$valor=false;
		//echo "no tiene sesion iniciada";
		}
    return $valor;

}

/*

	public function profile()
	{
		$user = User::find($this->session->get('user_id'));
		return $user;
	}

	public function isAllow($page,$action)
	{
		$user_perm = UserPermission::where('page',$page)->where('action',$action)->where('group_id',$this->session->get('group_id'))->get();
		if(empty($user_perm->toArray())){
			$this->session->set('flash','You dont have permission ');
			return Url::redirect($location='dashboard');
		}

	}

	public function cekPermission($page,$action)
	{
		$user_perm = UserPermission::where('page',$page)->where('action',$action)->where('group_id',$this->session->get('group_id'))->get();
		if(empty($user_perm->toArray())){
			return false;
		}
		return true;

	}

	public function getRoute($routes)
	{
		$route = str_replace('/', '', $routes);
		return Route::where('route',$route)->first();
	}
	public function isLogged()
	{
		$session1 = $this->session->get('usuario-id');
		if(isset($session1)){
            return true;
        }
        return false;

	}

	public function getPermission($group_id)
	{
		$role = UserPermission::where('group_id',$group_id)->get();
		if($role->toArray())
		{

		}
	}

	public function getResource()
	{
		return $privateResources = array(
	        'user' => array(
	            'index',
	            'search',
	            'edit',
	            'create',
	            'delete',
	            'changePassword'
	        ),
	        'group' => array(
	            'index',
	            'search',
	            'edit',
	            'create',
	            'delete'
	        ),
	        'permission' => array(
	            'index',
	            'search',
	            'edit',
	            'create',
	            'delete'
	        )
	    );
	}

	public function getUser()
	{
		return User::all();
	}*/



	public static function filtraCodigoMalicioso($dato){
		$filtro1=0;
		$filtro2=0;
		$filtro3=0;
		$filtro1 = strpos($dato,"+");
		$filtro2 = strpos($dato,"'");
		$filtro3 = strpos($dato,"(");

		if(($filtro1+$filtro2+$filtro3)>0){
			echo "<br><br><span style='color:#1a5f95; font-size:13px; font-family:Arial, Helvetica, sans-serif'> Se ha detectado c&oacute;digo malicioso. ACCESO RESTRINGIDO...</span>";
			exit;
		}
	}

	public static function getKey($n){
		switch ($n) {
			case "10": $key = "D"; break;
			case "11": $key = "F"; break;
			case "12": $key = "G"; break;
			case "13": $key = "H"; break;
			case "14": $key = "J"; break;
			case "15": $key = "K"; break;
			case "16": $key = "L"; break;
			case "17": $key = "M"; break;
			case "18": $key = "N"; break;
			case "19": $key = "O"; break;
			case "20": $key = "Q"; break;
			case "21": $key = "T"; break;
			case "22": $key = "U"; break;
			case "23": $key = "V"; break;
			case "24": $key = "W"; break;
			case "25": $key = "X"; break;
			case "26": $key = "Y"; break;
			case "27": $key = "Z"; break;
			default: $key = $n; break;
		}

		return $key;
	}

	public function fechaLargo($fecha){
		$d = $this->fechaFormato($fecha);

		return "Miraflores, ".$this->nomDia($d['wday']+1)." ".$d['mday']." de ".$this->nomMes($d['mon'])." del ".$d['year'];
	}

	public function fechaCorta($fecha){
		$d = $this->fechaFormato($fecha);

		return $d['mday']."-".$d['mon']."-".$d['year'];
	}

	public static function fechaFormato($fecha){
		return getdate(strtotime($fecha));
	}

	public static function fechaFormatoString($originalDate){

		$newDate = date("Y-m-d", strtotime($originalDate));
		return $newDate;
	}


public static function nowInEastern()
{
	$eastern = new DateTimeZone('America/Lima');
	$now = new DateTime('now', $eastern);
	return $now->format('Y-m-d');
}


	public static function fechaStringArray($a){
		$eastern = new DateTimeZone('America/Lima');
		/* $now = new DateTime($a, $eastern);
		return $now->format('Y-m-d'); */
		$date = strtotime($a);
		/* $objFecha = new DateTime($a, new DateTimeZone('America/Lima'));
		$mes= $objFecha->format('m'); */
		$fecha=Array("anio"=> date('Y', $date),"mes"=> date('m', $date),"dia"=> date('d', $date), );
		return $fecha;
	}



	private function nomDia($dia){
		$nomDia="";

		switch ($dia) {
			case "1":  $nomDia = "domingo"; break;
			case "2":  $nomDia = "lunes"; break;
			case "3":  $nomDia = "martes"; break;
			case "4":  $nomDia = "mi&eacute;rcoles"; break;
			case "5":  $nomDia = "jueves"; break;
			case "6":  $nomDia = "viernes"; break;
			case "7":  $nomDia = "s&aacute;bado"; break;
		}

		return $nomDia;
	}

	public static function nomMes($mes){
		$nomMes = "";
		switch ($mes) {
			case "1":  $nomMes = "enero"; break;
			case "2":  $nomMes = "febrero"; break;
			case "3":  $nomMes = "marzo"; break;
			case "4":  $nomMes = "abril"; break;
			case "5":  $nomMes = "mayo"; break;
			case "6":  $nomMes = "junio"; break;
			case "7":  $nomMes = "julio"; break;
			case "8":  $nomMes = "agosto"; break;
			case "9":  $nomMes = "septiembre"; break;
			case "10":  $nomMes = "octubre"; break;
			case "11":  $nomMes = "noviembre"; break;
			case "12":  $nomMes = "diciembre"; break;
		}

		return $nomMes;
	}

	function showFiles($path){
	    $dir = opendir($path);
	    $files = array();
	    while ($current = readdir($dir)){
	        if( $current != "." && $current != "..") {
	            if(is_dir($path.$current)) {
	                showFiles($path.$current.'/');
	            }else {
	                $files[] = $current;
	            }
	        }
	    }
	    /*echo '<h2>'.$path.'</h2>';
	    echo '<ul>';
	    for($i=0; $i<count( $files ); $i++){
	        echo '<li>'.$files[$i]."</li>";
	    }*/
	    return $files;
	}

	public function subirFile($file){
		$resp = array();

		$ruta = Constante::PATHFORMMUJER;
		$mesf = $this->nomMes(date("n"));
		$rutaFile = $ruta.$mesf."/";
		$nombre="";

		if($file['pmFoto']['size']!=0){
			$nombreOrig = $file['pmFoto']['name'];
			$nombre = rand(10000, 99999)."-".$file['pmFoto']['name'];
		    $nombre_tmp = $file['pmFoto']['tmp_name'];
		    $tipo = $file['pmFoto']['type'];
		    $tamano = $file['pmFoto']['size'];

			$ext_permitidas = array('jpg', 'JPG', 'jpeg', 'JPEG', 'png');
			$partes_nombre = explode('.', $nombre);
			$extension = end( $partes_nombre );
			$ext_correcta = in_array($extension, $ext_permitidas);

		    $tipo_correcto = preg_match('/^image\/(pjpeg|jpeg|gif|png)$/', $tipo);

		    $limite = 50000 * 1024;

		    if( $ext_correcta && $tipo_correcto && $tamano <= $limite ){
				if( $file['pmFoto']['error'] > 0 ){
			    	$resp["success"] = FALSE;
			    	$resp["message"] = "Error de registro.";
			    }else{
			    	if (!file_exists($rutaFile)) {
					    mkdir($rutaFile, 0777, true);
					}
			    	if(file_exists( $rutaFile.$nombre) ){
			    		$resp["success"] = FALSE;
			    		$resp["message"] = "El archivo seleccionado ya existe: " . $nombre;
			    	}else{
			        move_uploaded_file($nombre_tmp, $rutaFile . $nombre);

			        $resp["success"] = TRUE;
			        $resp["desFile"] = $nombre;
			        $resp["desRutaFile"] = $rutaFile.$nombre;
			      	$resp["message"] = "El archivo se subio correctamente.";
			      }
			    }
			}else{
			  	$resp["success"] = FALSE;
				$resp["message"] = "El archivo ingresado no cumple los requisitos minimos:".$ext_correcta."-".$tipo_correcto."-".$tamano."-".$limite;
			}
		}else{
			$resp["success"] = FALSE;
			$resp["message"] = "El archivo no ha sido ingresado correctamente.";
		}

		return $resp;
	}

	public function subirFilePdf($file, $ruta){
		$resp = array();

		//$ruta = Constante::PATHFORMPROYECTOSDA;
		$mesf = $this->nomMes(date("n"));
		$rutaFile = $ruta."/";
		$nombre="";

		if($file['pmProyecto']['size']!=0){
			$nombreOrig = $file['pmProyecto']['name'];
			$nombre = rand(10000, 99999)."-".$file['pmProyecto']['name'];
		    $nombre_tmp = $file['pmProyecto']['tmp_name'];
		    $tipo = $file['pmProyecto']['type'];
		    $tamano = $file['pmProyecto']['size'];

			$ext_permitidas = array('pdf');
			$partes_nombre = explode('.', $nombre);
			$extension = end( $partes_nombre );
			$ext_correcta = in_array($extension, $ext_permitidas);

		    //$tipo_correcto = preg_match('/\.pdf$/i', $tipo);

		    $limite = 500000 * 1024;

		    if( $ext_correcta && $extension=="pdf" && $tamano <= $limite ){
				if( $file['pmProyecto']['error'] > 0 ){
			    	$resp["success"] = FALSE;
			    	$resp["message"] = "Error de registro.";
			    }else{
			    	if (!file_exists($rutaFile)) {
					    mkdir($rutaFile, 0777, true);
					}
			    	if(file_exists( $rutaFile.$nombre) ){
			    		$resp["success"] = FALSE;
			    		$resp["message"] = "El archivo seleccionado ya existe: " . $nombre;
			    	}else{
			        move_uploaded_file($nombre_tmp, $rutaFile . $nombre);

			        $resp["success"] = TRUE;
			        $resp["desFile"] = $nombre;
			        $resp["desRutaFile"] = $rutaFile.$nombre;
			      	$resp["message"] = "El archivo se subio correctamente.";
			      }
			    }
			}else{
			  	$resp["success"] = FALSE;
				$resp["message"] = "El archivo ingresado no cumple los requisitos minimos:".$ext_correcta."-".$extension."-".$tamano."-".$limite;
			}
		}else{
			$resp["success"] = FALSE;
			$resp["message"] = "El archivo no ha sido ingresado correctamente.";
		}

		return $resp;
	}



	public static function ObtenerPesoFile($valor_bytes){
		if ($valor_bytes >= 1024) {
			$peso = $valor_bytes/1024;
			$unidad = "KB";
			if ($peso >= 1024) {
				$peso = $peso/1024;
				$unidad = "MB";
				if ($peso >= 1024) {
					$peso = $peso/1024;
					$unidad = "GB";
				}
			}
		} else {
			$peso = $valor_bytes;
			$unidad = "B";
		}

		$data = [
			"peso" => $peso,
			"unidad" => $unidad
		];

		return $data;
	}





	public function enviaMail($cor, $sTexto, $file){
		$sAsunto = "Voz de Mujer Miraflorina | Reto";
		$sPara   = $cor;
		$mensaje = "Esta es una prueba mensaje";

		$bHayFicheros = 0;
		$sCabeceraTexto = "";
		$sAdjuntos = "";

		$sCabeceras = "From: Municipalidad de Miraflores<serviciosenlinea@miraflores.gob.pe>" . "\r\n";
		$sCabeceras .= "Reply-To: verushka.villavicencio@miraflores.gob.pe" . "\r\n";
		$sCabeceras .= "X-Mailer: PHP/" . phpversion();

		$sCabeceras .= "MIME-version: 1.0\n";

		if ($bHayFicheros == 0){
			$bHayFicheros = 1;
			$sCabeceras .= "Content-type: multipart/mixed;";
			$sCabeceras .= "boundary=\"--_Separador-de-mensajes_--\"\n";

			$sCabeceraTexto = "----_Separador-de-mensajes_--\n";
			$sCabeceraTexto .= "Content-type: text/html;charset=iso-8859-1\n";
			$sCabeceraTexto .= "Content-transfer-encoding: 7BIT\n";

			$sTexto = $sCabeceraTexto.$sTexto;
		}

		if ($_FILES['pmFoto']['size'] > 0){
			$sAdjuntos .= "\n\n----_Separador-de-mensajes_--\n";
			$sAdjuntos .= "Content-type: ".$_FILES['pmFoto']['type'].";name=\"".$file["nomfoto"]."\"\n";;
			$sAdjuntos .= "Content-Transfer-Encoding: BASE64\n";
			$sAdjuntos .= "Content-disposition: attachment;filename=\"".$file["nomfoto"]."\"\n\n";

			//$oFichero = fopen($_FILES['pmFoto']["tmp_name"], 'r');
			$oFichero = fopen(Constante::PATHSITE.Constante::PATHFORMMUJER.$this->nomMes(date("n"))."/".$file["nomfoto"], 'r');
			$sContenido = fread($oFichero, filesize(Constante::PATHSITE.Constante::PATHFORMMUJER.$this->nomMes(date("n"))."/".$file["nomfoto"]));
			$sAdjuntos .= chunk_split(base64_encode($sContenido));
			fclose($oFichero);
		}

		if ($bHayFicheros)
		$sTexto .= $sAdjuntos."\n\n----_Separador-de-mensajes_----\n";

		@mail($sPara, $sAsunto,$sTexto, $sCabeceras);

	}

	public function plantillaRegistranteMF($data, $tipo){
		$sTexto = "";

		if($tipo == 1){
			$sTexto = "
			<div style='background-color:#f2f2f2;'>
			<table width='600px' align='center'><tr><td>
			<div style='background-color:#0f76bb;padding:5px;font-family:Arial, Helvetica, sans-serif; color:#FFFFFF;'>
			<div style='font-size:20px;padding:10px 10px 0 10px;'><b>Programa Reto con voz de mujer</b></div>
			<div style='font-size:12px;padding:0 10px 10px 10px;color:#FFFFFF;'><b>Municipalidad de Miraflores</b></div>
			</div>
			<div style='width:600px; height:auto;padding:10px;background-color:#FFFFFF;'>
			   <div style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:16px;color:#0f76bb;'><strong>Gracias por participar. En breve le informaremos el estado de su publicación.</strong></div>
			   <div style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;'><strong>DATOS DEL CONTACTO</strong></div>
			   <div style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:11px;'><strong>Nombre:</strong> <span style='color:#333;'>".$data["nom"]."</span></div>
			   <div style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:11px;'><strong>Como cumpli mi reto:</strong><br /><span style='color:#333;'>".$data["reto"]."</span></div>
			   </div></td></tr>
			</table></div>";
		}else{
			$sTexto = "
			<div style='background:#f2f2f2;'>
			<table width='600px' align='center'><tr><td>
			   <div style='background-color:#0f76bb;padding:5px;font-family:Arial, Helvetica, sans-serif; color:#FFFFFF;'>
			      <div style='font-size:20px;padding:10px;'><b>Un nuevo usuario esta participando del reto.</b></div>
			   </div>
			   <div style='width:600px; height:auto;padding:10px;background-color:#FFFFFF;'>
			      <div style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:12px;'><strong>DATOS DEL CONTACTO</strong></div>
			      <div style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:11px;'><strong>Nombre:</strong> <span style='color:#333;'>".$data["nom"]."</span></div>
			      <div style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:11px;'><strong>Foto:</strong> <span style='color:#333;'>".$data["nomfoto"]."</span></div>
			      <div style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:11px;'><strong>Como cumpli mi reto:</strong><br /><span style='color:#333;'>".$data["reto"]."</span></div>
			   </div>
			</td></tr>
			<tr><td>
				<div style='padding:5px;font-family:Arial, Helvetica, sans-serif;font-size:11px; color:#4285f4;padding-top:15px;border-top:1px solid #EEE;'><br /><span style='color:#333;'>Por el momento la publicación de fotografias se encuentra desactivado.<br>
				Para realizar la publicación debe recopilar en aún archivo el nombre de la fotografía de su elección y enviarla al siguiente correo: <a>lizandro.alipazaga@miraflores.gob.pe</a></span></div>
			</td></tr>
			</table></div>";
		}

		return $sTexto;
	}



static function Subir_ftp($server,$user,$pass,$path_local,$path_remore)
{
	$data["success"]=false;
	$ftp_conn = ftp_connect($server);
	if (@ftp_login($ftp_conn, $user, $pass)) {
		if (ftp_put($ftp_conn, $path_remore, $path_local, FTP_BINARY)){
			$data["success"]=true;
		}else{
			$data["error"] = "Error al subir el achivo de la ruta $path_local a la ruta $path_remore.";
		}
	}else{
	  	$data["error"] =  "No se puede establecer coneccion con el servidor: $server.";
	}
	ftp_close($ftp_conn);
    return $data;
}

static function SubirArchivo($directory, UploadedFile $uploadedFile,$nuevoNombre)
{
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $Nombre =  $nuevoNombre.".".$extension;
    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $Nombre);

    return $Nombre;
}

public static function RespuestaJSON($response, $data) {
	$newResponse = $response->withHeader('Access-Control-Allow-Origin', '*');
	$newResponse = $response->withAddedHeader('Content-Type', 'application/json');
	$newResponse = $newResponse->withStatus(200);
	$newResponse->getBody()->write(json_encode($data));
	return $newResponse;
}



public static function verMesesxAnio($a){
$meses = Array();
   for($i = $a; 0  < $i; $i--){
	   // despliega los meses
	   $meses[]=Acl::nomMes($i);

   }
   return  $meses;
 }



public static function verMeses($a){

    $f1 = new \DateTime($a[0]);
    $f2 = new \DateTime($a[1]);

   // mostrara las fechas
   echo "valor f1 : " . $f1->format('d-m-Y') . "\n";
   echo "valor f2 : " . $f2->format('d-m-Y') . "\n";

   // obtener la diferencia de fechas
   $d = $f1->diff($f2);
   $difmes =  $d->format('%m');

   echo " Cantidad de meses " . $difmes . "\n";

   $impf = $f1;
   for($i = 1; $i <= $difmes; $i++){
       // despliega los meses
       $impf->add(new \DateInterval('P1M'));
	   echo  $impf->format('d-m-Y') . " - " .$i.  "\n";
	   echo  $impf->format('m') . " - " .$i.  "\n";
	   echo  Acl::nomMes($impf->format('m'));
   }
 }


   /** Actual month last day **/
   public static   function data_last_month() {
	$month = date('m');
	$year = date('Y');
	$day = date("d", mktime(0,0,0, $month+1, 0, $year));

	return date('Y-m-d', mktime(0,0,0, $month, $day, $year));
}

/** Actual month first day **/
public static function data_first_month() {
	$month = date('m');
	$year = date('Y');
	return date('Y-m-d', mktime(0,0,0, $month, 1, $year));
}


   /** Actual month last day **/
   function _data_last_month_day() {
	$month = date('m');
	$year = date('Y');
	$day = date("d", mktime(0,0,0, $month+1, 0, $year));

	return date('Y-m-d', mktime(0,0,0, $month, $day, $year));
}

/** Actual month first day **/
function _data_first_month_day() {
	$month = date('m');
	$year = date('Y');
	return date('Y-m-d', mktime(0,0,0, $month, 1, $year));
}

public static function validaUrl($url){
	$valor = $url;
	$estado=false;
		if(trim($valor) == ''){
			//echo 'No has introducido ningun valor<br>';
			$estado = false;
		}else{
			if (filter_var($valor, FILTER_VALIDATE_URL)) {
				//echo("$valor is a valid URL");
				$estado=true;
			} else {
				//echo("$valor is not a valid URL");
				$estado=false;
			}
		}
		return $estado;
}

public static function validaUrlExiste($url){
	$valor = $url;
	function filtroUrl($valor){
		if(trim($valor) == ''){
			echo 'No has introducido ningun valor<br>';
			return false;
		}else{
			if (!filter_var($valor, FILTER_VALIDATE_URL)) {
				echo 'La direccion introducida no es valida<br>';
				return false;
			}
			if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|](\.)[a-z]{2}/i",$valor)) {
				echo 'La direccion introducida no es valida<br>';
				return false;
			}else{
				echo 'Direccion valida<br>';
				return true;
			}
		}
	}
	if (!filtroUrl($valor)) {
		echo 'URL incorrecta';
	}else {
		echo 'La URL '.$valor.' es correcta';
	}
}



}
