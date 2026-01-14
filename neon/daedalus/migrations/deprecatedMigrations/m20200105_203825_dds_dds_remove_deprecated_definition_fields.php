<?php

use neon\core\db\Migration;

class m20200105_203825_dds_dds_remove_deprecated_definition_fields extends Migration
{
	public function safeUp()
	{
		$this->execute("ALTER TABLE dds_class DROP COLUMN `definition`;");
		$this->execute("ALTER TABLE dds_member DROP COLUMN `definition`;");
	}

	public function safeDown()
	{
		$this->execute("ALTER TABLE dds_class ADD COLUMN `definition` text COLLATE utf8mb4_unicode_ci COMMENT 'A definition for use by other modules, but not understood by DDS';");
		$this->execute("ALTER TABLE dds_member ADD COLUMN `definition` text COLLATE utf8mb4_unicode_ci COMMENT 'A definition for use by other modules, but not understood by DDS' AFTER `description`;");
	}
}
