<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 29/11/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */
class MediaCest
{
	/**
	 * @var \neon\user\models\User
	 */
	public $user;

	public function _before(AcceptanceTester $I)
	{
		// delete all the users and create a fresh one.
		neon()->db->createCommand('TRUNCATE user_session');
		\neon\user\models\User::deleteAll();
		$user = new \neon\user\models\User();
		$user->username = 'test';
		$user->email = 'test@test.com';
		$user->password = 'testesttest';
		$user->save();
		$user->setRoles(['neon-administrator']);
		$this->user = $user;
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

	public function mediaIndexPage(AcceptanceTester $I)
	{
		$I->amOnPage('/firefly/media/index');
		$this->_login($I);
		$I->waitForText('Media');
		//$I->amOnPage('/firefly/media/index');
		$I->waitForText('Drop files anywhere to upload');
		$I->click('Add Folder');
		$I->waitForJS("return $.active == 0;", 60);
		$I->waitForText('untitled folder');
	}

}





