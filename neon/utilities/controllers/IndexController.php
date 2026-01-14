<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2008 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\utilities\controllers;

use \neon\core\web\AdminController;

class IndexController extends AdminController
{
	public $layout = '@neon/utilities/views/layout.php';

	public function actionIndex()
	{
		$this->view->title = 'Neon Updates';
		return $this->render('index');
	}
}
