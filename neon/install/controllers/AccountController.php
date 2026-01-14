<?php

namespace neon\install\controllers;

use neon\install\helpers\InstallHelper;
use neon\install\forms\InstallUser;

/**
 * # Install Progress: 3 / 3
 * This controller represents the final step of the installation
 * It assumes we have a valid database connection
 * And we have the database tables installed for key modules
 * Therefore now we need to create the administative user account
 */
class AccountController extends InstallController
{
	public $layout = 'install';

	/**
	 * Create an administrative user account
	 * @return \yii\web\Response|string
	 * @throws \Exception if unable to create a user
	 */
	public function actionIndex()
	{
		neon()->view->params['step'] = 3;

		// TODO: add check we have a config file
		// TODO: add check we have a database!

		if (InstallHelper::hasAdminAccount()) {
			return $this->render('_exists');
		}
		$model = new InstallUser();
		if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
			$success = $model->createUser($user);
			if ($success === false) {
				throw new \Exception('Unable to create and save a valid user ' . print_r($user->getErrors(), true));
			} else {
				return $this->redirect(['/install/account/complete']);
			}
		}
		// use the config info and install the database
		return $this->render('index', ['model' => $model]);
	}

	/**
	 * When successfully created a new user go here
	 */
	public function actionComplete()
	{
		neon()->view->params['step'] = 4;
		return $this->render('complete');
	}
}