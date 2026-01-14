<?php

use yii\db\Migration;
use neon\settings\services\settingsManager\models\Setting;
use \yii\helpers\Json;

class m181127_165817_settings_update_settings_to_json extends Migration
{
	public function safeUp()
	{
		// run through all of the settings and see if they can be decoded by
		// the new Settings manager decode method copied below. If it cannot be
		// then json encode and save and check again
		$settings = Setting::find()->all();
		$success = true;
		foreach ($settings as $setting) {
			try {
				if ($setting->value != null)
					$setting->value = $this->_decode($setting->value);
			} catch (\Exception $e) {
				dp("Error while decoding setting from json: {$setting->app} {$setting->key} {$setting->value}. Error is ".$e->getMessage());
				dp('Trying to save it back as Json');
				// try and save it
				try {
					$setting->value = $this->_encode($setting->value);
					$setting->save();
					dp('Saved it successfully');
				} catch (\Exception $e) {
					dp("Error while updating setting to json: {$setting->app} {$setting->key} {$setting->value}. Error is ".$e->getMessage());
					$success = false;
				}
			}
		}
		return $success;
	}

	public function safeDown()
	{
		// not really possible to do a down migration here so just succeed
		return true;
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

}
