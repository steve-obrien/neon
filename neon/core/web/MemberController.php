<?php

namespace neon\core\web;

use yii\filters\AccessControl;
use neon\core\web\Controller;
use \neon\user\services\apiTokenManager\JwtTokenAuth;

/**
 * Simple member controller base class.
 *
 * This checks to make sure a member is logged in and if required has the
 * pre-requisite role for the actions required.
 *
 * For non-logged in actions @see Controller
 *
 * For neon administration tasks @see AdminController
 */
class MemberController extends Controller
{
	private $_roles = ['@'];

	public function __construct($id, $module, $config = [])
	{
		parent::__construct($id, $module, $config);
		// determine whether or not the page request needs to be secure
		if (neon('user')->routeHasRoles('/'.neon()->getRequest()->getPathInfo(), $roles)) {
			$this->_roles = $roles;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		$behaviours = array_merge(parent::behaviors(), [
			'access' => [
				'class' => AccessControl::className(),
				'rules' => [
					// allow authenticated users with set roles
					[
						'allow' => true,
						'roles' => $this->_roles
					],
					// everything else is denied
				],
			],
		]);
		return $behaviours;
	}
}

