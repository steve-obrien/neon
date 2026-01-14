<?php

use neon\core\db\Migration;

class m20200119_192834_dds_update_change_log extends Migration
{
	public function safeUp()
	{
		$this->execute("ALTER TABLE dds_change_log MODIFY `object_uuid` char(22) CHARACTER SET latin1 COLLATE latin1_general_cs DEFAULT NULL COMMENT 'The object that was changed'");
		$this->execute("ALTER TABLE dds_change_log ADD `before` longblob COMMENT 'What the changed fields values were before the change' AFTER `when`;");
		$this->execute("ALTER TABLE dds_change_log ADD `after` longblob COMMENT 'What the changed fields values were after the change' AFTER `before`;");
		$this->execute("ALTER TABLE dds_change_log DROP COLUMN `changes`;");
	}

	public function safeDown()
	{
		$this->execute("ALTER TABLE dds_change_log MODIFY `object_uuid` char(22) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL COMMENT 'The object that was changed'");		$this->execute("ALTER TABLE dds_change_log DROP COLUMN `before`;");
		$this->execute("ALTER TABLE dds_change_log DROP COLUMN `after`;");
		$this->execute("ALTER TABLE dds_change_log ADD `changes` longblob COMMENT 'What the changes were' AFTER `when`;");
	}
}
