<?php

use neon\core\db\Migration;

class m20200602_010805_cms_redirects extends Migration
{
	public function safeUp()
	{
		$this->addColumn('cms_url', 'redirect', $this->string(255)->null()->comment('The redirect url'));
		$this->alterColumn('cms_url', 'nice_id', $this->string(100)->null()->comment('The foreign key of the page'));
	}

	public function safeDown()
	{
		$this->dropColumn('cms_url', 'redirect');
	}
}
