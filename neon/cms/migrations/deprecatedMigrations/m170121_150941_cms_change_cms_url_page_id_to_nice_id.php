<?php

use yii\db\Migration;

use \neon\cms\models\CmsUrl;

class m170121_150941_cms_change_cms_url_page_id_to_nice_id extends Migration
{
	public function safeUp()
	{
		$cms_url = CmsUrl::tableName();
		$cms_page = \neon\cms\models\CmsPage::tableName();
		$this->dropForeignKey('page_foreign_key', $cms_url);
		$this->renameColumn($cms_url, 'page_id', 'nice_id');
		$this->addForeignKey('page_foreign_key', $cms_url, 'nice_id', $cms_page, 'nice_id', null, 'CASCADE');
	}

	public function safeDown()
	{
		$cms_url = CmsUrl::tableName();
		$cms_page = \neon\cms\models\CmsPage::tableName();
		$this->dropForeignKey('page_foreign_key', $cms_url);
		$this->renameColumn($cms_url, 'nice_id', 'page_id');
		$this->addForeignKey('page_foreign_key', $cms_url, 'page_id', $cms_page, 'nice_id', null, 'CASCADE');
	}
}
