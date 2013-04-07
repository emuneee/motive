<?php

use Everyman\Neo4j\Client,
	Everyman\Neo4j\Node;

require 'vendor/autoload.php';
require 'Model.php';

class User extends Model
{	

	function __construct() {
		parent::__construct();
	}

	function createUser($first_name, $last_name, $email_address, $username, 
		$credential) {
		$user = new Node($this->db_client);
		return "Not Implemented";
	}

	function updateUser($id, $first_name, $last_name, $email_address, $username, 
		$credential) {
		return "Not Implemented";
	}

	function getUser($id) {
		return "Not Implemented";
	}
}