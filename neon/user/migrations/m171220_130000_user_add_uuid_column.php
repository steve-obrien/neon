<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

use \neon\user\models\User;

/**
 * Initializes the User app tables
 */
class m171220_130000_user_add_uuid_column extends \yii\db\Migration
{
	public function safeUp()
	{
		$this->addColumn(User::tableName(), 'uuid', 'char(22) AFTER `id`');
		// fetch 50 users at a time and iterate them one by one
		// save call will automatically create the uuid if it currently does not exist
		foreach (User::find()->each(50) as $user)
			$user->save(false, [User::UUID]);
		// create a unique index
		$this->createIndex('uuid', User::tableName(), 'uuid', true);
	}

	public function safeDown()
	{
		$this->dropColumn(User::tableName(), 'uuid');
	}
}
