<?php

use yii\db\Schema;
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
-- Table structure for table `dds_class`
--

DROP TABLE IF EXISTS `dds_class`;
CREATE TABLE IF NOT EXISTS `dds_class` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `class_type` char(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The type of data class this is e.g. ''Blog''',
  `label` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'The user facing label for the class',
  `description` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'A description of the class for the end user',
  `module` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'The neon module (e.g. cms) that the data belongs with if required',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether or not the class has been soft deleted',
  `object_count` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'A count of the number of objects of this type',
  `object_deleted_count` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'A count of the number of deleted objects of this type',
  PRIMARY KEY (`id`),
  UNIQUE KEY `classRef` (`class_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Holds the basic information about the dynamic data class';

-- --------------------------------------------------------

--
-- Table structure for table `dds_data_type`
--

DROP TABLE IF EXISTS `dds_data_type`;
CREATE TABLE IF NOT EXISTS `dds_data_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id for the row',
  `data_type_ref` char(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'a reference for the data type used in code',
  `label` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'User facing label for the data type (e.g. fixed choice)',
  `description` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'A description of the data type',
  `definition` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Any definitions inc constraints that the UI should know about as JSON',
  `storage_ref` char(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'the type of database storage type used to define this type',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether or not this data type is deleted',
  PRIMARY KEY (`id`),
  UNIQUE KEY `data_type_ref` (`data_type_ref`),
  KEY `storage_type` (`storage_ref`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Definition of the type of user facing object the class member can be (e.g. a choice)';

-- --------------------------------------------------------

--
-- Table structure for table `dds_member`
--

DROP TABLE IF EXISTS `dds_member`;
CREATE TABLE IF NOT EXISTS `dds_member` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id for the row',
  `class_type` char(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The class reference',
  `member_ref` char(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'A reference for the member used within the code generation',
  `data_type_ref` char(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The reference to the data type',
  `label` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'a user facing label for the member',
  `description` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'A user facing description of the member',
  `definition` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'A definition for the field element for display to the end user e.g. fixed select choices, whether or not it''s required, default values etc',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'whether or not this field has been soft deleted',
  PRIMARY KEY (`id`),
  UNIQUE KEY `class_member` (`class_type`,`member_ref`) USING BTREE,
  KEY `data_type` (`data_type_ref`) USING BTREE,
  KEY `member_ref` (`member_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Defines a particular member of a data class';

-- --------------------------------------------------------
--
-- Table structure for table `dds_object`
--

DROP TABLE IF EXISTS `dds_object`;
CREATE TABLE IF NOT EXISTS `dds_object` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id for the row',
  `class_type` char(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The class this object is an instance of',
  `label` varchar(300) COLLATE utf8mb4_unicode_ci NULL COMMENT 'A label to identify this object easily',
  `deleted` tinyint(1) DEFAULT '0' COMMENT 'whether or not the object has been deleted',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'when this was created',
  `updated` timestamp NULL DEFAULT NULL COMMENT 'when this was updated',
  PRIMARY KEY (`id`),
  KEY `class_ref` (`class_type`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='An instance of a particular data type';

-- --------------------------------------------------------

--
-- Table structure for table `dds_storage`
--

DROP TABLE IF EXISTS `dds_storage`;
CREATE TABLE IF NOT EXISTS `dds_storage` (
  `storage_ref` char(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'unique reference to the storage type within the code',
  `label` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'User facing label for the storage type',
  `type` enum('INTEGER_TINY','INTEGER_SHORT','INTEGER','INTEGER_LONG','FLOAT','DOUBLE','DATE','DATETIME','TEXT_SHORT','TEXT','TEXT_LONG','BINARY_SHORT','BINARY','BINARY_LONG','CHAR') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'the storage type',
  `description` varchar(8000) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'User facing description of what the type will take',
  PRIMARY KEY (`storage_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Defines the storage mechanisms for a particular data type';

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dds_data_type`
--
ALTER TABLE `dds_data_type`
  ADD CONSTRAINT `dds_data_type_storage_ref` FOREIGN KEY (`storage_ref`) REFERENCES `dds_storage` (`storage_ref`) ON UPDATE CASCADE;

--
-- Constraints for table `dds_member`
--
ALTER TABLE `dds_member`
  ADD CONSTRAINT `dds_member_class_ref` FOREIGN KEY (`class_type`) REFERENCES `dds_class` (`class_type`) ON UPDATE CASCADE,
  ADD CONSTRAINT `dds_member_data_type_ref` FOREIGN KEY (`data_type_ref`) REFERENCES `dds_data_type` (`data_type_ref`) ON UPDATE CASCADE;

--
-- Constraints for table `dds_object`
--
ALTER TABLE `dds_object`
  ADD CONSTRAINT `dds_object_class_type` FOREIGN KEY (`class_type`) REFERENCES `dds_class` (`class_type`) ON UPDATE CASCADE;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

EOQ;
		$command = $connection->createCommand($sql);
		$command->execute();

		$sql=<<<EOQ
--
-- Dumping data for table `dds_storage`
--

INSERT INTO `dds_storage` (`storage_ref`, `label`, `type`, `description`)
VALUES
('binary','Normal Binary','BINARY','For normal binaries such as images up to 16MB in size'),
('binarylong','Very Large Binary','BINARY_LONG','For storing binary data such as documents up to 4GB in size'),
('binaryshort','Small Binary','BINARY_SHORT','For small binaries up to 16KB in size (e.g thumbnails)'),
('char','Fixed Char','CHAR','For char of a fixed length (set by data type) up to 150 characters'),
('date','Date','DATE','For storing a date (no time associated) from the years 1000 to 9999'),
('datetime','Date and Time','DATETIME','For storing date and time from the years 1000 to 9999'),
('float','Float','FLOAT','For floating numbers (decimal with limited but large precision) up to about 1 x 10^±38'),
('floatdouble','Double Float','DOUBLE','A double precision float up to 1 x 10^±308'),
('integer','Integer','INTEGER','For integers up to ±2,147,483,648'),
('integerlong','Large Integer','INTEGER_LONG','For integers up to 9 quintillion (9 with 18 zeros after it). '),
('integershort','Short Integer','INTEGER_SHORT','For integers up to ±8192'),
('integertiny','Tiny Integer','INTEGER_TINY','For numbers up to ±128'),
('text','Text','TEXT','For text up to about twenty thousand characters (about two to four thousand average English words)'),
('textlong','Long Text','TEXT_LONG','For text up to about five million characters (about half to one million average English words). '),
('textshort','Short Text','TEXT_SHORT','For text up to 150 characters (about 15 to 30 words)');
EOQ;
		$command = $connection->createCommand($sql);
		$command->execute();
	}

	public function down()
	{
		# deletion order is important because of foreign key constraints
		$this->dropTable('dds_object');
		$this->dropTable('dds_member');
		$this->dropTable('dds_class');
		$this->dropTable('dds_data_type');
		$this->dropTable('dds_storage');
	}
}
