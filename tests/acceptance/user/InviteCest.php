<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 01/02/2019 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */
class InviteCest
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
		\neon\user\models\UserInvite::deleteAll();
		neon_test_server_restart();

		$user = new \neon\user\models\User();
		$user->username = 'test';
		$user->email = 'test@test.com';
		$user->password = 'testesttest';
		$user->save();
		$user->setRoles(['neon-administrator']);

		// The fromEmailAddress field of the admin app is required by the settings form
		// we fill this out in the test so ensure its blank to start
		set_setting('admin', 'fromEmailAddress', '');
		// Make sure all tests start with the user invites set to off
		set_setting('user', 'allowUserInvitations', false);
		$this->user = $user;
	}

	public function _after(AcceptanceTester $I)
	{
		$this->user->delete();
		\neon\user\models\UserInvite::deleteAll();
	}

	protected function _login($I)
	{
		$I->seeInCurrentUrl('/login');
		$I->fillField('#login-username', 'test@test.com');
		$I->fillField('#login-password', 'testesttest');
		$I->click('button[type="submit"]');
	}

	protected function _logout($I)
	{
		$I->amOnPage('/user/index/index');
		$I->seeElement('[href="/user/account/index"]');
		$I->click('[href="/user/account/index"]');
		$I->click('[href="/logout"]');
	}

	/**
	 * @skip
	 */
	public function inviteTest(AcceptanceTester $I)
	{
		$I->amOnPage('user/index/index');
		$this->_login($I);
		$I->waitForElement('#UserGrid');
		$I->dontSeeElement('[href="/user/invite/index"]');
		// Prepare settings
		$I->amOnPage('/settings/index/index');

		$I->waitForElement('[href="#admin-tab"]');
		// Have to fill in admin email field due to form rules... is this expected?
		$I->click('[href="#admin-tab"]');
		$I->fillField('#setting-admin-fromEmailAddress', 'test@test.com');
		$I->click('[href="#user-tab"]');
		$I->see('No', '[name="allowUserInvitations"] .neonSwitch_slider');
		$I->click('[name="allowUserInvitations"] .neonSwitch_slider');
		$I->see('Yes', '[name="allowUserInvitations"] .neonSwitch_slider');

		$I->click('setting[submit-user]');
		$I->waitForText('SUCCESS! Settings have been successfully saved.');

		// Navigate to user invite page and invite new neon admin
		$I->amOnPage('/user/index/index');
		$I->seeElement('[href="/user/invite/index"]');
		$I->click('[href="/user/invite/index"]');
		$I->waitForElement('#UserInvite-email');
		$I->fillField('#UserInvite-email', 'test2@test.com');
		$I->click('[value="neon-administrator"]');
		$I->click('#UserInvite-Save');
		$I->wait(1);
		$I->waitForText('success');
		// Test that the invite is in the pending invites grid
		$I->amOnPage('/user/invite/pending');
		$I->wait(1);
		$I->waitForJs('return neon.form.forms.UserInviteGridFilter !== undefined', 30);
		$I->seeNumberOfElements('tbody tr',1);
		$I->see('test2@test.com', 'tbody');

		// Logout
		$this->_logout($I);
		// Test invitation
		$inviteToken = neon()->db->createCommand("SELECT token FROM user_invite")->queryAll();
		$inviteToken = $inviteToken[0]['token'];
		$I->amOnPage('/user/account/register?token='.$inviteToken);
		$I->seeElement('[name="register-button"]');
		$username = "test2";
		$password = "test2test2test2";
		// Disallow existing/duplicate username
		$I->fillField('#register-username', 'test');
		$I->fillField('#register-password', $password);
		$I->click('[name="register-button"]');
		$I->wait(1);
		$I->seeElement('.alert-danger');
		$I->see('This username has already been taken.');
		// Unique username
		$I->fillField('#register-username', $username);
		$I->fillField('#register-password', $password);
		$I->click('[name="register-button"]');
		$I->wait(1);
		$I->seeElement('.alert-success');
		$I->see(\neon\user\models\UserInvite::$successfulRegistrationMessage);
		$I->seeCurrentUrlEquals('/admin');

		// Test attempt to register while logged in redirects
		$I->amOnPage('/user/account/register?token='.$inviteToken);
		$I->seeCurrentUrlEquals('/admin');

		// Logout
		$this->_logout($I);

		// Test attempt to register as already-registered user
		$I->amOnPage('/user/account/register?token='.$inviteToken);
		$I->see(\neon\user\models\UserInvite::$invalidTokenMessage);

		// Test attempt to register with invalid token
		$I->amOnPage('/user/account/register?token=invalidtoken');
		$I->see(\neon\user\models\UserInvite::$invalidTokenMessage);
	}

}
