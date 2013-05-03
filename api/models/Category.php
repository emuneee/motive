<?php

use Everyman\Neo4j\Node,
	Everyman\Neo4j\Relationship,
	Everyman\Neo4j\Client,
	Everyman\Neo4j\Index\NodeIndex;

/**
 * Provides functionality to categorize todos
 */
class Category extends Model {
	
	const CATEGORY_REL = "CATEGORY";

	function __construct() {
		parent::__construct();
	}

	private function nodeToCategory($node) {

	}

	/**
	 * Gets the category specified by the category name
	 */
	private function getCategory($category_name) {
		$this->log->info("Retrieving a category $category_name");
		$category = NULL;
		$query_string = "START category=node:categories(category_name = '$category_name')
			RETURN category";
		$result_set = $this->executeQuery($query_string);
		if($result_set->count() == 1) {
			foreach($result_set AS $row) {
				$category = $row['x'];
			}
		}
		return $category;
	}

	/**
	 * Indexes a category
	 */
	private function indexCategory($category) {
		$result = FALSE;
		$this->log->info("Indexing a category");
		try {
			$category_index = new NodeIndex($this->db_client, "categories");
			$category_index->add($category, "category_name", 
				$user->getProperty("category_name"));
			$category_index->save();
			$result = TRUE;
		} catch (Exception $e) {
			$this->log->error("Error indexing category");
			$this->log->error($e->getMessage());
		}

		return $result;
	}

	/**
	 * Inserts a category specified by the category name into the db
	 */
	private function insertCategory($category_name) {
		$new_category = NULL;
		$timestamp = time();
		$this->log->info("Inserting a new category with name $category_name");
		try {
			$category = new Node($this->db_client);
			$category->setProperty("category_name", $category_name);
			$category->setProperty("create_datetime", $timestamp);
			$category->setProperty("update_datetime", $timestamp);
			$category = $category->save();
			$result = $this->indexCategory($category);
			if($result) {
				$new_category = $category;
			}
		} catch (Exception $e) {
			$this->log->error("Error creating a new category");
			$this->log->error($e->getMessage());
		}
		return $new_category;
	}

	/**
	 * Categorizes the todo with the category name
	 */
	public function categorizeTodo($todo, $category_name) {
		$result = FALSE:
		$this->log->info("Categorizing a todo with the category $category_name");
		try {
			// get category from graph
			$category = $this->getCategory($category_name);
			// if category doesn't exist, create it
			if(!isset($category)) {
				$category = $this->insertCategory($category_name);
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