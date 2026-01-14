<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2008 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\utilities\controllers;

use \neon\core\web\AdminController;

class ErrorController extends AdminController
{
	public $layout = '@neon/utilities/views/layout.php';

	public function actionIndex()
	{
		$this->view->title = 'Error Logs';
		return $this->render('index');
	}

	public function actionClear()
	{
		$errorLog = neon()->getAlias('@runtime/logs/error.log');
		if (file_exists($errorLog) && unlink($errorLog)) {
			neon()->session->setFlash('success', 'successfully cleared error.log');
		}
		$this->redirect('index');
	}
}
