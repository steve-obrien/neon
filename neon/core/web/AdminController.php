<?php

namespace neon\core\web;

use neon\user\services\apiTokenManager\JwtTokenAuthFilter;
use yii\filters\AccessControl;
use neon\core\web\Controller;
use \neon\user\services\apiTokenManager\JwtTokenAuth;

/**
 * Simple admin controller base class.
 * This user needs to logged in and have the role neon-administrator.
 */
class AdminController extends Controller
{
	public $layout = '/admin.php';

	/**
	 * @inheritdoc
	 * Default behavior denies everyone except users that are authenticated.
	 * Note this is authentication only not authorisation.  They only need a user account and to be logged in
	 */
	public function behaviors()
	{
		return array_merge(parent::behaviors(), [
			'jwt' => [
				'class' => JwtTokenAuthFilter::class
			],
			'access' => [
				'class' => AccessControl::class,
				'rules' => $this->rules()
			],
		]);
	}

	/**
	 * Overwrite this function in order to create specific rules in child controllers
	 * A definition for an array of AccessRule objects:
	 * @see \yii\filters\AccessRule
	 *
	 * For example:
	 *
	 * ```php
	 * [
	 *     [
	 *         'allow' => true,
	 *         'actions' => ['update'], // if not specified will apply to all actions in the controller
	 *         'roles' => ['updatePost'], // the roles a user must have
	 *     ],
	 * ],
	 * ```
	 */
	public function rules()
	{
		return [
			// allow authenticated users
			[
				'allow' => true,
				'roles' => [ 'neon-administrator' ]
			],
			[
				'allow' => true,
				'matchCallback' => function ($rule, $action) {
					return neon()->user->isSuper();
				}
			]
			// everything else is denied
		];
	}
}