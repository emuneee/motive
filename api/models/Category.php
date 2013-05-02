<?php

use Everyman\Neo4j\Node,
	Everyman\Neo4j\Relationship,
	Everyman\Neo4j\Client;

/**
 * Provides functionality to categorize todos
 */
class Category extends Model {
	
	const CATEGORY_REL = "CATEGORY";

	function __construct() {
		parent::__construct();
	}

	public function getCategory($category_name) {
		$result = NULL;
	}

	public function createCategory($category_name) {
		$result = NULL;
	}

	public function categorizeTodo($category_name, $todo) {
		$result = FALSE:
		try {
			// get category from graph
			$category = $this->getCategory($category_name);
			// if category doesn't exist, create it
			if(!isset($category)) {
				$category = $this->createCategory($category_name);
			}
			// establish a relationship between the category and the todo
			$relationship = $this->db_client->makeRelationship();
			$relationship->setStartNode($category);
			$relationship->setEndNode($todo);
			$relationship->setType(self::CATEGORY_REL);
			$relationship->save();
			$result = TRUE;
		} catch (Exception $e) {
			$this->log->error("Error creating a category relationship");
			$this->log->error($e->getMessage());
		}
		return $result;
	}
}