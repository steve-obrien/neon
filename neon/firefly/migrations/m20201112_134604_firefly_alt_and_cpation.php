<?php

use neon\core\db\Migration;

class m20201112_134604_firefly_alt_and_cpation extends Migration
{
	public function safeUp()
	{
		$this->addColumn('firefly_file_manager', 'alt', $this->string());
		$this->addColumn('firefly_file_manager', 'caption', $this->string());
	}

	public function safeDown()
	{
		// create a down migration
		$this->dropColumn('firefly_file_manager', 'alt');
		$this->dropColumn('firefly_file_manager', 'caption');
	}
}
