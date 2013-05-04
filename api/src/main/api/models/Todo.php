<?php

namespace api\models;

use Everyman\Neo4j\Client,
	Everyman\Neo4j\Node,
	Everyman\Neo4j\Relationship,
	Everyman\Neo4j\Index\NodeIndex;

/**
 * Handles CRUD operations for the Todo objects
 */
class Todo extends Model {

	const TAG_REL = "TAG";

	function __construct($db_properties, $log_writer) {
		parent::__construct($db_properties, $log_writer);
	}

	/**
	 * Inserts a new todo
	 */
	private function insertTodo($title, $description, $completion_date) {	
		$new_todo = NULL;
		$timestamp = time();
		$this->log->info("Inserting a new todo");
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
			if($result) {
				$new_todo = $todo;
			} 
		} catch (Exception $e) {
			$this->log->error("Error creating a new todo");
			$this->log->error($e->getMessage());
		}
		return $new_todo;
	}

	/**
	 * Indexes a todo
	 */
	private function indexTodo($todo) {
		$result = FALSE;
		$this->log->info("Indexing a todo");

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

	/**
	 * Creates a todo object from a node
	 */
	private function nodeToTodo($node) {
		$todo =  array("todo" => array(
			"title" => $node->getProperty("title"),
			"description" => $node->getProperty("description"),
			"completion_date" => $node->getProperty("completion_date"),
			"id" => $node->getId()
			));
		return $todo;
	}

	/**
	 * Creates a new todo
	 */
	public function createTodo($todo_data) {
		$result = NULL;
		$this->log->info("Creating a new todo");

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
			$todo = $this->insertTodo($todo_data["title"], $todo_data["description"],
				$todo_data["completion_date"]);
			// categorize the new todo
			$category = new Category();
			$category->categorizeTodo($todo, $todo_data["category"]);
			// TODO assign an author to the new todo

			// TODO tag the new todo
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