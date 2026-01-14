<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 29/11/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

use neon\user\services\apiTokenManager\models\UserApiToken;

class MediaApiCest
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

	public function authenticationTest(ApiTester $I)
	{
		$I->sendGET('firefly/api/media/list', ['api_token' => 'wrong token']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);

		// correct token but a suspended user
		$this->user->status = \neon\user\models\User::STATUS_SUSPENDED;
		$this->user->save();
		$I->sendGET('firefly/api/media/list', ['api_token' => $this->token]);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
	}

	public function authenticationBearerAuthIncorrect(ApiTester $I)
	{
		$I->haveHttpHeader('Authorization', 'Bearer Wrong_token');
		$I->sendGET('firefly/api/media/list');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
	}

	public function authenticationBearerAuthCorrect(ApiTester $I)
	{
		$I->haveHttpHeader('Authorization', 'Bearer '. $this->token);
		$I->sendGET('firefly/api/media/list');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
	}

	/**
	 * Test list
	 * @param ApiTester $I
	 */
	public function actionList(ApiTester $I)
	{
		$I->sendGET('firefly/api/media/list', ['api_token' => 'wrong token']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
		$I->sendGET('firefly/api/media/list', ['api_token' => $this->token]);
		$response = json_decode($I->grabResponse(), true);
		$I->seeResponseContainsJson(array('path' => '', 'items' => []));
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->sendGET('firefly/api/media/list', ['path' => '/does/not/exit', 'api_token' =>  $this->token]);
		$I->seeResponseContainsJson(array('path' => '/does/not/exit', 'items' => []));
	}

	/**
	 * curl
	 * @param ApiTester $I
	 */
	public function actionCreateDirectory(ApiTester $I)
	{
		$this->_testAuthenticate($I, 'create-directory', 'POST');

		// Bad request no path specified
		// ------------------------
		$I->sendPOST('firefly/api/media/create-directory?api_token='.$this->token);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);

		// can't overwrite root! "/"
		// ------------------------
		$I->sendPOST('firefly/api/media/create-directory?api_token='.$this->token, ['path' => '/']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::CONFLICT);

		// You must specify a root path starting with "/"
		// ------------------------
		$I->sendPOST('firefly/api/media/create-directory?api_token='.$this->token, ['path' => 'my folder']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);

		// CREATE: /my folder
		// ------------------------
		$I->sendPOST('firefly/api/media/create-directory?api_token='.$this->token, ['path' => '/my folder']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

		$root = neon()->firefly->mediaManager->getRoot();
		$I->seeResponseContainsJson([[
			"parent_id" => $root['id'],
			"path" => "/my folder",
			"type" => "dir",
		]]);
		$response = json_decode($I->grabResponse(), true);
		$I->assertEquals(22, strlen($response[0]['id']), 'check uuid of 22 characters has been generated');

		// can create multiple nested folders
		// CREATE: /my folder/one/two
		// ------------------------
		$I->sendPOST('firefly/api/media/create-directory?api_token='.$this->token, ['path' => '/my folder/one/two']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

		$response2 = json_decode($I->grabResponse(), true);
		$I->seeResponseContainsJson([
			[
				"path" => "/my folder/one",
                "type" => "dir",
                "parent_id" => $response[0]['id'],
				"id" => $response2[0]['id']
			],
            [
	            "path" => "/my folder/one/two",
                "type" => "dir",
	            "parent_id" => $response2[0]['id'],
	            "id" => $response2[1]['id']
			]
		]);
	}

	public function _testAuthenticate(ApiTester $I, $action, $method, $params=[])
	{
		$send = 'send' . strtoupper($method);
		$I->$send('firefly/api/media/' . $action .'?api_token=wrong_token', $params);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
	}

}





