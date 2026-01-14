<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2008 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */



namespace neon\cms\models;

use neon\core\db\ActiveRecord;
use yii\caching\CacheInterface;

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
 * @property string $nice_id - the nice_id of the page this url points to
 * @property string $url - the relative url on the site
 * @property string $redirect - a url to redirect to
 * @property int $hits - a number representing the number of times this url has been followed
 */
class CmsUrl extends ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%cms_url}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			['url', 'required'],
			['url', 'unique'],
			[['nice_id', 'url', 'redirect'], 'safe'],
			[['nice_id', 'url', 'redirect'], 'check'],
		];
	}

	public function check($attribute, $params)
	{
		if (empty($this->redirect) && empty($this->nice_id)) {
			$this->addError('url', 'A page id or a redirect must be specified');
		}
	}

	/**
	 * Update a url
	 * Specify whether a 301 redirect should be created for the old url by setting $createRedirect to true
	 *
	 * @param string $newUrl - the new url value
	 * @param boolean $createRedirect - whether to create a redirect
	 * @throws \yii\web\HttpException - if $newUrl already exists
	 * @return boolean whether new url was saved
	 */
	public function updateUrl($newUrl, $createRedirect)
	{
		if ($newUrl !== $this->url) {
			$oldUrl = $this->url;
			// lets sanity check here - make sure the new url does not already exist:
			abort_if(CmsUrl::find()->where(['url' => $newUrl])->exists(), 400, 'The new url must not already exist');
			$this->url = $newUrl;
			$saved = $this->save();
			// do the redirect:
			if ($createRedirect) {
				static::createRedirect($oldUrl, $newUrl);
			}
			return $saved;
		}
		return false;
	}

	/**
	 * Create a redirect
	 *
	 * @param string $fromUrl
	 * @param string $toUrl
	 * @return CmsUrl - the new CmsUrl record created
	 */
	public static function createRedirect($fromUrl, $toUrl)
	{
		$r = new CmsUrl();
		$r->url = $fromUrl;
		// this must be null to prevent page link lookups (on nice_id) returning redirects
		$r->nice_id = null;
		$r->redirect = $toUrl;
		$r->save();
		return $r;
	}

	/**
	 * Returns the url for this niceId or false if not found
	 *
	 * @param string $niceId
	 * @return bool|string
	 */
	public static function getUrlByNiceId($niceId)
	{
		$urls = static::getAllUrlsByNiceId();
		$url = $urls[$niceId]['url'] ?? false;
		// if we can not find the url we try the $niceId as the page $id parameter of the pages table
		if ($url === false) {
			$pages = static::getPagesIndex();
			$pageNiceId = $pages[$niceId] ?? false;
			$url = $urls[$pageNiceId]['url'] ?? false;
		}
		return $url;
	}

	public static function findByUrl($url)
	{
		return CmsUrl::find()
			->where(['url'=>$url])
			->asArray()
			->one();
	}

	/**
	 * Gets all urls indexed by nice_id
	 *
	 * @return array
	 */
	public static function getAllUrlsByNiceId()
	{
		return static::cache()->getOrSet('cms_url', function() {
			return collect(CmsUrl::find()->asArray()->all())
				->indexBy('nice_id')
				->all();
		});
	}

	public static function getPagesIndex()
	{
		return static::cache()->getOrSet('cms_url_pages', function() {
			return collect(CmsPage::find()->select('id, nice_id')->asArray()->all())
				->flatMap(function($i){ return [$i['id'] => $i['nice_id']]; })
				->all();
		});
	}

	/**
	 * @inheritDoc
	 */
	public function afterSave($insert, $changedAttributes)
	{
		parent::afterSave($insert, $changedAttributes);
		static::cache()->delete('cms_url');
		static::cache()->delete('cms_url_pages');
	}

	/**
	 * Get the cache component to use for url caching
	 *
	 * @return \yii\caching\CacheInterface
	 */
	public static function cache()
	{
		return neon()->cache;
	}
}
