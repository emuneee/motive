<?php

require_once 'Model.php';
require_once 'User.php';
require_once '../libs/phpseclib/Crypt/Hash.php'

/**
 * Provides mechanisms for authenticating with the API
 */
class Session extends Model
{
	function __construct() {
		parent::__construct();
	}

	/**
	 * Creates and associates an authenticated session to the user
	 * Returns the hash to use for subsequent API calls
	 */
	function createSession($username, $credential) {
		$user = new User();
		$result = $user->doesUserWithCredentialExist($username, $credential);

		if($result["successful"] == TRUE) {
			// correct user credentials were presented, we should create a session

			//lets create a unique hash
			$duration = $this->app->config("session.duration")));
			$timestamp = time();
			// hash
			$hash = new $Hash();
			$hash->setHash("md5");
			$session_key = $hash->hash($timestamp.$username.$credential);
			// add the duration to the current time stamp
			$session_expire = strtotime("+".$duration." seconds", $timestamp);
			// store the session id/expire time in database
			$session = new Node($this->db_client);
			$session->setProperty("session_key", $session_key);
			$session->setProperty("session_expire", $session_expire);
			$session->setProperty("session_create_timestamp", $timestamp);
			// TODO create a relationship between the user and the session

			// TODO return the hash for the client to use in subsquent sessions
		} else {
			return APIUtils::wrapResult("Incorrect username/password combination", FALSE);
		}
	}

	/**
	 * Validate session key
	 */
	function validateSessionKey() {

	}
}