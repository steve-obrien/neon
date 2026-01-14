<?php

namespace neon\user\controllers;

use neon\core\web\View;
use neon\user\models\User;
use neon\user\forms\UserCreate;
use neon\user\forms\UserEdit;
use neon\user\forms\UserInvite;
use \neon\core\web\AdminController;
use yii\web\NotFoundHttpException;
use Yii;

/**
 * User self management
 */
class ProfileController extends AdminController
{
	/**
	 * A users profile page
	 * @return String
	 */
	public function actionIndex()
	{
		return $this->render('index');
	}
}