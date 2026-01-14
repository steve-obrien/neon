<?php
/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 25/09/2018
 * Time: 17:37
 */

namespace neon\cms\controllers;


use neon\core\web\AdminController;

class StyleController extends AdminController
{
	public function actionIndex()
	{
		neon()->view->styleManager->add('primary_color', '#ff0000', 'color');
		$form = neon()->view->styleManager->getForm();
		return $this->render('index', ['form' => $form]);
	}
}