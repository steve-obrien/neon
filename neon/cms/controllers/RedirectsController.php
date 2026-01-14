<?php

namespace neon\cms\controllers;

use neon\cms\models\CmsUrl;
use neon\core\web\AdminController;


class RedirectsController extends AdminController
{
	public $layout = '/admin-workbench';

	public function actionIndex()
	{
		$urls = CmsUrl::find()->where(['not', ['redirect' => null]])->asArray()->all();
		return $this->render('index.tpl', ['urls' => $urls]);
	}

	/**
	 * @return array
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\web\HttpException
	 */
	public function actionAdd()
	{
		if (!request()->isPost) abort(400, 'Must be a post method');

		$req = request()->validateData(['url' => null, 'redirect' => null], [
			[['url', 'redirect'], 'required'],
			[['url'], 'unique', 'targetClass' => CmsUrl::class, 'targetAttribute' => 'url', 'message' => 'This url already exists'],
		], 'post');

		if ($req->hasErrors()) {
			neon()->response->statusCode = 400;
			return $req->getErrors();
		}

		$cmsUrl = CmsUrl::createRedirect($req->url, $req->redirect);
		$cmsUrl->refresh();
		return $cmsUrl->toArray();
	}


	public function actionDelete()
	{
		if (!request()->isPost) abort(400, 'Must be a post method');
		$url = request()->postRequired('url', );
		$redirect = CmsUrl::find()->where(['not', ['redirect' => null]])->andWhere(['url' => $url])->one();
		if ($redirect===null) abort(404);
		return $redirect->delete();
	}
}
