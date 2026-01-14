<?php

use neon\core\db\Migration;

/**
 * Migrations need to be stored to millisecond accuracy so they can be reverted properly
 */
class m160901_000000_increase_migration_time_accuracy extends Migration
{
	public function safeUp()
	{
		$this->execute("ALTER TABLE migration MODIFY COLUMN `apply_time` DOUBLE(16,4) NOT NULL;");
	}

	public function safeDown()
	{
		$this->execute("ALTER TABLE migration MODIFY COLUMN `apply_time` INT(11) NOT NULL;");
	}
}
