<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 05/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\cms\controllers;

use neon\cms\form\Page;
use neon\cms\models\CmsPage;
use neon\cms\models\CmsUrl;
use neon\core\grid\GridAssets;
use neon\core\helpers\Arr;
use neon\core\web\AdminController;
use yii\web\HttpException;


class EditorController extends AdminController
{
	public $layout = '/admin-workbench';

	/**
	 * @param null $id
	 * @return string
	 */
	public function actionIndex($id=null)
	{
		(new \neon\core\form\fields\Ide('ide'))->registerScripts(neon()->view);
		$pageResults = CmsPage::find()->asArray()->orderBy('name')->all();
		$urls = collect(CmsUrl::find()->asArray()->all())->indexBy('nice_id')->all();
		$pages = collect($pageResults)->map(function($page) use($urls) {
			$page['url'] = Arr::get($urls, [$page['nice_id'], 'url'], '');
			return $page;
		})->indexBy('id');
		neon()->cms->page->setEditMode(true);
		return $this->render('index', [
			'pages' => $pages,
			'id' => $id
		]);
	}

	/**
	 * An endpoint that cancels the edit mode - this can be called via ajax
	 * then the next page call edit mode will be cancelled - can manually refresh if exec in js context
	 */
	public function actionExitEditMode()
	{
		neon()->cms->page->setEditMode(false);
	}

	/**
	 * Add a new page
	 * @return array|string
	 * @throws \yii\base\UnknownPropertyException
	 */
	public function actionPageAdd()
	{
		$pageForm = new Page();
		if (neon()->request->isAjax) {
			return $pageForm->ajaxValidation();
		}
		if ($pageForm->processRequest()) {
			if (!$pageForm->save($errors)) {
				// something went wrong
				throw new \Exception( 'Something went wrong ' . print_r($errors, true));
			}
			$data = $pageForm->getData();
			return $this->redirect(['/cms/editor/index', 'id' => $data['nice_id']]);
		}

		return $this->render('add.tpl', ['page' => $pageForm]);
	}

	/**
	 * So users can create pages uses a standard themes/page/editor.tpl
	 * in the future this could ship with eon - but the editor would have 
	 * to also provide layout and global components. (nav / footer / etc)
	 * Or potentially you could select the layout file - and the editor could
	 * replace the $content smarty variable used for layouts in themes...
	 */
	public function actionPageAddEditor()
	{
		$pageForm = new Page();
		if (neon()->request->isAjax) {
			return $pageForm->ajaxValidation();
		}
		if ($pageForm->processRequest()) {
			if (!$pageForm->save($errors)) {
				// something went wrong
				throw new \Exception( 'Something went wrong ' . print_r($errors, true));
			}
			$data = $pageForm->getData();
			return $this->redirect(['/cms/editor/index', 'id' => $data['nice_id']]);
		}

		return $this->render('add.tpl', ['page' => $pageForm]);
	}

	/**
	 * Handle saving page updates
	 */
	public function actionPageUpdate()
	{
		$data = neon()->request->post();
		abort_if(! isset($data['CmsPage']['id']), 400, 'A "CmsPage[id]" parameter must be supplied');
		$cmsPage = CmsPage::findOne($data['CmsPage']['id']);
		$cmsPage->load(neon()->request->post());
		$cmsPage->save();

		if (isset($data['CmsPage']['url'])) {
			$cmsPage->redirect($data['CmsPage']['url'], neon()->request->post('redirect'), false);
		}

		$page = $cmsPage->toArray();
		$url = $cmsPage->getUrls();
		$page['url'] = $url[0]['url'];
		return $page;
	}

}
