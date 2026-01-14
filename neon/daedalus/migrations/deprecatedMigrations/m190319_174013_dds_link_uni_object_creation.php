<?php

use neon\core\db\Migration;

class m190319_174013_dds_link_uni_object_creation extends Migration
{
	public function safeUp()
	{
		$sql = <<<EOQ
			insert into `dds_data_type` (`data_type_ref`, `label`, `description`, `definition`, `storage_ref`, `deleted`)
			values ('link_uni_object', 'Link Object - One to One', 'An object reference (uuid64) linking one object to any object across multiple daedalus table.', '{max:22}', 'uuid', 0);
EOQ;
		neon()->getDb()->createCommand($sql)->execute();
	}

	public function safeDown()
	{
		$sql = <<<EOQ
			delete from `dds_data_type` where `data_type_ref` = 'link_uni_object';
EOQ;
		neon()->getDb()->createCommand($sql)->execute();
	}
}
