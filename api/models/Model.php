<?php

use Everyman\Neo4j\Client;

require 'vendor/autoload.php';

class Model 
{

	protected $db_client;

	function __construct() {
		$this->db_client = new Client('localhost', 7474);
	}
	
}