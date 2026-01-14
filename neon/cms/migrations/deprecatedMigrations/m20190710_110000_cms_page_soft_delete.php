<?php

use neon\core\db\Migration;

class m20190710_110000_cms_page_soft_delete extends Migration
{
	public function safeUp()
	{
		$this->addColumnSoftDelete('{{%cms_page}}', \neon\core\db\ActiveRecord::DELETED);
		$this->addColumn('{{%cms_page}}', 'name', $this->string()->comment('A human readable name for the page')->after('nice_id'));
	}

	public function safeDown()
	{
		$this->dropColumn('{{%cms_page}}', \neon\core\db\ActiveRecord::DELETED);
		$this->dropColumn('{{%cms_page}}', 'name');
	}
}
