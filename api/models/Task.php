<?php

use Everyman\Neo4j\Client,
	Everyman\Neo4j\Node,
	Everyman\Neo4j\Index\NodeIndex;

require_once 'utils/APIUtils.php';
require 'vendor/autoload.php';
require 'Model.php';

/**
 * Handles CRUD operations for the task objects
 */
class Task extends Model {

	function __construct() {
		parent::__construct();
	}

	public function create_todo() {

	}
}