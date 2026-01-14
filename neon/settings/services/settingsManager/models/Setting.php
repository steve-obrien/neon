<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2008 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\settings\services\settingsManager\models;

use neon\core\db\ActiveRecord;

/**
 * The Setting ActiveRecord Class
 * Stores simple key value pairs against an app id
 * Has a joint primary key of app and name
 * 
 * @property string $app the string app id e.g. user, admin, etc
 * @property string $name the string name key of the setting property
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
	 * @param string $app the app id
	 * @param string $key the setting key string
	 * @param string $value the value of the setting	 
	 * @return  bool
	 */
	public static function set($app, $key, $value)
	{
		$setting = static::findOne(['app' => $app, 'key' => $key]);
		if ($setting === null) {
			$setting = new Setting();
		}
		$setting->app = $app;
		$setting->key = $key;
		$setting->value = $value;
		return $setting->save();
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
