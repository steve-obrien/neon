<?php

use yii\db\Migration;

class m170312_163159_dds_dds_fix_uuid_collation extends Migration
{
	public function safeUp()
	{
		// fix the object table uuid to be correc
		$query = "ALTER TABLE `dds_object` CHANGE `_uuid` `_uuid` CHAR(22) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL COMMENT 'A universally unique identifier for this object'";
		$this->execute($query);

		// create a new storage enum of UUID which is used to save correctly
		$query = "ALTER TABLE `dds_storage` CHANGE `type` `type` ENUM('INTEGER_TINY','INTEGER_SHORT','INTEGER','INTEGER_LONG','FLOAT','DOUBLE','DATE','DATETIME','TEXT_SHORT','TEXT','TEXT_LONG','BINARY_SHORT','BINARY','BINARY_LONG','TIME','CHAR','UUID') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'the storage type'";
		$this->execute($query);

		// and add a new storage type to map to it
		$query = "INSERT INTO `dds_storage` (`storage_ref`, `label`, `type`, `description`) VALUES ('uuid', 'Universally Unique Id', 'UUID', 'For storing UUIDs (which are stored as case sensitive chars of fixed length)')";
		$this->execute($query);

		// update the appropriate data types
		$query = "UPDATE `dds_data_type` SET `storage_ref`='uuid', `definition`='{\"size\":36}' WHERE `dds_data_type`.`data_type_ref` = 'uuid'";
		$this->execute($query);
		$query = "UPDATE `dds_data_type` SET `storage_ref`='uuid', `definition`='{\"size\":22}' WHERE `dds_data_type`.`data_type_ref` IN ('file_ref','image_ref','uuid64')";
		$this->execute($query);

		// change all UUIDs on tables to new format
		$query = "SELECT `class_type` FROM `dds_class`";
		$tables = $this->db->createCommand($query)->queryAll();
		foreach ($tables as $t) {
			$query = "ALTER TABLE `ddt_$t[class_type]` CHANGE `_uuid` `_uuid` CHAR(22) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL COMMENT 'Object _uuid from the dds_object table'";
			$this->execute($query);
		}

		// change all UUID64 types on tables to new format
		$query = "SELECT `class_type`, `member_ref` FROM `dds_member` WHERE	`data_type_ref`='uuid64'";
		$members = $this->db->createCommand($query)->queryAll();
		foreach ($members as $m) {
			$query = "ALTER TABLE `ddt_$m[class_type]` CHANGE `$m[member_ref]` `$m[member_ref]` CHAR(22) CHARACTER SET latin1 COLLATE latin1_general_cs";
			$this->execute($query);
		}
		$query = "SELECT `class_type`, `member_ref` FROM `dds_member` WHERE	`data_type_ref`='uuid'";
		$members = $this->db->createCommand($query)->queryAll();
		foreach ($members as $m) {
			$query = "ALTER TABLE `ddt_$m[class_type]` CHANGE `$m[member_ref]` `$m[member_ref]` CHAR(36) CHARACTER SET latin1 COLLATE latin1_general_cs";
			$this->execute($query);
		}
	}

	public function safeDown()
	{
		// change all UUID64 types on tables to old format
		$query = "SELECT `class_type`, `member_ref` FROM `dds_member` WHERE	`data_type_ref`='uuid64'";
		$members = $this->db->createCommand($query)->queryAll();
		foreach ($members as $m) {
			$query = "ALTER TABLE `ddt_$m[class_type]` CHANGE `$m[member_ref]` `$m[member_ref]` CHAR(22) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
			$this->execute($query);
		}
		$query = "SELECT `class_type`, `member_ref` FROM `dds_member` WHERE	`data_type_ref`='uuid'";
		$members = $this->db->createCommand($query)->queryAll();
		foreach ($members as $m) {
			$query = "ALTER TABLE `ddt_$m[class_type]` CHANGE `$m[member_ref]` `$m[member_ref]` CHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
			$this->execute($query);
		}

		// change all UUIDs on tables to old format
		$query = "SELECT `class_type` FROM `dds_class`";
		$tables = $this->db->createCommand($query)->queryAll();
		foreach ($tables as $t) {
			$query = "ALTER TABLE `ddt_$t[class_type]` CHANGE `_uuid` `_uuid` CHAR(22) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Object _uuid from the dds_object table'";
			$this->execute($query);
		}

		// downdate the appropriate data types
		$query = "UPDATE `dds_data_type` SET `storage_ref` = 'char' WHERE `dds_data_type`.`data_type_ref` IN ('uuid','file_ref','image_ref','uuid64')";
		$this->execute($query);

		// remove the dds storage type
		$query = "DELETE FROM `dds_storage` WHERE `dds_storage`.`storage_ref` = 'uuid'";
		$this->execute($query);

		// get rid of the UUID storage type
		$query = "ALTER TABLE `dds_storage` CHANGE `type` `type` ENUM('INTEGER_TINY','INTEGER_SHORT','INTEGER','INTEGER_LONG','FLOAT','DOUBLE','DATE','DATETIME','TEXT_SHORT','TEXT','TEXT_LONG','BINARY_SHORT','BINARY','BINARY_LONG','TIME','CHAR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'the storage type'";
		$this->execute($query);

		// set the uuid back to previous charset
		$query = "ALTER TABLE `dds_object` CHANGE `_uuid` `_uuid` CHAR(22) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'A universally unique identifier for this object'";
		$this->execute($query);
	}
}
