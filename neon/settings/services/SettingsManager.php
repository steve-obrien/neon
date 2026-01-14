<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2008 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\settings\services;

use yii\base\Component;
use neon\settings\models\Setting;
use \yii\helpers\Json;
use neon\settings\interfaces\ISettingsManager;

/**
 * Component to manage settings for apps
 *
 * @author Steve O'Brien
 * @since 1.2.3
 */
class SettingsManager extends Component implements ISettingsManager
{
	/**
	 * @var array per session cache of the settings
	 * stored in the format:
	 *
	 * ~~~php
	 * [
	 *	 // general format:
	 *	 'app_id' => [
	 *		 'setting_name' => 'setting_value'
	 *	 ],
	 *	 // realistic example:
	 *	 'admin' => [
	 *		 'fromEmailAddress' => 'hello@newicon.net',
	 *		 'fromEmailName' => 'Newicon',
	 *	 ]
	 * ]
	 * ~~~
	 */
	protected $_settings;

	/**
	 * Get a setting value
	 *
	 * @param string $app the string id of the app responsible for this setting
	 * @param string $key the name key of the setting
	 * @param mixed $default the default value to return if the settings does not exist defaults to null
	 * @param boolean $refresh whether to refresh the cache before fetching this setting defaults to false
	 * @return string
	 */
	public function get($app, $key, $default=null, $refresh=false)
	{
		$settings = $this->getSettings($refresh);
		return isset($settings[$app][$key]) && $settings[$app][$key] !== '' ? $settings[$app][$key] : $default;
	}

	/**
	 * Set a setting value
	 *
	 * @param string $app the string id of the app responsible for this setting
	 * @param string $key the name key of the setting
	 * @param mixed $value the value of the setting
	 * @throws \Exception if the setting has errors that prevent saving
	 * @return boolean success or fail
	 */
	public function set($app, $key, $value)
	{
		$setting = Setting::findOne(['app' => $app, 'key' => $key]);
		if ($setting === null) {
			$setting = new Setting();
		}

		$setting->app = $app;
		$setting->key = $key;
		$setting->value = $this->_encode($value);
		$result = $setting->save();

		if (!$result) {
			throw new \Exception('Unable to save setting. ' . print_r($setting->getErrors(), true));
		}
		neon()->cache->delete('neon_settings');
		$this->_settings[$app][$key] = $value;
		return $result;
	}

	/**
	 * Load the settings from the database and cache for the session
	 *
	 * @param boolean $refresh if true forces a reload of the the settings
	 * @return array in the format @see self::$_settings
	 */
	public function getAll($refresh=false)
	{
		if ($refresh) neon()->cache->delete('neon_settings');
		return $this->_settings = neon()->cache->getOrSet('neon_settings', function() {
			$settingRows = Setting::find()->asArray()->all();
			$this->_settings = [];
			foreach($settingRows as $setting) {
				$this->_settings[$setting['app']][$setting['key']] = $this->_decode($setting['value']);
			}
			return $this->_settings;
		});
	}

	/**
	 * Load the settings from the database and cache for the session
	 *
	 * @param boolean $refresh if true forces a reload of the the settings
	 * @return array in the format @see self::$_settings
	 */
	public function getSettings($refresh=false)
	{
		return $this->getAll($refresh);
	}

	/**
	 * @inheritdoc
	 */
	public function toArray(array $fields = [], array $expand = [], $recursive = true)
	{
		return $this->getAll();
	}

	/**
	 * @return string json representation of the settings
	 */
	public function toJson()
	{
		return $this->_encode($this);
	}

	/**
	 * @inheritdoc
	 * // we could just add :mixed here
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize(): mixed
	{
		return $this->toArray();
	}

	/**
	 * Encodes data to string
	 *
	 * @param mixed $data
	 * @return string
	 */
	protected function _encode($data)
	{
		return Json::encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT);
	}

	/**
	 * decodes data as an array
	 *
	 * @param string $data
	 * @return array
	 */
	protected function _decode($data)
	{
		return Json::decode($data, true);
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->toJson();
	}
}
