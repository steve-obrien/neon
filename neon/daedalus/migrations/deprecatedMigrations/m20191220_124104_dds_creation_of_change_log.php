<?php

use neon\core\db\Migration;

class m20191220_124104_dds_creation_of_change_log extends Migration
{
	public function safeUp()
	{
		// create a change log option on a class
		$this->execute("ALTER TABLE dds_class ADD `change_log` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether or not a change log is maintained for this class' AFTER `deleted`");

		// create a change log table
		$tableDefinition = <<<EOQ
CREATE TABLE `dds_change_log` (
  `log_id` BIGINT NOT NULL AUTO_INCREMENT COMMENT 'Autoincremented id to ensure entries made within the same second can be distinguished',
  `log_uuid` char(22) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL DEFAULT '' COMMENT 'Log unique identifier',
  `object_uuid` char(22) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL COMMENT 'The object that was changed',
  `class_type` char(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'The class the object is an instance of',
  `change_key` varchar(100) NOT NULL COMMENT 'A key defining the type of change e.g. EDIT',
  `description` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'A short user friendly description of the change',
  `who` char(22) CHARACTER SET latin1 COLLATE latin1_general_cs DEFAULT '' COMMENT 'Who made the changes if known',
  `when` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the changes were made',
  `changes` longblob COMMENT 'What the changes were',
  PRIMARY KEY (`log_id`),
  KEY (`log_uuid`),
  KEY `object_uuid` (`object_uuid`),
  KEY `class_type` (`class_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log of changes made to selected Daedalus tables';
EOQ;
		$this->execute($tableDefinition);
	}

	public function safeDown()
	{
		// remove the change log option on a class
		$this->execute("ALTER TABLE dds_class DROP COLUMN `change_log`");

		// remove the log table
		$this->execute("DROP TABLE `dds_change_log`");
	}
}
