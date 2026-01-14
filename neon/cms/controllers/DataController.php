<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2020 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\cms\controllers;

use neon\cms\components\Page;
use neon\cms\components\Renderer;
use neon\cms\components\Theme;
use neon\core\web\AdminController;
use neon\core\helpers\Html;


class DataController extends AdminController
{
	public function actionDaedalusEditor()
	{
		$get = neon()->request->get();
		return $this->renderAjax('daedalusForm.tpl', [
			'classType' => $get['classType'],
			'key' => $get['key']
		]);
	}


//	public function actionDaedalusEditor2()
//	{
//		$get = neon()->request->get();
//		$form = neon()->getPhoebe()->getForm('daedalus', $get['classType'], ['uuid' => $get['key']]);
//		return $form->toArray();
//	}


	/**
	 * Update a dynamic or static widget field
	 * @return void
	 */
	public function actionUpdateWidgetField()
	{
		// see if we have a key and if not we can do nothing
		$key = neon()->request->post('key');
		if (empty($key))
			return;
		$content = neon()->request->postUnsafe('content');
		$content = Html::sanitise($content, $this->inlineAllowedTags);
		$pageId = neon()->request->post('pageId', null);
		try {
			$result = neon('cms')->getICmsStaticData()->editStaticContent($key, $pageId, $content);
			if ($result === true)
				return ['success'=>true, 'saved'=>$content];
			else
				return neon()->debug ? ['success'=>false, 'errors'=>$result] : ['success'=>false];
		} catch (\Exception $e) {
			return neon()->debug ? ['success'=>false, 'errors'=>$e->getMessage()] : ['success'=>false];
		}
	}

	/**
	 * Update a static content widget
	 * @return void
	 */
	public function actionUpdateContentWidget($content=null)
	{
		$key = neon()->request->post('key');
		$content = $content ? $content : neon()->request->postUnsafe('content');
		// this controller action is for people with access to the editor
		// - they are already authenticated and authorized and should not be putting malicious code into their site.
		// $content = Html::purify($content, false);
		$pageId = neon()->request->post('pageId', null);
		try {
			$result = neon()->cms->getICmsStaticData()->editStaticContent($key, $pageId, $content);
			if ($result === true)
				return ['success'=>true, 'saved'=>$content];
			else if (neon()->debug)
				return neon()->debug ? ['success'=>false, 'errors'=>$result] : ['success'=>false];
		} catch (\Exception $e) {
			return neon()->debug ? ['success'=>false, 'errors'=>$e->getMessage()] : ['success'=>false];
		}
	}

	public function actionServerRender()
	{
		$tag = neon()->request->post('tag');
		$params = neon()->request->post('params', []);
		$renderer = new Renderer(new Page());
		$formatParams = '';
		foreach($params as $key => $value) {
			$formatParams .= $key.'="'.$value.'" ';
		}
		$out = $renderer->renderString('{'.$tag.' '.$formatParams .' }', false);
		return $this->getView()->renderAjaxContent($out);
	}

	/**
	 * request an IDdsClassManagement interface
	 * @return \neon\daedalus\services\ddsManager\interfaces\IDdsClassManagement
	 */
	public function getIDdsClassManagement() {
		return neon('dds')->getIDdsClassManagement();
	}

	/**
	 * request an IDdsDataTypeManagement interface
	 * @return \neon\daedalus\services\ddsManager\interfaces\IDdsDataTypeManagement
	 */
	public function getIDdsDataTypeManagement() {
		return neon('dds')->getIDdsDataTypeManagement();
	}

	/**
	 * request an IDdsObjectManagement interface
	 * @return \neon\daedalus\services\ddsManager\interfaces\IDdsObjectManagement
	 */
	public function getIDdsObjectManagement() {
		return neon('dds')->getIDdsObjectManagement();
	}
	/**
	 * The set of tags that we allow within normal editable code
	 * @var string
	 */
	protected $inlineAllowedTags = "<br><b><strong><em><u>";

	/**
	 * The set of tags that we allow within wysiwyg editable code
	 * @var string
	 */
	protected $wysiwygAllowedTags = "<p><a><em><strong><b><i><u><strike><cite><code><ul><ol><li><dl><dt><dd><img><table><tbody><tr><td><th><h1><h2><h3><h4><h5><h6><blockquote><hr><span><br><sup><sub><div><mark><pre>";

}