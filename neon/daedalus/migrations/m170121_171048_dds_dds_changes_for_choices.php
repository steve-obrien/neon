<?php

use yii\db\Migration;

class m170121_171048_dds_dds_changes_for_choices extends Migration
{
	public function Up()
	{
		// preparing dds to manage choices. Need a new data_type of choice and a place to
		// store the set of choices within Daedalus for easy retrieval. While here also
		// increasing the size of the definition of a member to cope with the choices
		// within the definition

		$connection = neon()->db;

		// Get rid of the cms_static_content in DDS (non-reversible) if it exists at
		// this point as it's not needed and may break later migrations below
//$sql =<<<EOQ
//SET foreign_key_checks = 0;
//DROP TABLE IF EXISTS `ddt_cms_static_content`;
//DELETE FROM `dds_member` WHERE `class_type`='cms_static_content';
//DELETE FROM `dds_class` WHERE `class_type`='cms_static_content';
//SET foreign_key_checks = 1;
//EOQ;
//		$connection->createCommand($sql)->execute();

		// 1. add the choices datatype
$sql =<<<EOQ
SET foreign_key_checks = 0;
REPLACE INTO `dds_data_type` (`data_type_ref`, `label`, `description`, `definition`, `storage_ref`, `deleted`)
VALUES
('choice','Choice','For the key of a key:value choice. The mapping is stored elsewhere','{max:150}','textshort',0);
SET foreign_key_checks = 1;
EOQ;
		$connection->createCommand($sql)->execute();

		// 2. add a choices field to the member
		$this->addColumn('{{%dds_member}}', 'choices', "TEXT NULL COMMENT 'If the member is of data type choice, the set of available choices' AFTER `definition`");

		// 3. Change the member definition to be of type text
		$this->alterColumn('{{%dds_member}}', 'definition', "TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'A definition for the field element for display to the end user e.g. fixed select choices, if required, default values etc'");

	}

	public function Down()
	{
		$connection = neon()->db;

$sql =<<<EOQ
SET foreign_key_checks = 0;
DELETE FROM {{%dds_data_type}} WHERE `data_type_ref`='choice' LIMIT 1;
SET foreign_key_checks = 1;
EOQ;
		$connection->createCommand($sql)->execute();

		$this->dropColumn('{{%dds_member}}', 'choices');

		$this->alterColumn('{{%dds_member}}', 'definition', "VARCHAR(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'A definition for the field element for display to the end user e.g. fixed select choices, if required, default values etc'");

	}
}
