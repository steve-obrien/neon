<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\cms\services\cmsManager;

use neon\cms\services\cmsManager\interfaces\ICmsStaticData;
use neon\cms\services\cmsManager\models\CmsStaticContent;

class CmsManager
implements ICmsStaticData
{
	/**
	 * @inheritdoc
	 */
	public function editStaticContent($key, $pageId, $content)
	{
		$model = $this->getModel($key, $pageId);
		if (!$model) {
			$model = new CmsStaticContent;
			$model->key = $key;
			$model->page_id = $this->fixEmptyPageId($pageId);
		}
		$model->content = $content;
		return ($model->save() ? true : $model->errors);
	}

	/**
	 * @inheritdoc
	 */
	public function getStaticContent($key, $pageId)
	{
		$model = $this->getModel($key, $pageId);
		return $model ? $model->content : null;
	}

	/**
	 * @inheritdoc
	 */
	public function bulkStaticContentRequest($requests)
	{
		$requestedData = [];
		if (count($requests)>0) {
			$query = CmsStaticContent::find()
				->select("`key`, `page_id` as `pageId`, `content`");
			foreach ($requests as $id => $request) {
				// manage null returns from database
				$requestedData[$id] = null;
				$query->orWhere([
					'key'=>$request['key'],
					'page_id' => $this->fixEmptyPageId($request['pageId'])
				]);
			}
			$results = $query->asArray()->all();
			foreach ($results as $r) {
				$id = $this->createStaticContentId($r);
				$requestedData[$id] = $r;
			}
		}
		return $requestedData;
	}

	/**
	 * @inheritdoc
	 */
	public function bulkCreateStaticContentIds($requests)
	{
		$newRequests = [];
		foreach ($requests as $r) {
			$id = $this->createStaticContentId($r);
			$newRequests[$id] = $r;
		}
		return $newRequests;
	}

	/**
	 * @inheritdoc
	 */
	public function createStaticContentId($request)
	{
		if (!empty($request['key'])) {
			$pageId = $this->fixEmptyPageId($request['pageId']);
			$key = $request['key'] ?? '';
			$pageId = $pageId ?? '';
			return md5('cms_static_content::' . trim($key) . trim($pageId));
		}
	}

	/**
	 * Get hold of a CmsStaticContent model given its key and page id
	 * @param string $key
	 * @param string $pageId
	 * @return null | CmsStaticContent
	 */
	private function getModel($key, $pageId)
	{
		$pageId = $this->fixEmptyPageId($pageId);
		return CmsStaticContent::find()->where(['key'=>$key, 'page_id'=>$pageId])->limit(1)->one();
	}

	/**
	 * The rest of the system may pass in a null-like pageId which fails
	 * when calling the database. This converts those into true nulls
	 * @param string $pageId
	 * @return string
	 */
	private function fixEmptyPageId($pageId)
	{
		return empty($pageId) ? null : "$pageId";
	}

}