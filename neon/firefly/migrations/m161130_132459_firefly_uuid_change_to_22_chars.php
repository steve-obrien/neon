<?php

use yii\db\Migration;

class m161130_132459_firefly_uuid_change_to_22_chars extends Migration
{
    public function safeUp()
    {
		$this->alterColumn('firefly_file_manager', 'uuid', "CHAR(22) NOT NULL COMMENT 'The unique identifier for this file'");
    }

    public function safeDown()
    {
		$this->alterColumn('firefly_file_manager', 'uuid', "CHAR(36) NOT NULL COMMENT 'The uuid for this file'");
    }
}
