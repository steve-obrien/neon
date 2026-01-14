<?php

use neon\core\db\Migration;

class m20230411_193800_add_indexs_to_object_table extends Migration
{
	public function safeUp()
	{
		$this->createIndex('created', 'dds_object', '_created');
		$this->createIndex('updated', 'dds_object', '_updated');
	}

	public function safeDown()
	{
		$this->dropIndex('created', 'dds_object');
		$this->dropIndex('updated', 'dds_object');
	}
}
