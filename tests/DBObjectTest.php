<?php
namespace Model;

class DBObjectTest extends \PHPUnit_Framework_TestCase {
	protected $db;
	/**
	 * Do some jiggery to the config
	 * 
	 * @return null
	 */
	protected function setUp() {
		$settings=array();
		$settings['database'] = array();
		$settings['database']['user'] = 'jenkins';
		$settings['database']['passwd'] = 'jenkinspasswd123=';
		$settings['database']['server'] = 'localhost';
		$settings['database']['port'] = '3306';
		$settings['database']['db'] = 'jenkins';
		\Core\Router::$settings = $settings;
		

		$this->db = new \Library\Database\LinqDB(
			$settings['database']['server'], 
			$settings['database']['user'], 
			$settings['database']['passwd'], 
			$settings['database']['db'], 
			$settings['database']['port']
		);
		$query = <<<EOF
CREATE TABLE `user` (
	id int(11) NOT NULL auto_increment,
	name varchar(255) NOT NULL,
	date int(11) NOT NULL,
	PRIMARY KEY (`id`)
);
EOF;
		$this->db->query($query);
		$query = <<<EOF
CREATE TABLE `login` (
	id int(11) NOT NULL auto_increment,
	ip varchar(255) NOT NULL,
	date int(11) NOT NULL,
	user int(11) NOT NULL,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`user`) REFERENCES `user`(`id`)
);
EOF;
		$this->db->query($query);
	}
	
	/**
	 * Tests creation logic
	 * 
	 * @return null
	 */
	public function testTableCreate() {
 		$q = $this->db->query("SHOW COLUMNS IN `user`");
		$this->assertEquals(3, $q->num_rows);
	}
	
	/**
	 * Test Code Generation Logic
	 * 
	 * @depends testTableCreate
	 * 
	 * @return null
	 */
	public function testGeneration() {
		$this->testTableCreate();
		include __DIR__."/../build/generation/models.php";
		$this->assertTrue(class_exists("\Model\User"), "User model created");
		$this->assertTrue(class_exists("\Model\Login"), "Login model created");
	}
	
	/**
	 * Test Creation Logic
	 *
	 * @covers System\Model\DBObject::Create
	 * 
	 * @depends testGeneration
	 * 
	 * @return null
	 */
	public function testCreate() {
		$this->testGeneration();
		$this->user = \Model\User::Create(array("id"=>1, "name"=>"Joe Bloggs", "date"=>time()));
		$this->login = \Model\Login::Create(array("ip"=>"127.0.0.1", "date"=>time(), "user"=>1));
		$this->loginB = \Model\Login::Create(array("ip"=>"127.0.0.1", "date"=>time(), "user"=>1));
		$this->assertInstanceOf("\Model\User", $this->user, "User created");
		$this->assertInstanceOf("\Model\Login", $this->login, "Login Created");
		$this->assertInstanceOf("\Model\Login", $this->loginB, "Login Created");
	}
	
	/**
	 * Tests that foreign keys between tables work properly
	 * 
	 * @depends testCreate
	 * 
	 * @return null
	 */
	public function testForeignKeys() {
		$this->testCreate();
		$this->user = new \Model\User(1);
		$this->assertTrue(is_array($this->user->getLogins()), "Get logins");
		$this->assertEquals(2, count($this->logins = $this->user->getLogins()), "Count logins");
		$this->login = $this->logins[0];
		$this->assertInstanceOf("\Model\User", $this->login->getUser(), "User returned");
		
	}
	
	/**
	 * Tests that foreign keys between tables work properly
	 *
	 * @depends testCreate
	 *
	 * @return null
	 */
	public function testLinqFramework() {
		$this->testCreate();
		$userQuery = $this->db->Select("\Model\User");
		$loginQuery = $this->db->Select("\Model\Login");
		$loginQuery->addCount("num_logins");
		$userQuery->addField("id");
		$userQuery->addField("name", "person_name");
		$eq = $this->db->getAndFilter();
		$eq->eq("id", 1);
		$userQuery->setFilter($eq);
		$userQuery->setGroup("id");
		$userQuery->setLimit(0, 10);
		$loginQuery->joinLeft("user", $userQuery, "id");
		$this->assertEquals("SELECT COUNT(*) AS `num_logins`,  `user`.`id`, `user`.`name` AS `person_name` FROM `login` LEFT JOIN `user` ON `user`.`id`=`login`.`user` WHERE   (`user`.`id` = '1' ) ", $loginQuery->getSQL());
		$q = $loginQuery->Exec();
		$this->assertEquals(1, count($q));
		$this->assertEquals(array('num_logins'=>"2", 'id'=>"1", 'person_name'=>"Joe Bloggs"), $q[0]);
	}
	
	/**
	 * Tear down
	 *
	 * @return null
	 */
	protected function tearDown() {
		$query = <<<EOF
DROP TABLE `login`;
EOF;
		$this->db->query($query);
		$query = <<<EOF
DROP TABLE `user`;
EOF;
		$this->db->query($query);
		if (file_exists(__DIR__."/../framework/application/model/Login.php")) {
			unlink(__DIR__."/../framework/application/model/Login.php");
			unlink(__DIR__."/../framework/application/model/User.php");
			unlink(__DIR__."/../framework/system/model/Login.php");
			unlink(__DIR__."/../framework/system/model/User.php");
		}
	}
}