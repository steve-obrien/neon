<?php

use yii\db\Migration;

class m190923_114800_user_add_dev_role extends Migration
{
	public function safeUp()
	{
		// create the developer role
		$auth = neon()->authManager;
		$role = $auth->getRole('neon-developer');
		if (!$role) {
			$developer = $auth->createRole('neon-developer');
			$auth->add($developer);
			
			// apply it to all existing neon-administrators
			$query = "SELECT `user_id` FROM `user_auth_assignment` WHERE `item_name`='neon-administrator'";
			$users = neon()->db->createCommand($query)->queryAll();
			foreach ($users as $user) {
				$auth->assign($developer, $user['user_id']);
			}
		}
	}

	public function safeDown()
	{
		$auth = neon()->authManager;
		$developer = $auth->getRole('neon-developer');
		$auth->remove($developer);
	}
}
