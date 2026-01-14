<?php

use yii\db\Migration;

class m170104_140219_user_change_text_to_varchar extends Migration
{

	public function safeUp()
	{
		$this->alterColumn('user_session', 'user_agent', "VARCHAR(2000) COMMENT 'The users user agent - OS and browser as reported by the HTTP user agent string'");
		$this->alterColumn('user_session', 'referrer', "VARCHAR(2000) COMMENT 'The referring address - HTTP referrer headerg'");
	}

	public function safeDown()
	{
		$this->alterColumn('user_session', 'user_agent', "TEXT COMMENT 'The users user agent - OS and browser as reported by the HTTP user agent string'");
		$this->alterColumn('user_session', 'referrer', "TEXT COMMENT 'The referring address - HTTP referrer headerg'");
	}

}
