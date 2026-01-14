<?php

use yii\db\Migration;

class m161211_160816_user_correct_current_field extends Migration
{

    public function safeUp()
    {
		/**
		 * fix an incorrect definition of current in the code and make the name more meaningful
		 */
		$table = \neon\user\models\Session::tableName();
		$this->dropColumn($table, 'current');
		$this->addColumn($table, 'current_url', $this->string(1000)->comment('The current url the user requested in this session'));
    }

    public function safeDown()
    {
		/**
		 * revert back to the broken definition
		 */
		$table = \neon\user\models\Session::tableName();
		$this->dropColumn($table, 'current_url');
		$this->addColumn($table, 'current', $this->dateTime()->comment('The current url the user requested in this session'));
    }
}
