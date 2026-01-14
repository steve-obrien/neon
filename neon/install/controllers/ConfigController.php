<?php

namespace neon\install\controllers;

use neon\core\Env;
use neon\core\web\Response;
use neon\install\helpers\InstallHelper;
use neon\install\forms\InstallDatabase;
use yii\widgets\ActiveForm;

/**
 * Install Progress: 1 / 3
 * Install the configuration file
 * This is concerned with creating the configuration file
 * which is mainly about connecting to a valid database
 */
class ConfigController extends InstallController
{
	public $layout = 'install';

	/**
	 * @inheritdoc
	 */
	public $enableCsrfValidation = false;

	/**
	 * @inheritdoc
	 */
	public function beforeAction($action)
	{
		neon()->request->enableCookieValidation = false;
		return parent::beforeAction($action);
	}

	/**
	 * Step 1: Create Config
	 * Collect database connection details
	 * @return \yii\web\Response|string
	 */
	public function actionIndex()
	{
		neon()->view->params['step'] = 1;
		if (InstallHelper::configFileExists()) {
			return $this->render('_exists', ['progress'=>InstallHelper::getProgress()]);
		}
		$installer = new InstallDatabase();

		if (neon()->request->isAjax) {
			$installer->load($_POST);
			$installer->isAjaxValidating = true;
			neon()->response->format = Response::FORMAT_JSON;
			return ActiveForm::validate($installer);
		}

		if ($installer->load(neon()->request->post())) {
			if ($installer->validate()) {
				// reload the global db with correct config
				$installer->setGlobalNeonDbConfig();
				// database credentials are valid!
				// write the config file with the details:
				if (InstallHelper::canWriteConfig()) {
					InstallHelper::createConfigFile($installer);
					return $this->render('_created');
				}
				return $this->render('_show', [ 'config' => InstallHelper::createConfigFileContents($installer) ]);
			}
		}
		return $this->render('index', ['model'=>$installer]);
	}
}