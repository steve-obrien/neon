<?php

namespace neon\cms\controllers;

use neon\cms\form\Component;
use neon\core\web\AdminController;
use \neon\cms\models\CmsPage;
use \neon\cms\models\CmsUrl;

class ComponentController extends AdminController
{
	public $layout = '/admin-workbench';

	public function actionIndex()
	{
		return $this->render('index.tpl');
	}

	public function actionAdd()
	{
		$component = new Component();
		return $this->render('add.tpl', [
			'cmpForm' => $component
		]);
	}
}