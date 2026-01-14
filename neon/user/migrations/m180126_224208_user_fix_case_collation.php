<?php

use yii\db\Migration;

/**
 * The user_user uuid, password_hash, auth_key and password_reset_tokens have
 * the wrong collation. These should all be case sensitive and the uuid should
 * match daedalus' definition so it can be joined on it. This is latin1_general_cs.
 */
class m180126_224208_user_fix_case_collation extends Migration
{
	public function safeUp()
	{
		$this->alterColumn('user_user', 'uuid', "CHAR(22) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL COMMENT 'A universally unique identifier for this user'");
		$this->alterColumn('user_user', 'password_hash', "VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_general_cs NULL");
		$this->alterColumn('user_user', 'auth_key', "VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_general_cs NULL");
		$this->alterColumn('user_user', 'password_reset_token', "VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_general_cs NULL");
	}

	public function safeDown()
	{
		$this->alterColumn('user_user', 'uuid', "CHAR(22) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");
		$this->alterColumn('user_user', 'password_hash', "VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL");
		$this->alterColumn('user_user', 'auth_key', "VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL");
		$this->alterColumn('user_user', 'password_reset_token', "VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL");
	}
}
