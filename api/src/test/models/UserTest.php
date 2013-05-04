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
}