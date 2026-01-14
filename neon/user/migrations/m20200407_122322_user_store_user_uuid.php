<?php

use neon\core\db\Migration;

class m20200407_122322_user_store_user_uuid extends Migration
{
	public function safeUp()
	{
		// remove user integer based id (user integer ids are being deprecated throughout the system)
		$this->dropColumn(\neon\user\models\Session::tableName(), 'user_id');
		// remove duplicate field - in datetime but not used - can cope with the 'expire' field timestamp
		$this->dropColumn(\neon\user\models\Session::tableName(), 'expires');
		// add reference to the user uuid - enabling sessions to be tied to a user record
		// the key fields in this table are responsible for the session
		// this is the session id, expire and data fields.  After that the next important field is the user_uuid
		// then the following additional information fields
		$this->addColumn(\neon\user\models\Session::tableName(), 'user_uuid', $this->uuid64()  .' AFTER `data`');
	}

	public function safeDown()
	{
		// create a down migration
		$this->dropColumn(\neon\user\models\Session::tableName(), 'user_uuid');
		// will all be null
		$this->addColumn(\neon\user\models\Session::tableName(), 'user_id', $this->integer(11));
		// will all be null
		$this->addColumn(\neon\user\models\Session::tableName(), 'expires', $this->dateTime());
	}
}
