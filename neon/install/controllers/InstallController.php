<?php
/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 24/10/2017
 * Time: 11:51
 */

namespace neon\install\controllers;

use \yii\web\Controller as ParentController;

/**
 * Class InstallController - parent class for all install controllers - contains no actions itself
 * @package neon\install\controllers
 */
class InstallController extends ParentController
{
	public $layout = 'install';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		// before doing anything we must turn off the session
		// this controller is for stateless non session APIs
		neon()->user->enableSession = false;
	}

	/**
	 * Install methods should be stateless in that each request must authenticate
	 * Furthermore session data should not be persisted between requests
	 * @param \yii\base\Action $action
	 * @return bool
	 */
	public function beforeAction($action)
	{
		// before doing anything we must turn off the session
		// this controller is for stateless non session APIs
		neon()->user->setIdentity(null);
		neon()->user->enableSession = false;
		neon()->user->loginUrl = null;
		// turn off database query schema caching!
		neon()->db->enableSchemaCache = false;
		// turn off any kind of catenation js/css asset jiggerypokery
		neon()->view->jsConcatenate = false;
		neon()->view->cssConcatenate = false;
		// call the auth methods
		$return = parent::beforeAction($action);
		return $return;
	}
}
