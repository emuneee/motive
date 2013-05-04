<?php

namespace api\models;

use Everyman\Neo4j\Client,
	Everyman\Neo4j\Node,
	Everyman\Neo4j\Index\NodeIndex;

/**
 * Handles CRUD operations for the task objects
 */
class Task extends Model {

	function __construct($db_properties, $log_writer) {
		parent::__construct($db_properties, $log_writer);
	}

	public function create_todo() {

	}
}