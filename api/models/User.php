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
	 * Returns false result if a user already with the email address already exists
	 */
	function doesUserWithEmailAddressExist($email_address) {
		$queryString = "START n=node:users(email_address = '$email_address')
			RETURN n";
		$resultSet = $this->executeQuery($queryString);
		if($resultSet->count() > 0) {
			return APIUtils::wrapResult("$email_address is already taken", FALSE);
		}
		else return APIUtils::wrapResult();
	}

	/**
	 * Returns false result if a user already with the username already exists
	 */
	function doesUserWithUsernameExist($username) {
		$queryString = "START n=node:users(username = '$username')
			RETURN n";
		$resultSet = $this->executeQuery($queryString);
		if($resultSet->count() > 0) {
			return APIUtils::wrapResult("$username is already taken", FALSE);
		}
		else return APIUtils::wrapResult();
	}

	/**
	 *	Returns false result if the user already exists
	 */
	function doesUserExist($username, $email_address) {
		$result = $this->doesUserWithUsernameExist($username);
		if($result['successful'] == TRUE) {
			return $this->doesUserWithEmailAddressExist($email_address);
		}
		return $result;
	}

	/**
	 * Converts a Neo4j node to a user
	 */
	function nodeToUser($node) {
		return array("user" => array(
			"first_name" => $node->getProperty("first_name"),
			"last_name" => $node->getProperty("last_name"),
			"email_address" => $node->getProperty("email_address"),
			"username" => $node->getProperty("username")
			));
	}

	/**
	 * Creates a user with the attributes
	 * Returns a true result with the id of the newly create user if successful
	 */
	function createUser($first_name, $last_name, $email_address, $username, 
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
			$userIndex = new NodeIndex($this->db_client, 'users');

			$user = new Node($this->db_client);
			$user->setProperty("first_name", $first_name);
			$user->setProperty("last_name", $last_name);
			$user->setProperty("email_address", $email_address);
			$user->setProperty("username", $username);
			$user->setProperty("credential", $credential);

			try {
				$user = $user->save();
				// index the new user on attributes
				$userIndex->add($user, "first_name", $user->getProperty("first_name"));
				$userIndex->add($user, "last_name", $user->getProperty("last_name"));
				$userIndex->add($user, "username", $user->getProperty("username"));
				$userIndex->add($user, "email_address", $user->getProperty("email_address"));
				$userIndex->save();
				return APIUtils::wrapResult($user->getId());
			} catch (Exception $e) {
				$this->log->error("Error creating a new user");
				$this->log->error($e->getMessage());
				return APIUtils::wrapResult("Error creating a new user", FALSE);
			}
		}
	}

	function updateUser($id, $first_name, $last_name, $email_address, $username, 
		$credential) {
		return "Not Implemented";
	}

	function getUser($id) {
		$userNode = $this->db_client->getNode($id);
		if(isset($userNode)) {
			return APIUtils::wrapResult($this->nodeToUser($userNode));
		} else {
			return APIUtils::wrapResult("User with $id does not exist", FALSE);
		}
	}
}