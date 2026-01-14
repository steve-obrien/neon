<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 18/05/2017
 * @package neon
 */

namespace neon\user\services\apiTokenManager;

use yii\base\ActionFilter;

/**
 * Validates the user based on a generated jwt cookie - this is generated from a logged in user
 * It enables us to authenticate if we have logged in - but have not expressed an api token
 * - this is intended for use with rest style stateless API's so that logged in users can interact with the same
 * API as might be exposed / access via an api_token
 * Class TokenAuth
 * @package neon\user\auth
 */
class JwtTokenAuthFilter extends ActionFilter
{
	/**
	 * @inheritdoc
	 */
	public function beforeAction($action)
	{
		JwtTokenAuth::createCookie(neon()->request, neon()->response);
		return true;
	}
}