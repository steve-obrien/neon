<?php

use yii\db\Migration;

class m161001_140538__cms_builder extends Migration
{
	public function up()
	{
		$this->createTable('{{%cms_builder}}', [
			'id' => 'pk',
			'definition' => 'text COMMENT "Json definition of the form"'
		]);
	}

	/**
	 * @nheritdoc
	 */
	public function down()
	{
		$this->dropTable('{{%cms_builder}}');
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
