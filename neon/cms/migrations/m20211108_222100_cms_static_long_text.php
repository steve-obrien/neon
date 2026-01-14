<?php

use neon\core\db\Migration;

/**
 * update column to be long text
 */
class m20211108_222100_cms_static_long_text extends Migration
{
	public function safeUp()
	{
		$this->alterColumn('cms_static_content', 'content', 'longtext');
	}

	public function safeDown()
	{
		$this->alterColumn('cms_static_content', 'content', 'text');
	}
}
