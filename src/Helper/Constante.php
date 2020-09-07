<?php
namespace App\Helper;
use App\Helper\Url;

class Constante {
	CONST DOMAINSITE = "http://sentinel.trabajo.ed/";

	CONST TIEMPOEXPIRACION = 60;
	const DEFAULT_PARAMETROS = [
	    "DOMAINSITE" => Constante::DOMAINSITE,
	    "ACTIVO" => Constante::ESTADO_ACTIVO,
		"INACTIVO" => Constante::ESTADO_INACTIVO,
		"TIPO_PERSONA_NATURAL"=> Constante::TIPO_PERSONA_NATURAL,
		"TIPO_PERSONA_JURIDICA"=> Constante::TIPO_PERSONA_JURIDICA
	];
	CONST REPOSITORIO_FILE = "uploads/";
	CONST REPOSITORIO_FILE_NORMA = "/../public/uploads/normas/";
	CONST Item_Pag_5 = 5;
	CONST Item_Pag_10 = 10;
	CONST Item_Pag_20 = 20;
	CONST Item_Pag_50 = 50;
	CONST ID_PROVINCIA_LIMA = "150100";
	CONST CAT_PERIODICIDAD=4; //periodo semanale, anual, diario etc
	CONST TIPO_NORMA=5; //tipo de norma del catalogo parametros
	CONST AMBITO_APLICACION=6; //Ambito de aplicacón
	CONST REPRESENTACION_TERRITORIAL=7; //Representacion territorial
	CONST DESCRIPTORES_TEMATICOS=8; //Descriptores tematicos
	CONST NODO=9; //nodo
	CONST CATALOGO_DOCUMENTO=13; //Catalogo de documentos (publicaciones, normas y mapas temáticos)
	CONST TIPO_DOCUMENTO=10; //Tipo de documentos
	CONST FUENTE_INFORMACION=11; // Fuentes de información
	CONST ID_PLANTILLA=1; // Tipo de plantilla Home
	CONST ID_PLANTILLA_INTERNA_1=2; // Tipo de plantilla Interna con Contenido - Menu
	CONST ID_PLANTILLA_INTERNA_2=3; // Tipo de plantilla Interna Solo contenido
	CONST ID_PLANTILLA_INTERNA_3=4; // Tipo de plantilla que tiene personalizado la cabecera
	CONST ID_ITEM_MODULO_CONTENIDO = 3;

/* contenidos personalizados */
	CONST RUTA_NOTICIAS = "noticias";
	CONST IDPADRE_NOTICIAS = 14;
	CONST IDPADRE_BANNER = 37;
	CONST ESTADO_BANNER_PUBLICADO = 6;
	CONST ESTADO_PUBLICADO = 6;


	CONST ID_TIPO_DOCUMENTO_PUBLICACIONES= 10;
	CONST ID_TIPO_DOCUMENTO_NORMAS= 5;
	CONST ID_TIPO_DOCUMENTO_MAPA= 14;

	//constante secciones home
	CONST ID_SECCION_HOME_RECOMENDACIONES= 616;
	CONST ID_SECCION_HOME_PRODUCTOS_TEMPORADA= 617;
	CONST ID_SECCION_HOME_SEMANA_AHORRO= 618;
	CONST ID_SECCION_HOME_SUMINISTRO= 619;

	CONST ID_SECCION_HOME_POPUP= 624;

	//tablas necesarias para los formularios
	CONST ID_ESTADOGENERAL = 5;
	CONST ID_TIPODOCUMENTOS = 1;


	CONST ESTADO_ACTIVO = 19;
	CONST ESTADO_INACTIVO = 20;
	CONST TIPO_PERSONA_NATURAL = 24;
	CONST TIPO_PERSONA_JURIDICA = 25;

	//constante tabla de tabtablas
	CONST TIPO_VINCULO_EMPRESA_PROVEEDOR=219;
	CONST TIPO_VINCULO_EMPRESA_TRANSPORTE=220;


}
