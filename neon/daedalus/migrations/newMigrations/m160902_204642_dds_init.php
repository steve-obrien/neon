<?php

use yii\db\Migration;

class m160902_204642_dds_init extends Migration
{
	public function up()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}
		$connection = \Yii::$app->getDb();

		// create the various tables for dds
		$sql=<<<EOQ
-- --------------------------------------------------------

--
-- Table structure for table `dds_storage`
--

DROP TABLE IF EXISTS `dds_storage`;
CREATE TABLE `dds_storage` (
  `storage_ref` char(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'unique reference to the storage type within the code',
  `label` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'User facing label for the storage type',
  `type` enum('INTEGER_TINY','INTEGER_SHORT','INTEGER','INTEGER_LONG','FLOAT','DOUBLE','DATE','DATETIME','TEXT_SHORT','TEXT','TEXT_LONG','BINARY_SHORT','BINARY','BINARY_LONG','TIME','CHAR','UUID') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'the storage type',
  `description` varchar(8000) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'User facing description of what the type will take',
  PRIMARY KEY (`storage_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Defines the storage mechanisms for a particular data type';

-- --------------------------------------------------------

--
-- Table structure for table `dds_class`
--

DROP TABLE IF EXISTS `dds_class`;
CREATE TABLE `dds_class` (
  `class_type` char(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The type of data class this is e.g. ''Blog''',
  `label` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'The user facing label for the class',
  `description` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'A description of the class for the end user',
  `module` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'The neon module (e.g. cms) that the data belongs with if required',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether or not the class has been soft deleted',
  `change_log` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether or not a change log is maintained for this class',
  `count_total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'A count of the number of objects of this type',
  `count_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'A count of the number of deleted objects of this type',
  `count_deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'A count of the number of deleted objects of this type',
  `definition` text COLLATE utf8mb4_unicode_ci COMMENT 'A definition for use by other modules, but not understood by DDS',
  UNIQUE KEY `classRef` (`class_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Holds the basic information about the dynamic data class';

-- --------------------------------------------------------

--
-- Table structure for table `dds_data_type`
--

DROP TABLE IF EXISTS `dds_data_type`;
CREATE TABLE `dds_data_type` (
  `data_type_ref` char(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'a reference for the data type used in code',
  `label` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'User facing label for the data type (e.g. fixed choice)',
  `description` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'A description of the data type',
  `definition` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Any definitions inc constraints that the UI should know about as JSON',
  `storage_ref` char(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'the type of database storage type used to define this type',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether or not this data type is deleted',
  UNIQUE KEY `data_type_ref` (`data_type_ref`),
  KEY `storage_type` (`storage_ref`) USING BTREE,
  CONSTRAINT `dds_data_type_storage_ref` FOREIGN KEY (`storage_ref`) REFERENCES `dds_storage` (`storage_ref`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Definition of the type of user facing object the class member can be (e.g. a choice)';

-- --------------------------------------------------------

--
-- Table structure for table `dds_link`
--

DROP TABLE IF EXISTS `dds_link`;
CREATE TABLE `dds_link` (
  `from_id` char(22) COLLATE latin1_general_cs NOT NULL COMMENT 'The UUID64 of the from object',
  `from_member` char(50) COLLATE latin1_general_cs NOT NULL COMMENT 'The member ref within from object',
  `to_id` char(22) COLLATE latin1_general_cs NOT NULL COMMENT 'The UUID64 of the to object',
  UNIQUE KEY `linky` (`from_id`,`from_member`,`to_id`),
  KEY `from_id` (`from_id`),
  KEY `to_id` (`to_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `dds_member`
--

DROP TABLE IF EXISTS `dds_member`;
CREATE TABLE `dds_member` (
  `class_type` char(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The class reference',
  `member_ref` char(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'A reference for the member used within the code generation',
  `data_type_ref` char(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The reference to the data type',
  `label` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'a user facing label for the member',
  `description` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'A user facing description of the member',
  `definition` text COLLATE utf8mb4_unicode_ci COMMENT 'A definition for use by other modules, but not understood by DDS',
  `choices` text COLLATE utf8mb4_unicode_ci COMMENT 'If the member is of data type choice, the set of available choices',
  `link_class` char(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'For links, the linked classtype.',
  `map_field` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If true, then this is field data used in defining maps',
  `created` datetime NOT NULL COMMENT 'When the member was first added',
  `updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'When the member was last updated',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'whether or not this field has been soft deleted',
  UNIQUE KEY `class_member` (`class_type`,`member_ref`) USING BTREE,
  KEY `data_type` (`data_type_ref`) USING BTREE,
  KEY `member_ref` (`member_ref`),
  CONSTRAINT `dds_member_class_ref` FOREIGN KEY (`class_type`) REFERENCES `dds_class` (`class_type`) ON UPDATE CASCADE,
  CONSTRAINT `dds_member_data_type_ref` FOREIGN KEY (`data_type_ref`) REFERENCES `dds_data_type` (`data_type_ref`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Defines a particular member of a data class';

-- --------------------------------------------------------
--
-- Table structure for table `dds_object`
--

DROP TABLE IF EXISTS `dds_object`;
CREATE TABLE `dds_object` (
  `_uuid` char(22) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL COMMENT 'A universally unique identifier for this object',
  `_class_type` char(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The class this object is an instance of',
  `_deleted` tinyint(1) DEFAULT '0' COMMENT 'whether or not the object has been deleted',
  `_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'when this was created',
  `_updated` timestamp NULL DEFAULT NULL COMMENT 'when this was updated',
  PRIMARY KEY (`_uuid`),
  KEY `class_ref` (`_class_type`),
  CONSTRAINT `dds_object_class_type` FOREIGN KEY (`_class_type`) REFERENCES `dds_class` (`class_type`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='An instance of a particular data type';

-- --------------------------------------------------------
--
-- Table structure for table `dds_change_log`
--

DROP TABLE IF EXISTS `dds_change_log`;
CREATE TABLE `dds_change_log` (
  `log_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Autoincremented id to ensure entries made within the same second can be distinguished',
  `log_uuid` char(22) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL DEFAULT '' COMMENT 'Log unique identifier',
  `module` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_cs DEFAULT 'daedalus' COMMENT 'The module managing the object - default to daedalus',
  `object_uuid` char(22) CHARACTER SET latin1 COLLATE latin1_general_cs DEFAULT NULL COMMENT 'The object that was changed',
  `associated_objects` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'List of associated object uuids',
  `class_type` char(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'The class the object is an instance of',
  `change_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'A key defining the type of change e.g. EDIT',
  `description` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'A short user friendly description of the change',
  `who` char(22) CHARACTER SET latin1 COLLATE latin1_general_cs DEFAULT '' COMMENT 'Who made the changes if known',
  `when` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the changes were made',
  `before` longblob COMMENT 'What the changed fields values were before the change',
  `after` longblob COMMENT 'What the changed fields values were after the change',
  PRIMARY KEY (`log_id`),
  KEY `log_uuid` (`log_uuid`),
  KEY `object_uuid` (`object_uuid`),
  KEY `class_type` (`class_type`),
  KEY `change_key` (`change_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log of changes made to selected Daedalus tables';
EOQ;
		$command = $connection->createCommand($sql);
		$command->execute();

		$sql=<<<EOQ
--
-- Dumping data for table `dds_storage`
--

INSERT INTO `dds_storage` (`storage_ref`, `label`, `type`, `description`)
VALUES
	('binary', 'Normal Binary', 'BINARY', 'For normal binaries such as images up to 16MB in size'),
	('binarylong', 'Very Large Binary', 'BINARY_LONG', 'For storing binary data such as documents up to 4GB in size'),
	('binaryshort', 'Small Binary', 'BINARY_SHORT', 'For small binaries up to 16KB in size (e.g thumbnails)'),
	('char', 'Fixed Char', 'CHAR', 'For char of a fixed length (set by data type) up to 150 characters'),
	('date', 'Date', 'DATE', 'For storing a date (no time associated) from the years 1000 to 9999'),
	('datetime', 'Date and Time', 'DATETIME', 'For storing date and time from the years 1000 to 9999'),
	('float', 'Float', 'FLOAT', 'For floating numbers (decimal with limited but large precision) up to about 1 x 10^±38'),
	('floatdouble', 'Double Float', 'DOUBLE', 'A double precision float up to 1 x 10^±308'),
	('integer', 'Integer', 'INTEGER', 'For integers up to ±2,147,483,648'),
	('integerlong', 'Large Integer', 'INTEGER_LONG', 'For integers up to 9 quintillion (9 with 18 zeros after it). '),
	('integershort', 'Short Integer', 'INTEGER_SHORT', 'For integers up to ±8192'),
	('integertiny', 'Tiny Integer', 'INTEGER_TINY', 'For numbers up to ±128'),
	('text', 'Text', 'TEXT', 'For text up to about twenty thousand characters (about two to four thousand average English words)'),
	('textlong', 'Long Text', 'TEXT_LONG', 'For text up to about five million characters (about half to one million average English words). '),
	('textshort', 'Short Text', 'TEXT_SHORT', 'For text up to 150 characters (about 15 to 30 words)'),
	('time', 'Time', 'TIME', 'For storing time only'),
	('uuid', 'Universally Unique Id', 'UUID', 'For storing UUIDs (which are stored as case sensitive chars of fixed length)');

--
-- Dumping data for table `dds_data_type`
--

INSERT INTO `dds_data_type` (`data_type_ref`, `label`, `description`, `definition`, `storage_ref`, `deleted`)
VALUES
	('binary', 'Normal Binary', 'For normal binaries such as images up to 16MB in size', '', 'binary', 0),
	('binarylong', 'Very Large Binary', 'For storing binary data such as documents up to 4GB in size', '', 'binarylong', 0),
	('binaryshort', 'Small Binary', 'For small binaries up to 16KB in size (e.g thumbnails)', '', 'binaryshort', 0),
	('boolean', 'Boolean', 'For true | false value', '{\"size\":1}', 'integertiny', 0),
	('choice', 'Choice', 'For the key of a key:value choice. The mapping is stored elsewhere', '{max:150}', 'textshort', 0),
	('choice_multiple', 'Multiple Choice', 'For storing multiple choice keys', '{\"size\":10000}', 'text', 0),
	('date', 'Date', 'For storing a date (no time associated) from the years 1000 to 9999', '', 'date', 0),
	('datetime', 'Date and Time', 'For storing date and time from the years 1000 to 9999', '', 'datetime', 0),
	('document', 'Document', 'For storing documents up to 4GB in size', '', 'binarylong', 0),
	('email', 'Email', 'For emails up to 150 characters', '{max:150}', 'textshort', 0),
	('file_ref', 'File Reference', 'A UUID64 reference for a file', '{\"size\":22}', 'uuid', 0),
	('file_ref_multi', 'Multiple File References', 'Multiple files stored against member. Data is stored in dds_link not the ddt_xxx table.', '{\"size\":10}', 'char', 0),
	('float', 'Float', 'For floating numbers (decimal with limited but large precision) up to about 1 x 10^±38', '', 'float', 0),
	('floatdouble', 'Double Float', 'A double precision float up to 1 x 10^±308', '', 'floatdouble', 0),
	('html', 'HTML', 'For HTML up to about a million characters', '{\"size\":1000000}', 'textlong', 0),
	('image', 'Image File', 'For storing images up to 16MB in size', '', 'binary', 0),
	('image_ref', 'Image Reference', 'A UUID64 reference for an image', '{\"size\":22}', 'uuid', 0),
	('integer', 'Integer', 'For integers up to ±2,147,483,648', '', 'integer', 0),
	('integerlong', 'Large Integer', 'For integers up to 9 quintillion (9 with 18 zeros after it).', '', 'integerlong', 0),
	('integershort', 'Short Integer', 'For integers up to ±8192', '{\"max\":8192}', 'integershort', 0),
	('integertiny', 'Tiny Integer', 'For numbers up to ±128', '{\"max\":128}', 'integertiny', 0),
	('json', 'JSON', 'For JSON up to about a million characters ', '{max:1000000}', 'textlong', 0),
	('link_multi', 'Link - Many to Many', 'Single or many to many link placeholder. Can be used in joins. Data is stored in dds_link not the ddt_xxx table.', '{\"size\":10}', 'char', 0),
	('link_uni', 'Link - One to One', 'An object reference (uuid64) linking one object to another', '{max:22}', 'uuid', 0),
	('link_uni_object', 'Link Object - One to One', 'An object reference (uuid64) linking one object to any object across multiple daedalus table.', '{max:22}', 'uuid', 0),
	('markdown', 'Markdown', 'For markdown files of up to about a million characters ', '{max:1000000}', 'textlong', 0),
	('mime', 'MIME Type', 'For storing mime types', '{\"size\":256}', 'text', 0),
	('text', 'Text', 'For text up to about twenty thousand characters (about two to four thousand average English words)', '{\"size\":20000}', 'text', 0),
	('textlong', 'Long Text', 'For text up to about five million characters (about half to one million average English words).', '{\"size\":5000000}', 'textlong', 0),
	('textshort', 'Short Text', 'For text up to 150 characters (about 15 to 30 words)', '{\"size\":150}', 'textshort', 0),
	('time', 'Time', 'For storing a time only field', '', 'time', 0),
	('url', 'URL', 'URL up to 150 characters', '{\"size\":150}', 'textshort', 0),
	('uuid', 'UUID', 'For Universally Unique Identifiers', '{\"size\":36}', 'uuid', 0),
	('uuid64', 'UUID64', 'For Universally Unique Identifiers in base 64', '{\"size\":22}', 'uuid', 0);


EOQ;
		$command = $connection->createCommand($sql);
		$command->execute();
	}

	public function down()
	{
		# deletion order is important because of foreign key constraints
		$this->dropTable('dds_change_log');
		$this->dropTable('dds_object');
		$this->dropTable('dds_member');
		$this->dropTable('dds_link');
		$this->dropTable('dds_class');
		$this->dropTable('dds_data_type');
		$this->dropTable('dds_storage');
	}
}
