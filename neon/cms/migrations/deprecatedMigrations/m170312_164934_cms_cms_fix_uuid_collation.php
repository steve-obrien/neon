<?php

use yii\db\Migration;

class m170312_164934_cms_cms_fix_uuid_collation extends Migration
{
	public function safeUp()
	{
		// set the cms_page type to the a case sensitive collation
		$query = "ALTER TABLE `cms_page` CHANGE `id` `id` CHAR(22) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL COMMENT 'A uuid using base 64'";
		$this->execute($query);
		// and on the cms_static_content
		$query = "ALTER TABLE `cms_static_content` CHANGE `page_id` `page_id` CHAR(22) CHARACTER SET latin1 COLLATE latin1_general_cs NULL DEFAULT NULL COMMENT 'A uuid using base 64'";
		$this->execute($query);
	}

	public function safeDown()
	{
		// set the uuid back to previous charset
		$query = "ALTER TABLE `cms_page` CHANGE `id` `id` CHAR(22) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'A uuid using base 64'";
		$this->execute($query);
		// set the cms_static_content references
		$query = "ALTER TABLE `cms_static_content` CHANGE `page_id` `page_id` CHAR(22) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'A uuid using base 64'";
		$this->execute($query);
	}
}
