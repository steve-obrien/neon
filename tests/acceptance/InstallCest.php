<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 29/11/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

use \neon\install\helpers\InstallHelper;

class InstallCest
{
	/**
	 * Get the full file path of the env.ini
	 * @return string
	 */
	private function getEnvFile()
	{
		return $this->getEnvDir() . '/env.ini';
	}

	public function __construct($settings = [])
	{
		// In order to successfully run the InstallCest
		// we need to remove some of the env variables so neon does not think its loaded
		// (you do not need to load in the env.ini file if basic env files exist (DB_NAME, DB_USER, DB_PASSWORD, NEON_ENV))
		// lets just empty the NEON_ENV
		\neon\core\Env::setEnvironmentVariable('NEON_ENV', '', true);
	}

	/**
	 * @return string
	 */
	private function getEnvDir()
	{
		return DIR_TEST.'/_root/config';
	}

	/**
	 * File path of the ignored env ini
	 * @return string
	 */
	private function getIgnoredEnvFile()
	{
		return dirname($this->getEnvFile()) . '/_ignored_env.ini';
	}

	/**
	 * Make sure neon can not find the env file (rename it) neon then assumes we need to install
	 */
	private function ignoreEnvFile()
	{
		rename($this->getEnvFile(), $this->getIgnoredEnvFile());
	}

	/**
	 * Restore the env file
	 */
	private function unIgnoreEnvFile()
	{
		// restore ini file if the install test failed
		rename($this->getIgnoredEnvFile(), $this->getEnvFile());
	}

	/**
	 * Store the current values of the env.ini file in the config dir
	 * after installation neon should have created a similar env file with these details
	 * @var array
	 */
	private $envData = [];

	public function _before()
	{
		neon_test_db_cmd('DROP DATABASE IF EXISTS neon_test');
		// load the settings from the existing ini file
		$this->envData = parse_ini_file($this->getEnvFile());
		// remove env.ini
		$this->ignoreEnvFile();
	}

	public function _after()
	{
		$this->unIgnoreEnvFile();
	}

	public function _failed(\AcceptanceTester $I)
	{
		$this->unIgnoreEnvFile();
		// We dropped the database hoping the install would reinstall it.
		// It failed and so to prevent failing acceptance tests failing we should reinstall it.
		neon_test_database_create();
	}

	public function install(AcceptanceTester $I)
	{
		$I->amGoingTo('Run the install');
		// Check the start screen message and hit the run the install button!
		$I->amOnPage('/install');
		$I->seeInSource('@test: installer welcome message');
		$I->seeInSource('@test: installer requirements success');
		$I->click('[data-cmd="start_installation"]');
		// Enter database details and submit
		$I->waitForElement('#installdatabase-name');
		$I->seeInSource('@test: enter database details');
		$I->fillField('#installdatabase-name', $this->envData['DB_NAME']);
		$I->fillField('#installdatabase-username', $this->envData['DB_USER']);
		$I->fillField('#installdatabase-password', $this->envData['DB_PASSWORD']);
		$I->fillField('#installdatabase-host', $this->envData['DB_HOST']);
		$I->click('[data-cmd="submit_database_install"]');
		$I->waitForElement('[data-cmd="submit_create_database_install"]');
		// Can't select the database but offer to submit and create the database
		$I->seeInSource('@test: cant select database');
		$I->seeInSource('@test: create database table');
		$I->click('[data-cmd="submit_create_database_install"]');
		$I->waitForElement('[data-cmd="run_install"]');
		// config created run the install!
		$I->seeInSource('@test: successfully created env config');
		$I->click('[data-cmd="run_install"]');
		// database installed success screen
		$I->waitForJS('return neonInstaller.coreInstalled;', 60);
		$I->click('[data-cmd="create_admin_account"]');
		$I->waitForText('Create an Admin Account', 10);
		//$I->see('Create an Admin Account');
		$I->fillField('#installuser-username', 'tester');
		$I->fillField('#installuser-email', 'test@test.com');
		$I->fillField('#installuser-password', 'mystrongpassword');
		$I->click('button[type="submit"]');
		$I->waitForElement('[data-cmd="login"]');
		// login:
		$I->click('Log In');
		$I->amGoingTo('Submit correct login form details');
		$I->amOnPage('/user/account/login');
		$I->fillField('#login-username', 'test@test.com');
		$I->fillField('#login-password', 'mystrongpassword');
		$I->click('button[type="submit"]');
		$I->waitForText('Select a management option from the menu bar above to continue.', 30);
		$I->see('tester');
		// Scrutinizer fails to parse file as itt does not exist??
		// $env = parse_ini_file($this->getEnvFile());
		// $I->assertTrue($env['DB_USER'] == $this->envData['DB_USER']);
		// $I->assertTrue($env['DB_NAME'] == $this->envData['DB_NAME']);
		// $I->assertTrue($env['DB_PASSWORD'] == $this->envData['DB_PASSWORD']);
	}
}





