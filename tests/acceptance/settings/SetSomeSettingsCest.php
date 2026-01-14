<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 29/11/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */
class SetSomeSettingsCest
{
	/**
	 * @var \neon\user\models\User
	 */
	public $user;

	public function _before(AcceptanceTester $I)
	{
		neon()->db->createCommand('TRUNCATE user_session');
		neon_test_server_restart();
		// delete all the users and create a fresh one.
		\neon\user\models\User::deleteAll();
		$user = new \neon\user\models\User();
		$user->username = 'test';
		$user->email = 'test@test.com';
		$user->password = 'testesttest';
		$user->save();
		$user->setRoles(['neon-administrator']);
		$this->user = $user;
		// The fromEmailAddress field of the admin app is required by the settings form
		// we fill this out in the test so ensure its blank to start
		set_setting('admin', 'fromEmailAddress', '');
		set_setting('core', 'site_name', '');
	}

	public function _after(AcceptanceTester $I)
	{
		$this->user->delete();
	}

	protected function _login($I)
	{
		$I->seeInCurrentUrl('/login');
		$I->fillField('#login-username', 'test@test.com');
		$I->fillField('#login-password', 'testesttest');
		$I->click('button[type="submit"]');
	}

	public function settingTest(AcceptanceTester $I)
	{
		$I->amOnPage('/settings/index/index');
		$this->_login($I);
		$I->waitForElement('#setting-core-site_name');
		$I->fillField('#setting-core-site_name', 'Test app');
		$I->click('[href="#admin-tab"]');
		$I->fillField('#setting-admin-fromEmailAddress', 'test@test.com');
		$I->wait(1);
		$I->click('#setting-submit-admin');
		$I->waitForText('Settings have been successfully saved', 30);
		$I->seeInField('#setting-core-site_name', 'Test app');
		$I->click('[href="#admin-tab"]');
		$I->seeInField('#setting-admin-fromEmailAddress', 'test@test.com');
	}

}





