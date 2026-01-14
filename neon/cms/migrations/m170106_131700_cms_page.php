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
class m170106_131700_cms_page extends \neon\core\db\Migration
{
	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$cms_url = CmsUrl::tableName();
		$cms_page = CmsPage::tableName();

		// Add a nice id
		$this->addColumn($cms_page, 'nice_id',
			$this->string(100)->unique()
				->comment('A unique readable id name for the page, that may be displayed to the user, it also helps give context to know what the page is')
			. ' AFTER id'
		);
		// timestamps
		$this->addColumn($cms_page, 'created_at', $this->createdAt());
		$this->addColumn($cms_page, 'updated_at', $this->updatedAt());

		// blame
		$this->addColumn($cms_page, 'created_by', $this->createdBy());
		$this->addColumn($cms_page, 'updated_by', $this->updatedBy());

		// remember current page ids
		$this->addColumn($cms_page, 'old_id', $this->integer(11));
		$this->execute("UPDATE $cms_page SET `old_id` = `id`");

		// create the uuids and nice_ids
		$this->alterColumn($cms_page, 'id', $this->char(22)->comment("A uuid using base 64"));
		$this->run('Generating Page UUIDs and nice_ids', function() {
			// the soft delete column has not been added yet - so lets turn off this behaviour!
			CmsPage::$softDelete = false;
			foreach(CmsPage::find()->each(100) as $page) {
				$page->id = CmsPage::uuid64();
				$page->nice_id = "page_{$page->old_id}";
				$page->save();
			}
		});

		// update references
		$this->alterColumn($cms_url, 'page_id', $this->string(100)->notNull()->comment('cms_page foreign key using the nice_id to maintain useful readability'));

		// after several attempts at doing the update in a single query (which works generally)
		// ("UPDATE $cms_url INNER JOIN $cms_page SET $cms_url.`page_id` = $cms_page.id WHERE $cms_page.old_id = $cms_url.page_id")
		// but failing due to weird timestamp double fail error on MySQL 5.7, split out the update of the field into two
		// steps. Probably caused some kind of race condition between update and read??
		$this->addColumn($cms_url, 'page_id_temp', $this->string(100)->notNull());
		$this->execute("UPDATE $cms_url INNER JOIN $cms_page SET $cms_url.`page_id_temp` = $cms_page.nice_id WHERE $cms_page.old_id = $cms_url.page_id");
		// Add a comment to this line
		$this->execute("UPDATE $cms_url SET $cms_url.`page_id` = $cms_url.`page_id_temp`");
		$this->dropColumn($cms_url, 'page_id_temp');
		// we need the old_id for down to work
		// usefull whilst developing - may remove later
		// $this->dropColumn($cms_page, 'old_id');

		$this->addForeignKey('page_foreign_key', $cms_url, 'page_id', $cms_page, 'nice_id', null, 'CASCADE');
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$cms_url = CmsUrl::tableName();
		$cms_page = CmsPage::tableName();
		$this->dropForeignKey('page_foreign_key', $cms_url);

		// timestamps
		$this->dropColumn($cms_page, 'created_at');
		$this->dropColumn($cms_page, 'updated_at');

		// blame
		$this->dropColumn($cms_page, 'created_by');
		$this->dropColumn($cms_page, 'updated_by');
		// change page id from uuid to auto incrementing integers
		// update references
		$this->execute(
			"UPDATE $cms_url INNER JOIN $cms_page ON $cms_page.nice_id = $cms_url.page_id SET `page_id` = $cms_page.old_id"
		);
		$this->execute("UPDATE $cms_page SET `id` = `old_id`");
		$this->alterColumn($cms_page, 'id', $this->integer(11) . ' NOT NULL AUTO_INCREMENT');
		$this->dropColumn($cms_page, 'old_id');
		$this->dropColumn($cms_page, 'nice_id');
		$this->alterColumn($cms_url, 'page_id', $this->integer(11)->comment('cms_page foreign key'));
	}
}
