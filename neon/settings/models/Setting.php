<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2008 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\settings\models;

use League\Flysystem\Exception;
use neon\core\db\ActiveRecord;

/**
 * The Setting ActiveRecord Class
 * Stores simple key value pairs against an app id
 * Has a joint primary key of app and name
 * 
 * @property string $app the string app id e.g. user, admin, etc
 * @property string $key the string name key of the setting property
 * @property string $value the settings value
 */
class Setting extends ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%settings_setting}}';
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributes()
	{
		return [
			'app',
			'key',
			'value'
		];
	}
}
