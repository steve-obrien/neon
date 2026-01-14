<?php
/**
 * Created by PhpStorm.
 * User: steve
 * Date: 12/12/2015
 * Time: 11:28
 */

namespace neon\cms\grid;

use neon\cms\models\CmsPage;
use neon\cms\models\CmsUrl;
use neon\core\grid\query\IQuery;
use neon\core\helpers\Html;
use neon\core\helpers\Url;
use neon\user\models\User;
use yii\web\HttpException;

class PageGrid extends \neon\core\grid\Grid
{
	/**
	 * @inheritdoc
	 */
	public function configure()
	{
		$this->query = (new \yii\db\Query())
			->from('cms_page')
			->select(["*", "CONCAT_WS(', ', cms_url.url) as urls"])
			->innerJoin('cms_url', 'cms_page.nice_id = cms_url.nice_id')
			//->where("cms_page.template NOT LIKE 'partials/%'")
			->orderBy('url, title');

		$this->title = 'Pages';

		$this->setPageSize(100);

		$this->addColumn('id')->setAsIndex()->setVisible(false);

		$this->addColumn('screenshot')->setWidth('150px');
		// columns
		$this->addTextColumn('nice_id')
			->setTitle('Id')
			->setDbField('cms_page.nice_id')
			->setWidth('100px')
			->setVisible(true);

		//$this->addTextColumn('name')->setTitle('Reference Name');
		$this->addTextColumn('urls')->setDbField('cms_url.url')->setTitle('Urls Linking Here')->setWidth('200px');
		$this->addTextColumn('title')->setWidth('450px');
		$this->addTextColumn('template')->setWidth('100px');

		$this->addColumn('og_image')->setTitle('Social')->setWidth('100px')->setMember(new \neon\core\form\fields\Image('og_image'));

		$this->addButtonColumn('edit')
			->addButton('edit', ['/cms/editor/index' , 'id' => '{{nice_id}}'], '', '', '', ['data-toggle' => 'tooltip', 'title'=>'Edit']);

		
		$this->addScope('all', 'All');
		$this->addScope('published', 'Published');
		$this->addScope('draft', 'Draft');
		
		$this->addScope('deleted', 'Deleted');
	}

	/**
	 * @inheritdoc
	 */
	public function setup()
	{
		if ($this->isScopeActive('draft')) {
			$this->getColumn('nice_id')->addRowAction('actionPublish', 'Publish');
		}
		if ($this->isScopeActive('all')) {
			$this->addColumn('status');
		}
	}

	public function renderNice_id($model)
	{
		return "<div style='text-align:center;width'><div>{$model['name']}</div><code>{$model['nice_id']}</code></div>";
	}

	public function renderTitle($model)
	{

		return '<div style="font-weight:600;">' . str_replace(['{{', '}}'], ['{', '}'], $model['title'] ?? '') . '</div><div stlye="margin-top:4px;">' . str_replace(['{{', '}}'], ['{', '}'], $model['description'] ?? '') . '</div>';
	}

	public function renderUrls($model, $key, $index, $column)
	{
		return Html::a($model[$key],  $model[$key], ['target'=>'_blank']);
	}

	public function renderScreenshot($model)
	{
		$url = neon()->urlManager->createAbsoluteUrl(CmsUrl::getUrlByNiceId($model['nice_id']));
		return "<img width='150' src='https://image.thum.io/get/auth/54089-qckimgt/width/300/$url' />";
	}

	/**
	 * Quick publish action
	 * @param string $id - page id (gets passed row column data marked as index)
	 * @throws HttpException - if no page found with id
	 */
	public function actionPublish($id)
	{
		$page = $this->_getPage($id);
		$page->status = CmsPage::STATUS_PUBLISHED;
		$page->save();
	}

	/**
	 * Quick make draft action
	 * @param string $id - page id (gets passed row column data marked as index)
	 * @throws HttpException
	 */
	public function actionDraft($id)
	{
		$page = $this->_getPage($id);
		$page->status = CmsPage::STATUS_DRAFT;
		$page->save();
	}

	/**
	 * Get user active record by id
	 * @throws HttpException 500 error if user with $id does not exist
	 * @param string $uuid
	 * @return User
	 */
	private function _getPage($uuid)
	{
		$page = CmsPage::find()->where(['id' => $uuid])->one();
		if ($page === null) {
			throw new HttpException(404, "No page exists with id '$uuid'");
		}
		return $page;
	}

	/**
	 * @param IQuery $query
	 */
	public function scopeDeleted(IQuery $query)
	{
		$query->where('deleted', '=', 1);
	}

	/**
	 * @param IQuery $query
	 */
	public function scopePublished(IQuery $query)
	{
		$query->where('status', '=', 'PUBLISHED');
	}

	/**
	 * @param IQuery $query
	 */
	public function scopeDraft(IQuery $query)
	{
		$query->where('status', '=', 'DRAFT');
	}

	/**
	 * @param IQuery $query
	 */
	public function scopeAll(IQuery $query)
	{
		return $query;
	}

}
