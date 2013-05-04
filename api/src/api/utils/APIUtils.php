<?php

namespace api\utils;

use Slim\Slim;

require_once 'vendor/autoload.php';

/**
 * API Utils contains commonly used API functions
 */
class APIUtils
{

	function __autoload($class_name) {
    	require_once $class_name . '.php';
	}

	/**
	 * Configures the HTTP response before its sent back to the client
	 */ 
	public static function configureResponse($response, $result) {
		$response["Content-Type"] = "application/json";
	}
	
	/**
	 * Valiadtes if the proper authentication credentials are in the header
	 */
	function validateAuthGetRequest($request) {
		$result = APIUtils::wrapResult(
			'Username and/or session key not included with request', FALSE);
		$session_key = $request->headers('session_key');
		$username = $request->headers('username');
		if(isset($session_key) && isset($username)) {
			$session_properties = array(
				"username" => $username,
				"session_key" => $session_key);
			$result = APIUtils::wrapResult($session_properties);
		}
		return $result;
	}

	/**
	 * Validates whether or not we have a valid HTTP request
	 */
	public static function validatePostRequest($request) {
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
	public static function wrapResult($payload = '', $successful = TRUE) {
		$result_arr = array(
		 'successful' => $successful,
			'payload' => $payload);
		return $result_arr;
	}
}