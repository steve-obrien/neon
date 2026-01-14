<?php

namespace neon\cms\controllers;

use neon\core\web\AdminController;


class IndexController extends AdminController
{
	public $layout = '/admin-workbench';
	public function actionIndex()
	{
		// turn edit mode off when in the grid page view
		neon()->cms->page->setEditMode(false);
		$grid = new \neon\cms\grid\PageGrid();
		// If theme has pages.editor - then this is used for new page creation
		return $this->render('index.tpl', [
				'grid'=> $grid,
				'can_develop' => (neon()->env == 'dev'),
				'can_page_add' => file_exists(neon()->getAlias(neon()->cms->getThemeAlias().'/pages/editor.tpl'))
			]
		);
	}
}