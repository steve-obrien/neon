<?php

namespace neon\core\controllers;

use neon\core\web\Controller;

class SiteController extends Controller
{
	/**
	 * Default Home page - should be overridden in your app.
	 * @return string
	 */
	public function actionIndex()
	{
		return $this->render('index.tpl');
	}
}
