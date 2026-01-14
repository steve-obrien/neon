<?php

use \neon\core\db\Migration;

use \neon\firefly\services\mediaManager\models\Media;
use \neon\firefly\services\fileManager\models\FileManager;

class m170510_firefly_media extends Migration
{
	public function safeUp()
	{
		$sql = "DROP TABLE IF EXISTS firefly_media";
		\Yii::$app->getDb()->createCommand($sql)->execute();

		$this->createTable('{{%firefly_media}}', [
			'id' => $this->uuid64()->notNull(),

			// DB - Hierarchy fields
			'parent_id' => $this->uuid64(),

			'path' => $this->string(10000), // TO BE DELETED - somehow
			'type' => $this->string(4), // Should be an enum

			'deleted' => "tinyint(1) DEFAULT 0 COMMENT 'Row has been deleted if 1'",

			'PRIMARY KEY (id)'
		]);
		// create the root node
		$this->insert('{{%firefly_media}}', ['id' => \neon\core\helpers\Hash::uuid64(), 'parent_id' => null, 'path' => '/', 'type' => 'root']);
	}

	public function safeDown()
	{
		$this->dropTable('{{%firefly_media}}');
	}
}
