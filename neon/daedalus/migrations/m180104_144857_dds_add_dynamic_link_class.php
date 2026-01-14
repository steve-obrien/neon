<?php

use yii\db\Migration;

class m180104_144857_dds_add_dynamic_link_class extends Migration
{
	public function safeUp()
	{
		// find all of the member definitions that have a dataMapKey associated that are uuid64s
		$rows = $this->db->createCommand("SELECT * FROM `dds_member` WHERE `data_type_ref`='uuid64'")->queryAll();
		foreach ($rows as $row) {
			$def = json_decode($row['definition'], true);
			if (isset($def['dataMapProvider']) && isset($def['dataMapKey'])) {
				echo "\nUpdating $row[class_type]::$row[member_ref] link_class to $def[dataMapKey]";
				$this->db->createCommand("UPDATE `dds_member` SET `link_class`='$def[dataMapKey]' WHERE `class_type`='$row[class_type]' AND `member_ref`='$row[member_ref]'")->execute();
			}
		}
		echo "\n";

		// update the new data types link_uni and link_multi
		// current link -> link_multi and uuid64 links become link_uni

		// Through cascaded updates the link will be changed to link_multi just by changing the data type
		$this->db->createCommand("UPDATE `dds_data_type` SET `data_type_ref`='link_multi', `label`='Link - Many to Many' WHERE `data_type_ref`='link'")->execute();
		// however just in case any are left over ...
		$this->db->createCommand("UPDATE `dds_member` SET `data_type_ref`='link_multi' WHERE `data_type_ref`='link'")->execute();

		$this->db->createCommand("INSERT INTO `dds_data_type` (`data_type_ref`, `label`, `description`, `definition`, `storage_ref`, `deleted`) VALUES ('link_uni','Link - One to One','An object reference (uuid64) linking one object to another','{max:22}','uuid',0)")->execute();

		// find all link class items and update their type references
		$rows = $this->db->createCommand("SELECT * FROM `dds_member` WHERE `data_type_ref`='uuid64' AND `link_class` IS NOT NULL")->queryAll();
		foreach ($rows as $row) {
			echo "\nUpdating $row[class_type]::$row[member_ref] data_type_ref to 'link_uni'";
			$this->db->createCommand("UPDATE `dds_member` SET `data_type_ref`='link_uni' WHERE `class_type`='$row[class_type]' AND `member_ref`='$row[member_ref]'")->execute();
		}
		echo "\n";
	}

	public function safeDown()
	{
		// find all link_uni class items and revert type to uuid64
		$rows = $this->db->createCommand("UPDATE `dds_member` SET `data_type_ref`='uuid64' WHERE `data_type_ref`='link_uni'")->execute();

		// revert the new data types link_uni and link_multi
		// current link_multi -> link and link_uni become uuid64

		// Through cascaded updates the link will be changed to link_multi just by changing the data type
		$this->db->createCommand("UPDATE `dds_data_type` SET `data_type_ref`='link', `label`='link' WHERE `data_type_ref`='link_multi'")->execute();
		// but again just in case this doesn't work ...
		$this->db->createCommand("UPDATE `dds_member` SET `data_type_ref`='link' WHERE `data_type_ref`='link_multi'")->execute();

		// and get rid of the link_uni type
		$this->db->createCommand("DELETE FROM `dds_data_type` WHERE `data_type_ref`='link_uni'")->execute();

		// find all of the member definitions that have a link class set and clear them
		$rows = $this->db->createCommand("SELECT * FROM `dds_member` WHERE `data_type_ref`='uuid64' AND `link_class` IS NOT NULL")->queryAll();
		foreach ($rows as $row) {
			echo "\nUpdating $row[class_type]::$row[member_ref] to link_class to NULL";
			$this->db->createCommand("UPDATE `dds_member` SET `link_class`=NULL WHERE `class_type`='$row[class_type]' AND `member_ref`='$row[member_ref]'")->execute();
		}
		echo "\n";
	}
}
