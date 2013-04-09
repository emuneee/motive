<?php

use phpSec\Crypt\Hash,
	phpSec\Core,
	Everyman\Neo4j\Node,
	Everyman\Neo4j\Relationship,
	Everyman\Neo4j\Client;

require_once 'Model.php';
require_once 'User.php';

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
		$userId = $user->getUserWithCredential($username, $credential);
 
		if($userId["result"] != -1) {
			$this->log->info("Node with ID ".$userId['result']." found");
			//lets create a unique hash
			$duration = $this->app->config("session.duration");
			$timestamp = time();
			// hash
			$hash = new Hash(new Core());
			$session_key = $hash->create($timestamp.$username.$credential);
			// add the duration to the current time stamp
			$session_expire = strtotime("+".$duration." seconds", $timestamp);
			// store the session id/expire time in database
			$session = new Node($this->db_client);
			$session->setProperty("session_key", $session_key);
			$session->setProperty("session_expire", $session_expire);
			$session->setProperty("session_create_timestamp", $timestamp);
			// create a relationship between the user and the session
			$userNode = $this->db_client->getNode($userId['result']);
			// create the relationship
			$relationship = $this->db_client->makeRelationship();
			$relationship->setStartNode($userNode);
			$relationship->setEndNode($session);
			$relationship->setType("AUTHENTICATES");

			try {
				// save the session
				$session->save();
				// save the relationship...lol
				$relationship->save();
				$sessionDetails = array('session_key' => $session_key,
					'session_expire' => $session_expire);
				return APIUtils::wrapResult($sessionDetails);
			} catch (Exception $e) {
				$this->log->error("Error creating a new user session");
				$this->log->error($e->getMessage());
				return APIUtils::wrapResult("Error creating a new user session", FALSE);
			}
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