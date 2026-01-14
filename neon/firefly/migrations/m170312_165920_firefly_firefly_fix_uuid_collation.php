<?php

use yii\db\Migration;

class m170312_165920_firefly_firefly_fix_uuid_collation extends Migration
{
    public function safeUp()
    {
		$query = "ALTER TABLE `firefly_file_manager` CHANGE `uuid` `uuid` CHAR(22) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL COMMENT 'The unique identifier for this file'";
		$this->execute($query);
    }

    public function safeDown()
    {
		$query = "ALTER TABLE `firefly_file_manager` CHANGE `uuid` `uuid` CHAR(22) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'The unique identifier for this file'";
		$this->execute($query);
    }
}
