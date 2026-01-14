<?php

use neon\core\db\Migration;
use \neon\user\models\UserInvite;

class m181128_143758_user_create_invite_table extends Migration
{
	public function safeUp()
	{
	    $this->createTable(UserInvite::tableName(), [
		    'token'=>'varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs PRIMARY KEY COMMENT "The token"',
		    'user_id'=> $this->uuid64( 'foreign key of the user who owns this token')->notNull(),
		    // timestamps
		    'expires_at' => $this->dateTime(),
		    'created_at' => $this->createdAt(),
		    'updated_at' => $this->updatedAt(),
		    // blames
		    'created_by' => $this->createdBy(),
		    'updated_by' => $this->updatedBy()
	    ]);
	}

	public function safeDown()
	{
		$this->dropTable(UserInvite::tableName());
	}
}
