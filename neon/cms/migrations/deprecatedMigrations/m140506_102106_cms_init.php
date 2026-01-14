<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

use \neon\cms\models\CmsUrl;
use \neon\cms\models\CmsPage;

/**
 * Initializes the User app tables
 */
class m140506_102106_cms_init extends \yii\db\Migration
{

	/**
	 * @inheritdoc
	 * Install the User, AuthRule, AuthItem, AuthItemChild, AuthAssignment tables
	 */
	public function safeUp()
	{
		$this->createTable(CmsPage::tableName(), [
			'id' => 'pk',
			'title' => 'string COMMENT "Page title"',
			'status' => 'string not null default "DRAFT"',
			'layout' => 'string COMMENT "the name of the layout template to load for this page"',
			'template' => 'string COMMENT "the name of the template file"'
		]);
		$this->createTable(CmsUrl::tableName(),[
			'id' => 'pk',
			'url' => 'string',
			'page_id' => 'int',
		]);
		$this->createIndex('url_key', CmsUrl::tableName(), '`url`(150)', true);
	}

	/**
	 * @inheritdoc
	 * Drop the User, AuthRule, AuthItem, AuthItemChild, AuthAssignment tables
	 */
	public function safeDown()
	{
		$this->dropTable(CmsPage::tableName());
		$this->dropTable(CmsUrl::tableName());
	}
}
