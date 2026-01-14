<?php

use neon\core\db\Migration;

class m20190827_155050_dds_add_datatype_filerefmulitple extends Migration
{
	public function safeUp()
	{
		$sql = <<<EOQ
SET foreign_key_checks = 0;
INSERT INTO `dds_data_type` (`data_type_ref`, `label`, `description`, `definition`, `storage_ref`, `deleted`)
VALUES
	('file_ref_multi', 'Multiple File References', 'Multiple files stored against member. Data is stored in dds_link not the ddt_xxx table.', '{\"size\":10}', 'char', 0);
SET foreign_key_checks = 1;
EOQ;
		$this->execute($sql);
	}

	public function safeDown()
	{
		$sql = <<<EOQ
SET foreign_key_checks = 0;
DELETE FROM `dds_data_type` WHERE `data_type_ref`='file_ref_multi' LIMIT 1;
SET foreign_key_checks = 1;
EOQ;
		$this->execute($sql);
	}
}
