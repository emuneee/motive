<?php

namespace api\models;

use phpSec\Crypt\Hash,
	phpSec\Core,
	Everyman\Neo4j\Node,
	Everyman\Neo4j\Relationship,
	Everyman\Neo4j\Client;

/**
 * Provides mechanisms for authenticating with the API
 */
class Session extends Model
{
	const AUTH_REL = "AUTHENTICATES";

	function __construct($db_properties, $log_writer) {
		parent::__construct($db_properties, $log_writer);
	}

	/**
	 * Gets the session for the user specified by the session key
	 */
	private function getUserSession($username, $session_key) {
		$this->log->info("Retrieving a user session");
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
	 * Inserts a new session
	 */
	private function insertSession($username, $credential) {
		$this->log->info("Inserting a new session");
		$new_session = NULL;

		try {
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
			$new_session = $session->save();
			// TODO index our new session
		} catch (Exception $e) {
			$this->log->error("Error creating session");
			$this->log->error($e->getMessage());
		}

		return $new_session;
	}

	/**
	 * Establishes and authenticated relationship between the user and the session
	 */
	private function createAuthenticationRelationship($user, $session) {
		$this->log->info("Creating relationship between user and session");
		$result = FALSE;

		try {
			$relationship = $this->db_client->makeRelationship;
			$relationship->setStartNode($user);
			$relationship->setEndNode($session);
			$relationship->setType(self::AUTH_REL);
			$relationship->save();
			$result = TRUE;
		} catch (Exception $e) {
			$this->log->error("Error creating relationship to session");
			$this->log->error($e->getMessage());
		}

		return $result;
	}

	/**
	 * Creates and associates an authenticated session to the user
	 * Returns the hash to use for subsequent API calls
	 */
	public function createSession($session_data) {
		$result = NULL;

		// verify we have all the required attributes
		if(!array_key_exists("username", $session_data) || 
			!array_key_exists("credential", $session_data)) {
			$result = APIUtils::wrapResult("All required attributes aren't present", 
				FALSE);
		} else {
 			$user_model = new User($this->config, $this->log);
			$user = $user_model->getUserWithCredential($session_data["username"], 
				$session_data["credential"]);

			if(isset($user)) {
				$this->log->info("Node with ID $userId found");
				// create the session
				$session = $this->insertSession($username, $credential);
				// create a relationship between the user and the session
				$userNode = $this->db_client->getNode($userId);

				$relationship_created = createAuthenticationRelationship($user, $session);
				if($relationship_created) {
					$session_details = array(
						"session_key" => $session_key,
						"session_expire" => $session_expire);
					$result = APIUtils::wrapResult($session_details);
				}
			} else {
				$result = APIUtils::wrapResult("Incorrect username/password combination", FALSE);
			}
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