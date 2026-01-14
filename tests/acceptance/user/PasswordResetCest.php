<?php

use \neon\core\helpers\File;

/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 29/11/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */
class PasswordResetCest
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
		\neon\user\models\User::deleteAll();
		$user = new \neon\user\models\User();
		$user->username = 'testuser';
		$user->email = 'test@user.com';
		$user->password = 'testesttest';
		$user->save();
		$user->setRoles(['neon-administrator']);
		$this->user = $user;

		neon()->mailer->useFileTransport = true;
		neon()->mailer->fileTransportPath = '@runtime/mail';

		neon()->urlManager->setScriptUrl(test_url());
	}

	public function _after(AcceptanceTester $I)
	{
		$this->user->delete();
	}

	public function reset(AcceptanceTester $I)
	{
		$I->amOnPage('/user/account/login');

//		$I->click('#password_reset_link');
		$I->amOnPage('/user/account/request-password-reset');
//		$I->waitForElement('#passwordresetrequest-email');
		$I->fillField('#passwordresetrequest-email', 'test@user.com');

		// clear out any email files
		$mailDir = neon()->getAlias('@runtime/mail');
		if (file_exists($mailDir))
			File::removeDirectory($mailDir);
		$I->click('Send');
		// the send should submit the form, and send an email which will generate an email file
		// (due to useFileTransport setting)
		// lets keep testing to see if the email file has been generated - times out after 30 seconds
		$files = retry(60, function() use ($mailDir) {
			return File::findFiles($mailDir);
		},500); // wait for 30 seconds

		$emailContent = file_get_contents($files[0]);
		$this->user->refresh();
		$positionOfResetToken = strpos(str_replace(["\n", "\r", '='], '', $emailContent), $this->user->password_reset_token);
		$I->assertTrue($positionOfResetToken > 0);
		// set new password
		$resetLink = '/user/account/reset-password?token='.$this->user->password_reset_token;
		$I->amOnPage($resetLink);
		$I->fillField('#passwordreset-password', 'newpassword');
		$I->click('Save');
		$I->waitForText(\neon\user\forms\PasswordReset::$successResetMessage);
		// wait for the page submission to finish
		// then lets login
		$I->fillField('#login-username', $this->user->email);
		$I->fillField('#login-password', 'newpassword');
		$I->click('Login');
		$I->waitForText('testuser');
	}

}





