<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 11/11/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\dev\controllers;

use \neon\core\helpers\File;
use neon\core\web\AdminController;
use yii\web\HttpException;
use \neon\core\helpers\Url;
use \neon\core\helpers\Arr;


Class DocsController extends AdminController
{
	public $layout = 'docs';

	public function actionIndex($app='', $doc='')
	{
		if ($app == '')
			$app = 'dev';

		$app = ($app == '') ? 'dev' : $app;
		$doc = ($doc == '') ? 'index.md' : $doc;

		$this->view->params['menu'] = $this->getDocsMenu();

		return $this->render('index', [
			'content' => $this->getDocsContent($app, $doc),
		]);
	}

	public function getDocsPath($app)
	{
		return neon(strtolower($app))->getDocsPath();
	}

	public function getDocsMenu()
	{
		$menu = [];
		foreach (neon()->getActiveApps() as $app) {
			$path = $app->getDocsPath();
			if (is_dir($path)) {
				foreach (File::findFiles($path) as $file) {
					$menu[] = [
						'app' => $app->name,
						'title' => ucwords(str_replace(['-', '.md', '.tpl'], ' ', basename($file))),
						'link' => Url::toRoute(['/dev/docs/index', 'app' => $app->id, 'doc' => basename($file)])
					];
				}
			}
		}
		return Arr::groupBy($menu, 'app');
	}

	public function getDocsContent($app, $doc)
	{
		$path = $this->getDocsPath($app);
		$file = "$path/$doc";

		if (!file_exists($file)) {
			throw new HttpException(404, "This page does not exists '$file'");
		}

		return $this->view->renderFile($file);
	}

}