<?php

use yii\db\Migration;

class m170104_161200_use_baseclass_timestamps extends Migration
{

    public function safeUp()
    {
		$table = \neon\user\models\User::tableName();
		$this->renameColumn($table, 'created_on', 'created_at');
		$this->renameColumn($table, 'updated_on', 'updated_at');
    }

    public function safeDown()
    {
		$table = \neon\user\models\User::tableName();
		$this->renameColumn($table, 'created_at', 'created_on');
		$this->renameColumn($table, 'updated_at', 'updated_on');
    }
}
