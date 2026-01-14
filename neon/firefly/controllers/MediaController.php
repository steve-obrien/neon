<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 28/11/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\firefly\controllers;

use neon\firefly\assets\BrowserAsset;
use neon\core\web\AdminController;

/**
 * Class MediaController
 * @package neon\firefly\controllers
 */
class MediaController extends AdminController
{
	public $layout = '/admin-workbench';

	/**
	 * The main admin application media browser
	 * @return string
	 */
	public function actionIndex()
	{
		BrowserAsset::register($this->view);
		return $this->render('index.tpl');
	}
}