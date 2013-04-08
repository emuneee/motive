<?php

use Everyman\Neo4j\Client,
	Everyman\Neo4j\Cypher\Query,
	Slim\Slim;

require 'vendor/autoload.php';

/**
 * Base model class, contains common functionality
 */
class Model 
{

	protected $db_client;
	protected $log;
	protected $app;

	/**
	 * Constructor
	 */
	function __construct() {
		$this->app = Slim::getInstance();
		$this->db_client = new Client($this->app->config("database.hostname", 
			$this->app->config("database.port")));
		$this->log = $this->app->getLog();
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