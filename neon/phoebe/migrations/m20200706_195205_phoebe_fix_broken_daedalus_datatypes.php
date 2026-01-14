<?php

use neon\core\db\Migration;

class m20200706_195205_phoebe_fix_broken_daedalus_datatypes extends Migration
{
	public function safeUp()
	{
		$query = "SELECT `class_type`, `serialised_definition` FROM `phoebe_class` WHERE `phoebe_type`='daedalus'";
		$rows = neon()->db->createCommand($query)->queryAll();
		foreach ($rows as $row) {
			if (strpos($row['serialised_definition'], '"dataMapProvider":"dds"') === false) {
				dp("Ignoring $row[class_type]");
				continue;
			}
			dp("Converting $row[class_type]");
			$update = str_replace('"dataMapProvider":"dds"', '"dataMapProvider":"daedalus"', $row['serialised_definition']);
			$this->update('phoebe_class', ['serialised_definition'=>$update], "`phoebe_type`='daedalus' AND `class_type`='$row[class_type]'");
		}
	}

	public function safeDown()
	{
		$query = "SELECT `class_type`, `serialised_definition` FROM `phoebe_class` WHERE `phoebe_type`='daedalus'";
		$rows = neon()->db->createCommand($query)->queryAll();
		foreach ($rows as $row) {
			if (strpos($row['serialised_definition'], '"dataMapProvider":"daedalus"') === false) {
				dp("Ignoring $row[class_type]");
				continue;
			}
			dp("Converting $row[class_type]");
			$update = str_replace('"dataMapProvider":"daedalus"', '"dataMapProvider":"dds"', $row['serialised_definition']);
			$this->update('phoebe_class', ['serialised_definition'=>$update], "`phoebe_type`='daedalus' AND `class_type`='$row[class_type]'");
		}
	}
}
