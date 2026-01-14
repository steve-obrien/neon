<?php

namespace neon\user\models;

use neon\core\db\ActiveRecord;

/**
 * User model
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $source
 * @property string $source_id
 */
class AuthClient extends ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%user_client}}';
	}
}
