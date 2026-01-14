<?php

use neon\firefly\services\driveManager\models\DbFile;
use yii\db\Migration;

class m161123_191412_firefly_files_table extends Migration
{
    public function safeUp()
    {
		$this->createTable(DbFile::tableName(), [
			// limit the path size on the db file storage driver.
			// otherwise the key gets too big.
			// Microsoft generally only support paths of 260 characters
			// the database driver is not really intended for complex directory hierarchies
			'path' => $this->string(255)->notNull()->comment('The path to the file - also the primary key'),
			/**
			 * Storing blobs of data in Mysql
			 *
			 * - `BLOB` up to 65535 bytes (64 KB) maximum.
			 * - `MEDIUMBLOB` up to 16777215 bytes (16 MB).
			 * - `LONGBLOB` up to  4294967295 bytes (4 GB).
			 *
			 * We have set this to a LONGBLOB but it's probably silly to store files this big in the database.
			 * A `MEDIUMBLOB` sounds like a nice compromise - the file manager could determine based on size whether they
			 * can be stored in the database or not.
			 */
			'contents' => 'LONGBLOB COMMENT "The file contents"',
			'visibility' => "ENUM ('".DbFile::VISIBILITY_PRIVATE."', '".DbFile::VISIBILITY_PUBLIC."') DEFAULT '".DbFile::VISIBILITY_PUBLIC."' COMMENT 'Whether this file is visible true if visible false if not'",
			'mime_type' => $this->string()->comment('The mimetype of the file'),
			'size' => $this->integer(11)->comment('The size of the file in bytes'),
			'type' => "ENUM ('".DbFile::TYPE_FILE."', '".DbFile::TYPE_DIR."') COMMENT 'The type of file - either a `dir` or `file`'",
			// created_at
			// this is really updated_at
			'updated_at' => $this->timestamp()->comment('When the file was last modified'),
			'created_at' => $this->datetime()->comment('When the file was first created'),
			'PRIMARY KEY (`path`(150))'
		]);
    }

    public function safeDown()
    {
		$this->dropTable(DbFile::tableName());
    }

}
