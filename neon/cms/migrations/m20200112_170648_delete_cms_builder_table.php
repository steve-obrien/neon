<?php

use neon\core\db\Migration;

/**
 * Create names based on the nice ids for blank name sites
 */
class m20200112_170648_delete_cms_builder_table extends Migration
{
	public function safeUp()
	{
		// Create a name for all pages with no name based on the nice id
		$this->execute("DROP TABLE IF EXISTS `cms_builder`");
	}

	public function safeDown()
	{
		// the table was never used so no need to do a down
	}
}
