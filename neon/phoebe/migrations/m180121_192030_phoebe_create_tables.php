<?php

use yii\db\Migration;

class m180121_192030_phoebe_create_tables extends Migration
{
	public function safeUp()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}
		$connection = \Yii::$app->getDb();

		// create the various tables for phoebe

		/**
		 * phoebeClass
		 *
		 * Stores the full definition of a particular phoebe class
		 * E.g. a daedalus table or application form. Versions are defined by
		 * the front end builder.
		 *
		 */

		$sql = <<<EOQ
-- --------------------------------------------------------

--
-- Table structure for table `phoebe_class`
--

DROP TABLE IF EXISTS `phoebe_class`;
CREATE TABLE `phoebe_class` (
  `phoebe_type` char(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The phoebe type of this class e.g. daedalus',
  `class_type` char(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The class type within the Phoebe type. E.g. table name for daedalus',
  `version` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The version number for this class definition. These are set by the builder / user as required',
  `module` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The neon module that created this',
  `label` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'A user facing label for this class',
  `description` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'A user facing description for this class',
  `version_locked` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If true then objects are locked to their version and versions are stored in this table',
  `volatile` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If true then object data can be updated from other sources e.g. from daedalus tables. If false the full object data is stored',
  `object_history` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If true then keep a history log for objects of this class',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When this was created',
  `updated` timestamp NULL DEFAULT NULL COMMENT 'Time of last change of the definition',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If true then the type has marked deleted. Definitions are kept for data integrity.',
  `serialised_definition` mediumtext COLLATE utf8_unicode_ci COMMENT 'The serialised definition of this class the nature of which is phoebe type specific',
  PRIMARY KEY (`phoebe_type`,`class_type`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='The definition of the phoebe class';
EOQ;
		$connection->createCommand($sql)->execute();


		/**
		 * phoebeObject
		 *
		 * The phoebe section of the object data - e.g. the link through to the daedalus object
		 * the set of daedalus objects, or the full object data depending on the type and setting
		 * of the phoebe class.
		 */
		$sql = <<<EOQ
-- --------------------------------------------------------

--
-- Table structure for table `phoebe_object`
--

DROP TABLE IF EXISTS `phoebe_object`;
CREATE TABLE `phoebe_object` (
  `uuid` char(22) COLLATE latin1_general_cs NOT NULL COMMENT 'The phoebe uuid of this object',
  `phoebe_type` char(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The phoebe type of this class e.g. daedalus',
  `class_type` char(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The class type within the Phoebe type. E.g. table name for daedalus',
  `version` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'The associated definition version number upon saving',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When this was created',
  `updated` timestamp NULL DEFAULT NULL COMMENT 'Time of last change',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If true then the object is marked deleted. Values are kept for data integrity.',
  `serialised_data` mediumtext COLLATE utf8_unicode_ci COMMENT 'The object data - sufficient for up to 16MiB of data',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='The phoebe data component of the object';
EOQ;

		$connection->createCommand($sql)->execute();

		/**
		 * phoebeObjectHistory
		 *
		 * Stores history of objects (if keep_history is set on the class)
		 */
		$sql = <<<EOQ
-- --------------------------------------------------------

--
-- Table structure for table `phoebe_object_history`
--

DROP TABLE IF EXISTS `phoebe_object_history`;
CREATE TABLE `phoebe_object_history` (
  `uuid` char(22) COLLATE latin1_general_cs NOT NULL COMMENT 'A unique history uuid for the object',
  `phoebe_type` char(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The phoebe type of this class e.g. daedalus',
  `class_type` char(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The class type within the Phoebe type. E.g. table name for daedalus',
  `object_uuid` char(22) COLLATE latin1_general_cs NOT NULL COMMENT 'The phoebe uuid of the object',
  `archived` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When this was archived',
  `serialised_data` mediumtext COLLATE utf8_unicode_ci COMMENT 'The archived object data',
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='History log of the phoebe data component of the object';
EOQ;

		$connection->createCommand($sql)->execute();

		/**
		 * update daedalus class modules from 'form' to 'phoebe'
		 */
		$sql = "UPDATE `dds_class` SET `module`='phoebe' WHERE `module`='form'";
		$connection->createCommand($sql)->execute();

	}

	public function safeDown()
	{
		$this->dropTable('phoebe_class');
		$this->dropTable('phoebe_object');
		$this->dropTable('phoebe_object_history');
		/**
		 * revert daedalus class modules from 'phoebe' to 'form'
		 */
		$connection = \Yii::$app->getDb();
		$sql = "UPDATE `dds_class` SET `module`='form' WHERE `module`='phoebe'";
		$connection->createCommand($sql)->execute();
	}
}
