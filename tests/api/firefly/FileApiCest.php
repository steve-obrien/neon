<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 29/11/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

use \neon\user\services\apiTokenManager\models\UserApiToken;

class FileApiCest
{
	/**
	 * @var
	 */
	protected $token;

	/**
	 * @var \neon\user\models\User
	 */
	protected $user;

	public function _before()
	{
		// create a user and an API token to authenticate requests
		\neon\user\models\User::deleteAll();
		$user = new \neon\user\models\User([
			'email' => 'test@test.com',
			'username' => 'test',
			'password' => 'testesttest'
		]);
		if (!$user->save()) {
			dd($user->getErrors());
		}
		$user->setRoles(['neon-administrator']);
		neon()->user->autoRenewCookie = false;

		$token = UserApiToken::generateTokenFor($user->id);
		$this->user = $user;
		$this->token = $token->token;
	}

	public function _after()
	{
		$this->user->delete();
		UserApiToken::deleteAll(['token' => $this->token]);
	}

	public function testActionMeta($I)
	{
		// lets add a file to the filemanager
		$id = neon()->firefly->save('Hello im a new file', 'testfile.txt');
		// lets get meta about it.
		$meta = neon()->firefly->getMeta($id);
		// lets get meta from the API
		$I->sendGET("/firefly/api/file/meta?id=$id&api_token=".$this->token);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseContainsJson($meta);
	}
}





