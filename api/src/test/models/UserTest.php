<?php 

require '../../../vendor/autoload.php';

class UserTest extends PHPUnit_Framework_TestCase {
	
	protected $user;

	function setUp() {

		// load the motive.properties file
		$properties = parse_ini_file("../../../../config/motive.properties");

		$propertiesArr = array(
		    'database.hostname' => $properties['database.hostname'],
		    'database.port' => $properties['database.port']
		);

		$this->user = new \api\models\User($propertiesArr, new \api\utils\LogWriter());
	}

	function tearDown() {

	}

	function testDoesUserExist() {
		$result = $this->user->doesUserExist("fake_user","fake@email.com");
		$this->assertEquals(FALSE, $result);
	}

	/**
	 * 
	 */
	function testUserCreate() {
		$user_data = array(
			"first_name" => "Test",
			"last_name" => "Man",
			"email_address" => "test.man@auto.com",
			"username" => "testman01",
			"credential" => "password");
		$result = $this->user->createUser($user_data);
		$this->assertEquals(TRUE, $result["successful"]);
	}

	/**
	 * @depends testUserCreate
	 */
	function testDoesRealUserExist() {
		$result = $this->user->doesUserExist("testman01","test.man@auto.com");
		$this->assertEquals(TRUE, $result);
	}

	/**
	 * @depends testDoesRealUserExist
	 */
	function testGetUser() {
		$result = $this->user->getUser("testman01");
		$user = $result["payload"]["user"];
		$this->assertEquals("Test", $user["first_name"]);
		$this->assertEquals("Man", $user["last_name"]);
		$this->assertEquals("test.man@auto.com", $user["email_address"]);
		$this->assertEquals("testman01", $user["username"]);
	}

	/**
	 * @depends testGetUser
	 */
	function testUserDelete() {
		$result = $this->user->removeUser("testman01");
		$this->assertEquals(TRUE, $result["successful"]);
	}
}