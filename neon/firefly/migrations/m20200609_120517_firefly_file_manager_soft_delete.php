<?php

use neon\core\db\Migration;
use \neon\firefly\services\fileManager\models\FileManager;

class m20200609_120517_firefly_file_manager_soft_delete extends Migration
{
	public function safeUp()
	{
		$this->dropColumn(FileManager::tableName(), 'deleted_at');
		$this->addColumn(FileManager::tableName(), 'deleted', $this->softDelete());
	}

	public function safeDown()
	{
		// create a down migration
		$this->addColumn(FileManager::tableName(), 'deleted_at', $this->dateTime()->defaultValue(null)->comment('When the record was deleted'));
		$this->dropColumn(FileManager::tableName(), 'deleted');
	}
}
