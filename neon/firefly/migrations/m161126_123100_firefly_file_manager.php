<?php

use \neon\firefly\services\fileManager\models\FileManager;
use neon\core\db\Migration;

class m161126_123100_firefly_file_manager extends Migration
{

    public function safeUp()
    {
		$this->createTable(FileManager::tableName(), [

			'uuid' => $this->char(36)->notNull()->comment('The uuid for this file'),

			'name' => $this->string(100)->comment('An optional name for the file'),
			'file_hash' => $this->string(32)->comment('An MD5 hash of the files contents'),

			'path' => $this->string(500)->comment('The full path to the file on the drive'),
			'drive' => $this->string(50)->comment('The drive configured to store this file e.g. typical default values "s3", "db", "local"'),

			'mime_type' => $this->string()->comment('The mimetype of the file'),
			'size' => $this->integer(11)->comment('The size of the file in bytes'),

			'downloaded' => $this->integer(11)->defaultValue(0)->comment('Count showing the number of downloads'),
			'viewed' => $this->integer(11)->defaultValue(0)->comment('Count showing the number of downloads'),

			'updated_by' => $this->integer(11)->comment('The user id that updated the file - null if guest'),
			'updated_at' => $this->timestamp()->comment('When the record was last updated'),

			'created_by' => $this->integer(11)->comment('The user id that created the file - null if guest'),
			'created_at' => $this->dateTime()->comment('When the record was first created'),

			'deleted_at' => $this->dateTime()->defaultValue(null)->comment('When the record was deleted'),

			'PRIMARY KEY (uuid)'

		]);
    }

    public function safeDown()
    {
		$this->dropTable(FileManager::tableName());
    }

}
