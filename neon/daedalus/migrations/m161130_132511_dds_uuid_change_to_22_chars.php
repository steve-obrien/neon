<?php

use yii\db\Migration;

class m161130_132511_dds_uuid_change_to_22_chars extends Migration
{
    public function safeUp()
    {
		$this->alterColumn('dds_object', '_uuid', "CHAR(22) NOT NULL COMMENT 'A universally unique identifier for this object'");
    }

    public function safeDown()
    {
		$this->alterColumn('dds_object', '_uuid', "CHAR(36) NOT NULL");
    }
}
