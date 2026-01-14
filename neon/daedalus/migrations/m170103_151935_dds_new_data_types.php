<?php

use yii\db\Migration;

class m170103_151935_dds_new_data_types extends Migration
{
	public function safeUp()
	{
		$connection = neon()->db;

		$this->alterColumn('dds_storage', 'type', "enum('INTEGER_TINY','INTEGER_SHORT','INTEGER','INTEGER_LONG','FLOAT','DOUBLE','DATE','DATETIME','TEXT_SHORT','TEXT','TEXT_LONG','BINARY_SHORT','BINARY','BINARY_LONG','CHAR','TIME') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'the storage type'");

		$sql =<<<EOQ
SET foreign_key_checks = 0;
REPLACE INTO {{%dds_storage}} (`storage_ref`, `label`, `type`, `description`)
VALUES
('time', 'Time', 'TIME', 'For storing time only'),
('char','Fixed Char','CHAR','For char of a fixed length (set by data type) up to 150 characters');
SET foreign_key_checks = 1;
EOQ;
		$connection->createCommand($sql)->execute();


		$sql =<<<EOQ
SET foreign_key_checks = 0;
REPLACE INTO {{%dds_data_type}} (`data_type_ref`, `label`, `description`, `definition`, `storage_ref`, `deleted`)
VALUES
('image_ref','Image Reference','A UUID64 reference for an image','{max:22}','char',0),
('file_ref','File Reference','A UUID64 reference for a file','{max:22}','char',0),
('json','JSON','For JSON up to about a million characters ','{max:1000000}','textlong',0),
('markdown','Markdown','For markdown files of up to about a million characters ','{max:1000000}','text',0),
('email','Email','For emails up to 150 characters','{max:150}','textshort',0),
('time','Time','For storing a time only field','','time',0);
SET foreign_key_checks = 1;
EOQ;
		$connection->createCommand($sql)->execute();

	}

	public function safeDown()
	{
	}
}
