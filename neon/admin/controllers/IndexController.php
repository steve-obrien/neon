<?php

namespace neon\admin\controllers;

use neon\core\web\AdminController;

class IndexController extends AdminController
{
	public function actionIndex()
	{
		return $this->render('index');
	}
}
