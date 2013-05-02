<?php

use phpSec\Crypt\Hash,
	phpSec\Core,
	Everyman\Neo4j\Node,
	Everyman\Neo4j\Relationship,
	Everyman\Neo4j\Client;

require_once 'Model.php';
require_once 'User.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/utils/APIUtils.php';

/**
 * Provides mechanisms for authenticating with the API
 */
class Session extends Model
{
	const AUTH_REL = "AUTHENTICATES";

	function __construct() {
		parent::__construct();
	}

	/**
	 * Gets the session for the user specified by the session key
	 */
	private function getUserSession($username, $session_key) {
		// for this user, does the session with session_key exist and is it valud
		$query_string = "START user=node:users('username:\"$username\"')
			MATCH user-[:AUTHENTICATES]->session
			WHERE session.session_key = \"$session_key\"
			RETURN session";
		$result_set = $this->executeQuery($query_string);
		$session = NULL;
		// check to see if we have a valid result
		if($result_set->count() == 1) {
			foreach ($result_set AS $row) {
				$session_key = $row['x']->getProperty('session_key');
				$session = $this->nodeToSession($row['x']);
			}
		}
		return $session;
	}

	/**
	 * Converts a Neo4j node to a session
	 */
	private function nodeToSession($node) {
		$session = array("session" => array(
			"session_key" => $node->getProperty("session_key"),
			"session_expire" => $node->getProperty("session_expire"),
			"session_create_timestamp" => $node->getProperty("session_create_timestamp")
			));
		return $session;
	}

	/**
	 * Creates and associates an authenticated session to the user
	 * Returns the hash to use for subsequent API calls
	 */
	public function createSession($username, $credential) {
		$result = NULL;
		$user = new User();
		$userId = $user->getUserIdWithCredential($username, $credential);
 
		if($userId != -1) {
			$this->log->info("Node with ID $userId found");
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
			$userNode = $this->db_client->getNode($userId);
			// create the relationship
			$relationship = $this->db_client->makeRelationship();
			$relationship->setStartNode($userNode);
			$relationship->setEndNode($session);
			$relationship->setType(self::AUTH_REL);

			try {
				// save the session
				$session->save();
				// save the relationship...lol
				$relationship->save();
				$session_details = array('session_key' => $session_key,
					'session_expire' => $session_expire);
				$result = APIUtils::wrapResult($session_details);
			} catch (Exception $e) {
				$this->log->error("Error creating a new user session");
				$this->log->error($e->getMessage());
				$result = APIUtils::wrapResult("Error creating a new user session", FALSE);
			}
		} else {
			$result = APIUtils::wrapResult("Incorrect username/password combination", FALSE);
		}
		return $result;
	}

	/**
	 * Validate session key is active and valid for the current user
	 */
	public function validateSessionKey($username, $session_key) {
		$result = NULL;
		$session = $this->getUserSession($username, $session_key);
		if(isset($session)) {
			// see if the session has expired
			$current_time = time();
			if($current_time > $session['session']['session_expire']) {
				$result = APIUtils::wrapResult("Session has expired", FALSE);
			} else {
				$result = APIUtils::wrapResult();
			}
		} else {
			$result = APIUtils::wrapResult("Session is not valid", FALSE);
		}
		return $result;
	}
}