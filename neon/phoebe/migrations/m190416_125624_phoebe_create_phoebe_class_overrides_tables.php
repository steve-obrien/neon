<?php

use neon\core\db\Migration;

class m190416_125624_phoebe_create_phoebe_class_overrides_tables extends Migration
{
	public function safeUp()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}
		$connection = \Yii::$app->getDb();

		// create the new table for phoebe_class_overrides

		/**
		 * phoebeClassOverrides
		 * Contains any overrides for a phoebe_class
		 */

		$sql = <<<EOQ
-- --------------------------------------------------------

--
-- Table structure for table `phoebe_class_overrides`
--
DROP TABLE IF EXISTS `phoebe_class_overrides`;
CREATE TABLE `phoebe_class_overrides` (
  `uuid` char(22) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL DEFAULT '' COMMENT 'A unique uuid for the override',
  `phoebe_type` char(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'The phoebe_type of the phoebe_class being overridden',
  `class_type` char(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'The class_type of the phoebe_class being overridden',
  `version` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The version number of the phoebe_class being overridden',
  `label` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'A user facing label for this override',
  `selector` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'A selector that may be used to further classify this override, e.g. user role',
  `is_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether or not the overrides are currently active',
  `active_from` datetime NOT NULL COMMENT 'When the override is active from',
  `active_to` datetime NULL DEFAULT NULL COMMENT 'When the override is active until',
  `serialised_overrides` mediumtext COLLATE utf8_unicode_ci COMMENT 'The serialised set of overrides which is type specific',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When this was created',
  `updated` timestamp NULL DEFAULT NULL COMMENT 'Time of last change of the overrides',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If true then the type has marked deleted. Overrides are kept for data integrity.',
  PRIMARY KEY (`uuid`),
  KEY `phoebe_class` (`phoebe_type`,`class_type`,`version`),
  UNIQUE `phoebe_class_overrides` (`phoebe_type`,`class_type`,`version`, `active_from`, `selector`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Overrides of the definition of a phoebe class';
EOQ;
		$this->execute($sql);
		//$connection->createCommand($sql)->execute();

		// add modifications of phoebe_object table
		$sql = <<<EOQ
ALTER TABLE `phoebe_object` ADD
  `override_uuid` char(22) CHARACTER SET latin1 COLLATE latin1_general_cs DEFAULT NULL COMMENT 'Uuid of any override that was applied to the class definition'
AFTER `version`;
EOQ;
		$connection->createCommand($sql)->execute();

		// add modifications to phoebe_object_history table
		$sql = <<<EOQ
ALTER TABLE `phoebe_object_history` ADD
  `version` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The version number of the phoebe_class being overridden'
AFTER `class_type`;
ALTER TABLE `phoebe_object_history` ADD
  `override_uuid` char(22) CHARACTER SET latin1 COLLATE latin1_general_cs NULL COMMENT 'The uuid of any phoebe class overrides applied'
AFTER `version`;
EOQ;
		$connection->createCommand($sql)->execute();

	}

	public function safeDown()
	{
		$this->dropTable('phoebe_class_overrides');
		$this->dropColumn('phoebe_object', 'override_uuid');
		$this->dropColumn('phoebe_object_history', 'version');
		$this->dropColumn('phoebe_object_history', 'override_uuid');
	}
}
