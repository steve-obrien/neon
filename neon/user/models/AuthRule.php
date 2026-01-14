<?php

namespace neon\user\models;

use neon\core\db\ActiveRecord;

/**
 */
class AuthRule extends ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return \Neon::$app->authManager->ruleTable;
	}
}
