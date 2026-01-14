<?php


namespace neon\utilities\controllers;


use neon\core\helpers\File;
use neon\core\web\AdminController;
use yii\base\Exception;

class SystemController extends AdminController
{
	public $layout = '@neon/utilities/views/layout.php';

	public function actionReport()
	{
		$this->view->title = 'System Report';
		$requirements = require(__DIR__.'/requirements.php');
		return $this->render('report', ['requirements'=>$requirements]);
	}

	public function actionPhpfpm()
	{
		$this->view->title = 'PHP FPM';
		return $this->render('phpfpm');
	}
}
