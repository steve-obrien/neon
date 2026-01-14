<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2008 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */
namespace neon\cms\models;

use neon\cms\models\CmsUrl;
use neon\core\db\ActiveRecord;

/**
 * CmsPage model
 *
 * The idea is that each url has a dedicated page.  A page then has content attached to it.
 * For example a page might show a an ecommerce product, a blog post and an author, each of these
 * items needs to be attached to the page.
 *
 * You could imagine a product manager for example would have to add a product and under the hood
 * configure a page record that shows the product.  This allows you to set custom page type things
 * and I think gives maximum flexibility, for example different templates for each page if you really
 * want.   However another approach could be to make a generic routes lookup table that defers everything
 * to the routing.
 *
 * @property string nice_id - a friendly looking id to reference this page in code and templates
 */
class CmsPage extends ActiveRecord
{
	const STATUS_PUBLISHED = 'PUBLISHED';
	const STATUS_DRAFT = 'DRAFT';

	public static $timestamps = true;
	public static $blame = true;
	public static $idIsUuid64 = true;
	public static $softDelete = true;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%cms_page}}';
	}

	/**
	 * Find page by its uuid (id) or nice_id
	 * @param string $pageId
	 * @return array of cms_page fields
	 */
	public static function findBy($pageId)
	{
		return neon()->cache->getOrSet("page.$pageId", function() use ($pageId) {
			return CmsPage::find()
				->where(['cms_page.nice_id' => $pageId])
				->orWhere(['id' => $pageId])
				->asArray()
				->one();
		});
	}

	/**
	 * @inheritdoc
	 */
	public function afterSave($insert, $changedAttributes)
	{
		parent::afterSave($insert, $changedAttributes);
		// clear caches
		neon()->cache->delete("page.".$this->nice_id);
		neon()->cache->delete("page.".$this->id);
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			['nice_id', 'unique'],
			[[
				'name', 'title', 'status', 'description', 'keywords', 'template', 'status',
				'og_description', 'og_title', 'og_image', 'og_title_use_seo', 'og_description_use_seo'
			], 'safe']
		];
	}

	/**
	 * Get an array of all urls that link to this nice_id
	 *
	 * @return CmsUrl[]
	 */
	public function getUrls()
	{
		return CmsUrl::find()->where(['nice_id' => $this->nice_id])->all();
	}

	/**
	 * Update a pages url
	 * This also creates a 301 redirect from the existing url to the new url if £createRedirect is true
	 *
	 * @param string $newUrl - the new url for the page
	 * @param boolean $createRedirect - whether to create a 301 redirect or not
	 * @param boolean $throw - whether to throw an exception if the page has multiple urls
	 * @throws \yii\web\HttpException
	 * @return boolean - whether new url was saved
	 */
	public function redirect($newUrl, $createRedirect, $throw=true)
	{
		// find urls that link to this page
		$urls = CmsUrl::find()->where(['nice_id' => $this->nice_id])->limit(10)->all();
		if (count($urls) > 1) {
			if ($throw) abort(400, 'There must be only one url that links to this page - otherwise the wrong url may be updated');
			return false;
		}
		$url = $urls[0];
		return $url->updateUrl($newUrl, $createRedirect);
	}

}
