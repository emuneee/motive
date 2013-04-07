<?php

use Slim\Slim;

require 'vendor/autoload.php';

/**
 * API Utils contains commonly used API functions
 */
class APIUtils
{

	/**
	 * Configures the HTTP response before its sent back to the client
	 */ 
	public static function configureResponse($response, $result) {
		$response["Content-Type"] = "application/json";
	}
	
	/**
	 * Validates whether or not we have a valid HTTP request
	 */
	public static function validateRequest($request) {
		$content_type = $request->headers('Content-Type');
		// make sure the content-type is application/json
		if($content_type != 'application/json') {
			return self::wrapResult('Content-Type is not application/json', FALSE);
		}

		// parse the response body and create a json object
		$body = $request->getBody();
		return self::wrapResult(json_decode($body, TRUE));
	}

	/**
	 * wrap result in an envelope containing any status messages
	 */
	public static function wrapResult($result = '', $successful = TRUE) {
		$result_arr = array(
		 'successful' => $successful,
			'result' => $result);
		return $result_arr;
	}
}