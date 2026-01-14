<?php
/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 12/10/2017
 * Time: 14:28
 */

namespace neon\user\services\apiTokenManager;

use neon\user\models\User;
use yii\filters\auth\AuthMethod;

class NullAuth extends AuthMethod
{
	/**
	 * Find the user identity - indicates successful authentication
	 *
	 * @param \yii\web\User $user
	 * @param Request $request
	 * @param \yii\web\Response $response
	 * @return null | \yii\web\IdentityInterface
	 */
	public function authenticate($user, $request, $response)
	{
		return call_user_func(neon()->user->identityClass."::findOne",1);
	}
}