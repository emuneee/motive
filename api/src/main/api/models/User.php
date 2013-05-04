<?php

namespace api\models;

use Everyman\Neo4j\Client,
	Everyman\Neo4j\Node,
	Everyman\Neo4j\Index\NodeIndex;


/**
 * Handles CRUD operations for Motive users
 */
class User extends Model
{	

	function __construct($db_properties, $log_writer) {
		parent::__construct($db_properties, $log_writer);
	}

	/**
	 * Returns true if a user already with the email address already exists
	 */
	private function isEmailAddressUsed($email_address) {
		$query_string = "START n=node:users(email_address = '$email_address')
			RETURN n";
		$result_set = $this->executeQuery($query_string);
		$result = $result_set->count() > 0;
		return $result;
	}

	/**
	 * Returns false result if a user already with the username already exists
	 */
	private function isUsernameUsed($username) {
		$query_string = "START n=node:users(username = '$username')
			RETURN n";
		$result_set = $this->executeQuery($query_string);
		$result = $result_set->count() > 0;
		return $result;
	}

	/**
	 * Converts a Neo4j node to a user
	 */
	private function nodeToUser($node) {
		$user = array("user" => array(
			"first_name" => $node->getProperty("first_name"),
			"last_name" => $node->getProperty("last_name"),
			"email_address" => $node->getProperty("email_address"),
			"username" => $node->getProperty("username")
			));
		return $user;
	}

	/**
	 * Returns the user specified by the username
	 */
	private function getUserWithUsername($username) {
		$user = NULL;
		$query_string = "START user=node:users(username = '$username')
			RETURN user";
		$result_set = $this->executeQuery($query_string);
		if($result_set->count() == 1) {
			foreach($result_set AS $row) {
				$user = $this->nodeToUser($row['x']);
			}
		}
		return $user;
	}

	/**
	 * Inserts a new user into the database
	 */
	private function insertUser($first_name, $last_name, $email_address, $username, 
		$credential) {
		$new_user = NULL;
		$timestamp = time();

		try {
			$user = new Node($this->db_client);
			$user->setProperty("first_name", $first_name);
			$user->setProperty("last_name", $last_name);
			$user->setProperty("email_address", $email_address);
			$user->setProperty("username", $username);
			$user->setProperty("credential", $credential);
			$user->setProperty("create_datetime", $timestamp);
			$user->setProperty("update_datetime", $timestamp);
			$user = $user->save();
			$result = $this->indexUser($user);
			if($result) {
				$new_user = $user;
			}
		} catch (Exception $e) {
			$this->log->error("Error creating a new user");
			$this->log->error($e->getMessage());
		}
		return $new_user;
	}

	/**
	 * Indexes a user
	 */
	private function indexUser($user) {
		$result = FALSE;
		try {
			$user_index = new NodeIndex($this->db_client, 'users');
			// index the new user on attributes
			$user_index->add($user, "first_name", $user->getProperty("first_name"));
			$user_index->add($user, "last_name", $user->getProperty("last_name"));
			$user_index->add($user, "username", $user->getProperty("username"));
			$user_index->add($user, "email_address", $user->getProperty("email_address"));
			$user_index->add($user, "credential", $user->getProperty("credential"));
			$user_index->save();
			$result = TRUE;
		} catch (Exception $e) {
			$this->log->error("Error indexing user");
			$this->log->error($e->getMessage());
		}
		return $result;
	}

	/**
	 * Returns the user node with the specified username and credential
	 * Returns -1 if it does not exist
	 */
	public function getUserIdWithCredential($username, $credential) {
		$query_string = "START n=node:users('username:\"$username\" 
			AND credential:\"$credential\"') RETURN n";
		$result_set = $this->executeQuery($query_string);
		$user_id = -1;
		if($result_set->count() == 1) {
			foreach ($result_set as $row) {
				$user_id = $row['x']->getId();
			}
		} 
		return $user_id;
	}

	/**
	 *	Returns false result if the user already exists
	 */
	public function doesUserExist($username, $email_address) {
		$does_user_exist = NULL;
		$is_username_used = $this->isUsernameUsed($username);
		// check to see if username already used
		if(!$is_username_used) {
			$is_email_used = $this->isEmailAddressUsed($email_address);
			if(!$is_email_used) {
				$does_user_exist = FALSE;
			} else {
				$does_user_exist = TRUE;
			}
		} else {
			$does_user_exist = TRUE;
		}
		return $does_user_exist;
	}

	/**
	 * Creates a user with the attributes
	 * Returns a true result with the id of the newly create user if successful
	 */
	public function createUser($user_data) {
		$result = NULL;
		// verify we have all the required attributes
		if(!array_key_exists("first_name", $user_data) || 
			!array_key_exists("last_name", $user_data) ||
			!array_key_exists("email_address", $user_data) || 
			!array_key_exists("username", $user_data) || 
			!array_key_exists("credential", $user_data)) {
			$result = APIUtils::wrapResult("All required attributes aren't present", 
				FALSE);
		} else {
			$does_user_exist = $this->doesUserExist($user_data["username"],
				$user_data["email_address"]);
			if($does_user_exist) {
				$result = APIUtils::wrapResult("User already exists", FALSE);
			} else {
				// create a new user
				$user = $this->insertUser($user_data["first_name"], 
					$user_data["last_name"], $user_data["email_address"], 
					$user_data["username"], $user_data["credential"]);
				if(isset($user)) {
					$new_user = $this->nodeToUser($user);
					$result = APIUtils::wrapResult($new_user);
				} else {
					$result = APIUtils::wrapResult("Error creating a new user", FALSE);
				}
			}
		}
		return $result;
	}

	public function updateUser($user_data) {
		return "Not Implemented";
	}

	/**
	 * Returns a user
	 */
	public function getUser($username) {
		$result = NULL;
		$user = $this->getUserWithUsername($username);
		if(isset($user)) {
			$result = APIUtils::wrapResult($user);
		} else {
			$result = APIUtils::wrapResult("User $username does not exist", FALSE);
		}
		return $result;
	}
}