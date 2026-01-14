<?php

use yii\db\Migration;

class m161202_111327_dds_set_member_creation_time extends Migration
{
	public function safeUp()
	{
		$this->addColumn('dds_member', 'created', "DATETIME NOT NULL COMMENT 'When the member was first added' AFTER `map_field`");
		$this->addColumn('dds_member', 'updated', "TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'When the member was last updated' AFTER `created`");
	}

	public function safeDown()
	{
		$this->dropColumn('dds_member', 'created');
		$this->dropColumn('dds_member', 'updated');
	}
}
