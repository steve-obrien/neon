<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 18/05/2017
 * @package neon
 */

namespace neon\user\services\apiTokenManager;

use neon\user\services\apiTokenManager\models\UserApiToken;

/**
 * Manages user API tokens
 * Class TokenManager
 */
class Manager implements IManager
{

	/**
	 * @inheritDoc
	 */
	public function all($userId)
	{
		// TODO: Implement all() method.
	}

	/**
	 * @inheritDoc
	 */
	public function validToken($token)
	{
		// TODO: Implement validToken() method.
	}

	/**
	 * @inheritDoc
	 */
	public function createToken($userId, $name)
	{
		UserApiToken::generateTokenFor($userId, $name);
	}

	/**
	 * @inheritDoc
	 */
	public function createTokenCookie($user)
	{
		// TODO: Implement createTokenCookie() method.
	}

	/**
	 * @inheritDoc
	 */
	public function updateToken($token, $name, array $abilities = [])
	{
		// TODO: Implement updateToken() method.
	}

	/**
	 * @inheritDoc
	 */
	public function deleteExpiredTokens($user)
	{
		// TODO: Implement deleteExpiredTokens() method.
	}
}
