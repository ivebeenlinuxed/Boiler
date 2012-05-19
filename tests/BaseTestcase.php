<?php
abstract class BaseTestcase extends Selenium2TestCase
{
	public $coverageScriptUrl;
	public $coverageDomain;
	public static $browsers = array(
      array(
        'name'    => 'Firefox',
        'browser' => 'firefox',
        'host'    => 'server2.bcslichfield.com',
        'port'    => 4444,
        'timeout' => 30000,
      ),
      array(
        'name'    => 'Google Chrome',
        'browser' => 'chrome',
        'host'    => 'server2.bcslichfield.com',
        'port'    => 4444,
        'timeout' => 30000,
      ),
      array(
        'name'    => 'Internet Explorer',
        'browser' => 'internet explorer',
        'host'    => 'server2.bcslichfield.com',
        'port'    => 4444,
        'timeout' => 30000,
      ),
      array(
        'name'    => 'Opera',
        'browser' => 'opera',
        'host'    => 'server2.bcslichfield.com',
        'port'    => 4444,
        'timeout' => 30000,
      )
    );
	
	
	protected function setUp() {
		$this->coverageScriptUrl = __DIR__.'../build/Selenium2PHPUnit/phpunit_coverage.php';
		$this->coverageDomain = \Core\Router::$settings['site']['address'];
		parent::setUp();
	}
}
