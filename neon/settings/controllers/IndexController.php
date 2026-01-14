<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2008 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\settings\controllers;

use \neon\core\web\AdminController;

class IndexController extends AdminController
{
	public function actionIndex()
	{
		$form = new \neon\settings\forms\AppSettings('setting');
		$form->registerScripts($this->getView());
		if (neon()->request->isAjax)
			return $form->ajaxValidation();
		$form->process();
		return $this->render('index', [
			'form' => $form,
		]);
	}

}
