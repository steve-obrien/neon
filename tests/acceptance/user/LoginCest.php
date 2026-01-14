<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 29/11/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */
class LoginCest
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
		neon_test_server_restart();
		$user = new \neon\user\models\User();
		$user->username = 'testuser';
		$user->email = 'test@user.com';
		$user->password = 'testesttest';
		$user->save();
		$user->setRoles(['neon-administrator']);
		$this->user = $user;
	}

	public function _after(AcceptanceTester $I)
	{
		$this->user->delete();
	}

	public function login(AcceptanceTester $I)
	{
		$I->amOnPage('/user/account/login');
		$I->fillField('#login-username', 'HERE!!');
		$I->click('button[type="submit"]');
		$I->waitForText('Password cannot be blank');
		$I->dontSee('Username cannot be blank');
		// fill in the form
		$I->fillField('#login-username', 'wfwf');
		$I->fillField('#login-password', 'qwefwefwerf123we');
		$I->click('button[type="submit"]');
		$I->waitForText('Incorrect username or password', 30);

		// lets do 3 failed attempts
		neon()->settingsManager->set('user', 'failedLoginAttempts', 2);
		// Attempt 1
		$I->fillField('#login-username', 'test@user.com');
		$I->fillField('#login-password', 'qwefwefwerf123we');
		$I->click('Login');
		// wait for the request to be made from clicking the button above.
		// Otherwise the tests access the elements on the previous page once reloaded and this obviously fails,
		// generating a stale element error
		$I->wait(1);
		$I->waitForText('Incorrect username or password', 30);

		// Attempt 2
		$I->fillField('#login-username', 'test@user.com');
		$I->fillField('#login-password', 'w12341234');
		$I->click('Login');
		// again wait for the page to start the request
		$I->wait(1);
		$I->waitForText('Incorrect username or password', 30);
		// Attempt 3
		$I->fillField('#login-username', 'test@user.com');
		$I->fillField('#login-password', 'asdfasdf');
		$I->click('Login');
		// we should not need to wait here as suspended text will not exist on the page until
		$I->waitForText('suspended', 30);
		// check the user has been suspended
		$this->user->refresh();
		$I->assertTrue($this->user->isSuspended(), 'The user should be suspended');
		$I->assertTrue($this->user->hasTooManyFailedLoginAttempts(), 'The user should have too many failed login attempts');
	}

}





