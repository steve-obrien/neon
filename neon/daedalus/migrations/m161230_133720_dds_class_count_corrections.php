<?php

use yii\db\Migration;

class m161230_133720_dds_class_count_corrections extends Migration
{
	public function safeUp()
	{
		$this->renameColumn('dds_class', 'object_count', 'count_total');
		$this->renameColumn('dds_class', 'object_deleted_count', 'count_deleted');
	}

	public function safeDown()
	{
		$this->renameColumn('dds_class', 'count_total', 'object_count');
		$this->renameColumn('dds_class', 'count_deleted', 'object_deleted_count');
	}
}
