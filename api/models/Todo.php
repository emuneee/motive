<?php

use Everyman\Neo4j\Client,
	Everyman\Neo4j\Node,
	Everyman\Neo4j\Relationship,
	Everyman\Neo4j\Index\NodeIndex;

require_once 'utils/APIUtils.php';
require_once 'Category.php';
require 'vendor/autoload.php';
require_once 'Model.php';

/**
 * Handles CRUD operations for the Todo objects
 */
class Todo extends Model {

	const TAG_REL = "TAG";

	function __construct() {
		parent::__construct();
	}

	private function insertTodo($title, $description, $completion_date) {	
		$new_todo = NULL;
		$timestamp = time();
		
		try {
			// TODO transaction@!
			// create the new todo
			$todo = new Node($this->db_client);
			$todo->setProperty("title", $title);
			$todo->setProperty("description", $description);
			$todo->setProperty("completion_date", $completion_date);
			$todo->setProperty("create_datetime", $timestamp);
			$todo->setProperty("updated_datetime", $timestamp);
			$todo = $todo->save();
			$result = $this->indexTodo($todo);
			if($result == TRUE) {
				$new_todo = $todo;
			} 
		} catch (Exception $e) {
			$this->log->error("Error creating a new todo");
			$this->log->error($e->getMessage());
		}
		return $new_todo;
	}

	private function indexTodo($todo) {
		$result = FALSE;
		try {
			// index the new todo on attributes
			$todo_index = new NodeIndex($this->db_client, 'todos');
			$todo_index->add($todo, "title", $todo->getProperty("title"));
			$todo_index->add($todo, "description", $todo->getProperty("description"));
			$todo_index->add($todo, "completion_date", $todo->getProperty("completion_date"));
			$todo_index->save();
			$result = TRUE;
		} catch (Exception $e) {
			$this->log->error("Error indexing a todo");
			$this->log->error($e->getMessage());
		}
		return $result;
	}

	private function tagTodo() {

	}

	private function nodeToTodo($node) {
		$todo =  array("todo" => array(
			"title" => $node->getProperty("title"),
			"description" => $node->getProperty("description"),
			"completion_date" => $node->getProperty("completion_date"),
			"id" => $node->getId()
			));
		return $todo;
	}

	public function createTodo($todo_data) {
		$result = NULL;
		// make sure we have all required attributes
		if(!array_key_exists("title", $todo_data) ||
			!array_key_exists("description", $todo_data) ||
			!array_key_exists("completion_date", $todo_data) ||
			!array_key_exists("author", $todo_data) ||
			!array_key_exists("category", $todo_data)) {
			$result = APIUtils::wrapResult("All required attributes aren't present", 
				FALSE);
		} else {
			// create the todo

			// index the new todo

			// categorize the new todo

			// assign an author to the new todo

			// tag the new todo
		}
		return $result;
	}

	public function competeTodo() {

	}

	public function dismissTodo() {

	}

	public function updateTodo() {

	}
}