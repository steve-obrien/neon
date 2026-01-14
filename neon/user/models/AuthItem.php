<?php

namespace neon\user\models;

use neon\core\db\ActiveRecord;

/**
 * @property integer $id
 * @property integer $user_id
 * @property string $source
 * @property string $source_id
 */
class AuthItem extends ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return neon()->authManager->itemTable;
	}
}
