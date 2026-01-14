<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 21/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */
namespace neon\cms\controllers;

use \neon\core\web\AdminController;

class SiteController extends AdminController
{
	public function actionIndex()
	{
		return $this->render('index');
	}
}
