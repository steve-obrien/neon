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
class m170106_131700_cms_url extends \neon\core\db\Migration
{
	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$cms_url = CmsUrl::tableName();
		$this->dropColumn($cms_url, 'id');
		$this->addPrimaryKey('id', $cms_url, '`url`(150)');
		$this->alterColumn($cms_url, 'url', $this->string(255)->notNull()->comment('Unique url primary key'));
		$this->dropIndex('url_key', $cms_url);
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$cms_url = CmsUrl::tableName();
		$this->dropPrimaryKey('id', $cms_url);
		$this->createIndex('url_key', CmsUrl::tableName(), 'url', true);
		$this->addColumn($cms_url, 'id', $this->primaryKey());
	}
}
