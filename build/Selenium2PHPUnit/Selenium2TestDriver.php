<?php
class Selenium2TestDriver extends WebDriver {
	protected $testId;
	protected $collectCodeCoverageInformation;
	protected $verificationErrors = array();
	protected $browser;
	protected $setupCoverage;
	
	public function setTestId($testId) {
		$this->testId = $testId;
	}
	
	/**
	 * @param  boolean $flag
	 * @throws InvalidArgumentException
	 */
	public function setCollectCodeCoverageInformation($flag)
	{
		if (!is_bool($flag)) {
			throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
		}
		$this->collectCodeCoverageInformation = $flag;
	}
	
	public function setupCodeCoverage($url) {
		if ($this->collectCodeCoverageInformation && $url != false) {
			parent::get($url);
			$this->deleteCookie('PHPUNIT_SELENIUM_TEST_ID');
			$u = parse_url($url);
			$this->setCookie('PHPUNIT_SELENIUM_TEST_ID', $this->testId);
		}
	}
	
	public function setBrowser($browser) {
		$this->browser = $browser;
	}
	
	public function getBrowser() {
		return $this->browser;
	}
	
	public function start($url=false) {
		$this->connect($this->browser);
		$this->setupCodeCoverage($url);
	}
	
	public function stop() {
		$this->close();
	}
	
	protected function verifyCommand($command, $arguments, $info)
	{
		try {
			$this->assertCommand($command, $arguments, $info);
		}
	
		catch (PHPUnit_Framework_AssertionFailedError $e) {
			array_push($this->verificationErrors, $e->toString());
		}
	}
	
	public function getVerificationErrors()
	{
		return $this->verificationErrors;
	}
	
	public function clearVerificationErrors()
	{
		$this->verificationErrors = array();
	}
}