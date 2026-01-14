<?php

use yii\db\Schema;
use yii\db\Migration;
use \neon\settings\models\Setting;

class m160124_004328_settings_init extends Migration
{
	/**
	 * @inheritdoc
	 */
	public function up()
	{
		$this->createTable(Setting::tableName(), [
			'app'=>'string COMMENT "String key name of the app the setting belongs to e.g. \'core\', `\'user\' etc"',
			'key'=>'string',
			'value'=>'text',
			'PRIMARY KEY (`app`(100), `key`(100))'
		]);
		$this->createIndex('key', Setting::tableName(), '`key`(100)');
	}

	/**
	 * @inheritdoc
	 */
	public function down()
	{
		$this->dropTable(Setting::tableName());
		return true;
	}

	/*
	// Use safeUp/safeDown to run migration code within a transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}
