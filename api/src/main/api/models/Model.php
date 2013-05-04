<?php

namespace api\models;

use Everyman\Neo4j\Client,
	Everyman\Neo4j\Cypher\Query,
	Slim\Slim;
	
/**
 * Base model class, contains common functionality
 */
class Model 
{

	protected $db_client;
	protected $log;
	protected $config;

	/**
	 * Constructor
	 */
	function __construct($config, $log_writer) {
		$this->config = $config;
		$this->db_client = new Client(
			$this->config["database.hostname"], 
			$this->config["database.port"]);
		$this->log = $log_writer;
	}

	/**
	 * Wrap cypher query calls for logging purposes
	 */
	function executeQuery($queryString) {
		$this->log->debug("Executing query: ".$queryString);
		$query = new Query($this->db_client, $queryString);
		$resultSet = $query->getResultSet();
		return $resultSet;
	}
}