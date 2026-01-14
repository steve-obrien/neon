<?php

use yii\db\Migration;

class m171214_150436_user_add_admin_role extends Migration
{
	public function safeUp()
	{
		// create the administrator role
		$auth = neon()->authManager;
		$role = $auth->getRole('neon-administrator');
		if (!$role) {
			$admin = $auth->createRole('neon-administrator');
			$auth->add($admin);

			// apply it to all existing users who should all be admin
			$query = "SELECT `id` FROM `user_user`";
			$users = neon()->db->createCommand($query)->queryAll();
			foreach ($users as $user) {
				$auth->assign($admin, $user['id']);
			}
		}
	}

	public function safeDown()
	{
		$auth = neon()->authManager;
        $auth->removeAll();
	}

}