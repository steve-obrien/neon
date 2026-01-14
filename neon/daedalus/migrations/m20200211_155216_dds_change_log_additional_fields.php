<?php

use neon\core\db\Migration;

class m20200211_155216_dds_change_log_additional_fields extends Migration
{
	public function safeUp()
	{
		$this->execute("ALTER TABLE `dds_change_log` ADD `module` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_cs DEFAULT 'daedalus' COMMENT 'The module managing the object - default to daedalus' AFTER `log_uuid`;");
		$this->execute("ALTER TABLE `dds_change_log` ADD `associated_objects` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'List of associated object uuids' AFTER `object_uuid`;");
		$this->execute('CREATE INDEX `change_key` ON `dds_change_log` (`change_key`);');
	}

	public function safeDown()
	{
		$this->execute("ALTER TABLE dds_change_log DROP COLUMN `module`;");
		$this->execute("ALTER TABLE dds_change_log DROP COLUMN `associated_objects`;");
		$this->execute("DROP INDEX `change_key` ON `dds_change_log`;");
	}
}
