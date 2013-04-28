<?php

use Everyman\Neo4j\Client,
	Everyman\Neo4j\Node,
	Everyman\Neo4j\Index\NodeIndex;

require_once 'utils/APIUtils.php';
require 'vendor/autoload.php';
require 'Model.php';

/**
 * Handles CRUD operations for Motive users
 */
class User extends Model
{	

	function __construct() {
		parent::__construct();
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
		$result = NULL;
		$username_used = $this->isUsernameUsed($username);
		// check to see if username already used
		if(!$username_used) {
			$email_used = $this->isEmailAddressUsed($email_address);
			if(!$email_used) {
				$result = APIUtils::wrapResult();
			} else {
				$result = APIUtils::wrapResult("Email address is in use", FALSE);
			}
		} else {
			$result = APIUtils::wrapResult("Username is in use", FALSE);
		}
		return $result;
	}

	/**
	 * Creates a user with the attributes
	 * Returns a true result with the id of the newly create user if successful
	 */
	public function createUser($first_name, $last_name, $email_address, $username, 
		$credential) {

		// verify we have all the required attributes
		if(!isset($first_name) || !isset($last_name) ||
			!isset($email_address) || !isset($username)
			|| !isset($credential)) {
			return APIUtils::wrapResult("All required attributes aren't present", 
				FALSE);
		} else {
			// check to see if a user with the username or email address exists
			$result = $this->doesUserExist($username, $email_address);
			if($result['successful'] == FALSE) {
				return $result;
			}
			$user_index = new NodeIndex($this->db_client, 'users');
			$timestamp = time();

			$user = new Node($this->db_client);
			$user->setProperty("first_name", $first_name);
			$user->setProperty("last_name", $last_name);
			$user->setProperty("email_address", $email_address);
			$user->setProperty("username", $username);
			$user->setProperty("credential", $credential);
			$user->setProperty("create_datetime", $timestamp);
			$user->setProperty("update_datetime", $timestamp);

			try {
				$user = $user->save();
				// index the new user on attributes
				$user_index->add($user, "first_name", $user->getProperty("first_name"));
				$user_index->add($user, "last_name", $user->getProperty("last_name"));
				$user_index->add($user, "username", $user->getProperty("username"));
				$user_index->add($user, "email_address", $user->getProperty("email_address"));
				$user_index->add($user, "credential", $user->getProperty("credential"));
				$user_index->save();
				$created_user = array(
					"first_name" => $first_name,
					"last_name" => $last_name,
					"email_address" => $email_address,
					"username" => $username,
					"user_id" => $user->getId());
				return APIUtils::wrapResult($created_user);
			} catch (Exception $e) {
				$this->log->error("Error creating a new user");
				$this->log->error($e->getMessage());
				return APIUtils::wrapResult("Error creating a new user", FALSE);
			}
		}
	}

	public function updateUser($id, $first_name, $last_name, $email_address, $username, 
		$credential) {
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