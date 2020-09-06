<?php
namespace App\Helper;

use \Psr\Http\Message\ResponseInterface;

/**
 * JsonRenderer
 *
 * Render JSON view into a PSR-7 Response object
 */
class JsonRenderer
{
  /**
   *
   * @param ResponseInterface $response
   * @param int $statusCode
   * @param array $data
   *
   * @return ResponseInterface
   *
   * @throws \InvalidArgumentException
   * @throws \RuntimeException
   */

  public static function  render(ResponseInterface $response, $statusCode = 200, array $data = [])
  {
    $newResponse = $response->withHeader('Content-Type', 'application/json');
    $newResponse = $newResponse->withStatus($statusCode);
    $newResponse->getBody()->write(json_encode($data));

    return $newResponse;
  }

  	// ENVIAR RESPUESTA DE UNA FUNCION EN FORMATO JSON
	public static function RespuestaJSON($response, $data) {
		$newResponse = $response->withHeader('Access-Control-Allow-Origin', '*');
		$newResponse = $response->withAddedHeader('Content-Type', 'application/json');
		$newResponse = $newResponse->withStatus(200);
		$newResponse->getBody()->write(json_encode($data));
		return $newResponse;
  }

  public static function RespuestaJSONEncoder($response, $data) {
		$newResponse = $response->withHeader('Access-Control-Allow-Origin', '*');
		$newResponse = $response->withAddedHeader('Content-Type', 'application/json');
		$newResponse = $newResponse->withStatus(200);
		$newResponse->getBody()->write($data);
		return $newResponse;
	}

  public static function StringJSONDecoder($string) {
    $json = json_decode($string, true);
    return $json;
  }

}
