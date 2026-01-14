<?php

namespace neon\user\models;

use neon\core\db\ActiveRecord;

/**
 */
class Session extends ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%user_session}}';
	}
}
