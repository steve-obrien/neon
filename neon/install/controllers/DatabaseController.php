<?php

namespace neon\install\controllers;

use neon\core\helpers\Url;
use neon\install\helpers\InstallHelper;

/**
 * Install Progress: 2 / 3
 * This controller represents doing things with the database.
 * dit assumes we have a valid database connection
 * therefore the main role is installing database tables
 */
class DatabaseController extends InstallController
{
	public $layout = 'install';

	/**
	 * If the config file has been created previously (manually or otherwise)
	 * but a user account or user table is not installed then you will come here.
	 *
	 * @throws \Exception if migrations fail
	 * @return \yii\web\Response|string
	 */
	public function actionIndex()
	{
		neon()->view->params['step'] = 2;

		if ($this->hasLocalConfigError($errorResponse))
			return $errorResponse;
		if ($this->hasDatabaseError($errorResponse))
			return $errorResponse;

		// check if tables have already been installed:
		if (InstallHelper::coreInstalled()) {
			return $this->render('_installed', ['progress' => InstallHelper::getProgress()]);
		}
		// Do the install!
		return $this->render('index', ['migrations' => InstallHelper::getAllMigrations()]);
	}

	/**
	 * Run install migrations for the whole system
	 * 
	 * @return array
	 */
	public function actionMigrations()
	{
	    ob_start();
		$migrator = new \neon\core\db\Migrator();
		$migrator->runAllMigrations();
		$migrations = ob_get_clean();
		return [
			'success' => true,
		];
	}

	/**
	 * Attempt to create the database defined in the config
	 * @return \yii\web\Response
	 */
	public function actionCreateDatabase()
	{
		$connection = new \neon\core\db\Connection(InstallHelper::getDbConfig());
		$connection->createCommand('CREATE DATABASE '.env('DB_NAME'))->execute();
		return $this->redirect(['/install/database/index']);
	}

	/**
	 * Returns true if there is no local config file
	 * @param $errorResponse  A valid controller response to return if the error exists
	 * @return bool
	 */
	public function hasLocalConfigError(&$errorResponse)
	{
		if (!InstallHelper::hasLocalConfigFile()) {
			// show message saying no config file found
			// and provide a button to go back to the config step /install/config
			$errorResponse = $this->render('/install/_error.tpl', [
				'message' => 'This install step requires the config file to be set',
				'buttons' => [
					[
						'url' => Url::toRoute(['/install/config']),
						'label' => 'Back to Config Step',
						'iconClass' => 'fa fa-arrow-left',
					]
				]
			]);
			return true;
		}
		return false;
	}

	/**
	 * Returns true if there is a database connection error
	 * @param null $errorResponse  A valid controller response to return if the error exists
	 * @return bool
	 */
	public function hasDatabaseError(&$errorResponse = null)
	{
		$error = InstallHelper::checkDatabaseConnection(neon()->db);
		if ($error === 0) {
			return false;
		}
		if ($error == InstallHelper::ERROR_DB_UNKNOWN_DATABASE) {
			// show message saying no config file found
			// and provide a button to go back to the config step /install/config
			$errorResponse = $this->render('/install/_error.tpl', [
				'message' => 'Could not find the database <strong>' . env('DB_NAME') . '</strong>',
				'buttons' => [
					[
						'label' => 'Try to Create &amp; Install Database <strong>' . env('DB_NAME') . '</strong>',
						'url' => Url::toRoute(['/install/database/create-database']),
						'iconClass' => 'fa fa-plus',
						'type' => 'primary',
					],
					[
						'label' => 'Back to Config Step',
						'url' => Url::toRoute(['/install/config']),
						'type' => 'default',
						'iconClass' => 'fa fa-arrow-left',
					]
				]
			]);
			return true;
		}
		else if ($error !== 0) {
			// show message saying no config file found
			// and provide a button to go back to the config step /install/config
			$errorResponse = $this->render('/install/_error.tpl', [
				'message' => 'Could not establish a successful database connection with the current connection details. Check your configuration file',
				'buttons' => [
					[
						'label' => 'Back to Config Step',
						'url' => Url::toRoute(['/install/config']),
						'type' => 'default',
						'iconClass' => 'fa fa-arrow-left',
					]
				]
			]);
			return true;
		}
		return false;
	}
}