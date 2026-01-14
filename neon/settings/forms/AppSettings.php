<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2008 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\settings\forms;

use \neon\core\form\Form;
use neon\core\helpers\Str;

/**
 * Class AppSettings
 */
class AppSettings extends Form
{
	/**
	 * Auto config init options
	 */
	public function init()
	{
		$this->setName('setting');
		$this->loadFields();
	}

	/**
	 * @return \neon\settings\interfaces\iSettingsManager
	 */
	public function getSettingsManager()
	{
		return neon()->settingsManager;
	}

	/**
	 *
	 */
	public function process()
	{
		if ($this->shouldProcess()) {
			$this->load();
			$this->validate();
			if ($this->hasError()) {
				neon()->session->setFlash('error', 'The form has errors.');
			} else {
				$this->saveToSettings();
				neon()->session->setFlash('success', 'Settings have been successfully saved.');
			}
		} else {
			$this->loadFieldValuesFromSettings();
		}
	}

	/**
	 * This function saves form data to the settings manager.
	 *
	 * @return boolean
	 * - true on successful save of settings
	 * - false if any setting save failed or no settings were saved
	 */
	public function saveToSettings()
	{
		$saved = false;
		foreach ($this->getData() as $appKey => $appSettings) {
			$appForm = $this->getSubForm($appKey);
			foreach ($appSettings as $key => $value) {
				// if the fields value is valid then save it
				if ($appForm->getField($key)->validate()) {
				    $saved = $this->getSettingsManager()->set($appKey, $key, $value);
				}
			}   
		}
		return $saved;
	}

	/**
	 * Get a form representing all settings for each registered app
	 *
	 * @return \neon\core\form\Form
	 */
	public function loadFields()
	{
		$settings = neon()->settings->getAppSettings();
		foreach($settings as $app => $config) {
			if (is_array($config)) {
				$this->addSubForm($app)->setFields($config)->setLabel(neon($app)->name);
			}
		}
	}

	/**
	 * Load the form field values based on the current setting values stored.
	 * @return void
	 */
	public function loadFieldValuesFromSettings()
	{
		$settingValues = $this->getSettingsManager()->getAll(true);
		$this->load($settingValues);
	}
}
