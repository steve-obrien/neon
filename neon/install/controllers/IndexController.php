<?php

namespace neon\install\controllers;

use neon\install\helpers\InstallHelper;

/**
 * Install Progress: 0 / 3
 */
class IndexController extends InstallController
{

	public function actions()
	{
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			],
		];
	}
	
	/**
	 * If the system detects that installation is required it redirects here
	 * This controller action looks at the state of the installation process
	 * and redirects you to the correct installation step
	 * @return \yii\web\Response
	 */
	public function actionDispatch()
	{
		$route = InstallHelper::getRequiredInstallationRoute();
		return $this->redirect($route);
	}
	
	/**
	 * install progress: 0
	 * Start the install process
	 * @return string
	 */
	public function actionIndex()
	{
		neon()->view->params['step'] = 0;
		return $this->render('index.tpl', [
			'installed' => InstallHelper::hasLocalConfigFile(),
			'progress' => InstallHelper::getProgress(),
			'requirements' => InstallHelper::checkRequirements()
		]);
	}
	
}