<?php

use yii\db\Migration;

class m161026_200631__dds_object_table_namespace_fields extends Migration
{
	public function safeUp()
	{
		$this->dropForeignKey('dds_object_class_type', 'dds_object');
		$this->renameColumn('dds_object', 'id', '_id');
		$this->renameColumn('dds_object', 'class_type', '_class_type');
		$this->renameColumn('dds_object', 'deleted', '_deleted');
		$this->renameColumn('dds_object', 'created', '_created');
		$this->renameColumn('dds_object', 'updated', '_updated');
		$this->addForeignKey('dds_object_class_type', 'dds_object', '_class_type', 'dds_class', 'class_type', null, 'CASCADE');
		$this->dropColumn('dds_object', 'label');
	}

	public function safeDown()
	{
		$this->dropForeignKey('dds_object_class_type', 'dds_object');
		$this->renameColumn('dds_object', '_id', 'id');
		$this->renameColumn('dds_object', '_class_type', 'class_type');
		$this->renameColumn('dds_object', '_deleted', 'deleted');
		$this->renameColumn('dds_object', '_created', 'created');
		$this->renameColumn('dds_object', '_updated', 'updated');
		$this->addForeignKey('dds_object_class_type', 'dds_object', 'class_type', 'dds_class', 'class_type', null, 'CASCADE');
		$this->addColumn('dds_object', 'label', 'varchar(300)');
	}
}