<?php

namespace neon\settings;
use neon\core\helpers\Converter;

/**
 * The neon core settings app class
 *
 * @property \neon\settings\services\SettingsManager  $settingsManager
 * @property \neon\settings\services\SettingsManager  $manager
 */
class App extends \neon\core\BaseApp
{
	/**
	 * @inheritdoc
	 */
	public function configure()
	{
		$this->set('settingsManager', [
			'class' => 'neon\settings\services\SettingsManager',
		]);
	}

	/**
	 * Get all registered settings for each neon app
     * ??? This should be in the settings manager ???
     *
	 * @return array
	 */
	public function getAppSettings()
	{
		$ret = [];
		foreach(neon()->getActiveApps() as $key => $app) {
			if (!empty($app->getSettings())) {
				$ret[$key] = $app->getSettings();
			}
		}
		return $ret;
	}

	public function getMenu()
	{
		return [
			'label' => 'Settings',
			'order' => 1500,
			'url' => ['/settings/index/index'],
			'visible' => neon()->user->is('neon-administrator'),
			'active' => is_route('/settings/*'),
		];
	}

	/**
	 * Get the settings manager
	 * @return \neon\settings\services\SettingsManager
	 */
	public function getSettingsManager()
	{
		return $this->settingsManager;
	}

	/**
	 * Get the settings manager
	 * @return \neon\settings\services\SettingsManager
	 */
	public function getManager()
	{
		return $this->settingsManager;
	}

	/**
	 * @inheritdoc
	 */
	public function getSettings()
	{
		$exampleDate = new \DateTime();
		return [
			'timeFormat' => [
				'class' => 'neon\\core\\form\\fields\\Select',
				'label' => 'Time format',
				'value' => neon()->formatter->timeFormat,
				'items' => [
					'hh:mm a' => $this->_itemLineTime('hh:mm a', $exampleDate),
					'hh:mm:ss a' => $this->_itemLineTime('hh:mm:ss a', $exampleDate),
					'HH:mm' => $this->_itemLineTime('HH:mm', $exampleDate),
					'HH:mm:ss' => $this->_itemLineTime('HH:mm:ss', $exampleDate),
				]
			],

			'dateFormat' => [
				'class' => 'neon\\core\\form\\fields\\Select',
				'label' => 'Date format',
				'allowClear' => false,
				'value' => neon()->formatter->dateFormat,
				'items' => [
					//'Short' => [
						'dd/MM/yy' => $this->_itemLine('dd/MM/yy', $exampleDate),
						'MM/dd/yy' => $this->_itemLine('MM/dd/yy', $exampleDate),
						'yy/MM/dd' => $this->_itemLine('yy/MM/dd', $exampleDate),
					//],
					//'Medium' => [
						'dd/MM/yyyy' => $this->_itemLine('dd/MM/yyyy', $exampleDate),
						'MM/dd/yyyy' => $this->_itemLine('MM/dd/yyyy', $exampleDate),
						'yyyy/MM/dd' => $this->_itemLine('yyyy/MM/dd', $exampleDate),
					//],
					//'Long' => [
						'dd MMM yyyy' => $this->_itemLine('dd MMM yyyy', $exampleDate),
						'dd MMMM yyyy' => $this->_itemLine('dd MMMM yyyy', $exampleDate),
						'MMMM dd, yyyy' => $this->_itemLine('MMMM dd, yyyy', $exampleDate),
						'MMM dd, yyyy' => $this->_itemLine('MMM dd, yyyy', $exampleDate),
						'yyyy MM dd' => $this->_itemLine('yyyy MM dd', $exampleDate),
					//],
					//'Custom' => [
						'custom' => 'Create a custom format...'
					//]
				]
			],
			'dateFormatCustom' => [
				'class' => 'neon\\core\\form\\fields\\Text',
				'label' => 'Date Custom format',
				'hint' => 'Can be defined as http://userguide.icu-project.org/formatparse/datetime, you can also use PHP format by prefixing with `php:`',
				'showIf' => ["dateFormat", "=", "custom"]
			],

			'datetimeFormat' => [
				'class' => 'neon\\core\\form\\fields\\Select',
				'label' => 'Datetime format',
				'allowClear' => false,
				'value' => neon()->formatter->datetimeFormat,
				'items' => [
					// 12 hour formats
					'dd/MM/yy hh:mm a' => $this->_itemLineDatetime('dd/MM/yy hh:mm a', $exampleDate),
					'MM/dd/yy hh:mm a' => $this->_itemLineDatetime('MM/dd/yy hh:mm a', $exampleDate),
					'dd/MM/yyyy hh:mm a' => $this->_itemLineDatetime('dd/MM/yyyy hh:mm a', $exampleDate),
					'MM/dd/yyyy hh:mm a' => $this->_itemLineDatetime('MM/dd/yyyy hh:mm a', $exampleDate),
					'dd MMM yyyy hh:mm a' => $this->_itemLineDatetime('dd MMM yyyy hh:mm a', $exampleDate),
					'dd MMMM yyyy hh:mm a' => $this->_itemLineDatetime('dd MMMM yyyy hh:mm a', $exampleDate),
					'MMMM dd, yyyy hh:mm a' => $this->_itemLineDatetime('MMMM dd, yyyy hh:mm a', $exampleDate),
					'MMM dd, yyyy hh:mm a' => $this->_itemLineDatetime('MMM dd, yyyy hh:mm a', $exampleDate),
					'yyyy-MM-dd hh:mm a' => $this->_itemLineDatetime('yyyy-MM-dd hh:mm a', $exampleDate),

					// 24 hour formats
					'dd/MM/yy HH:mm' => $this->_itemLineDatetime('dd/MM/yy HH:mm', $exampleDate),
					'MM/dd/yy HH:mm' => $this->_itemLineDatetime('MM/dd/yy HH:mm', $exampleDate),
					'dd/MM/yyyy HH:mm' => $this->_itemLineDatetime('dd/MM/yyyy HH:mm', $exampleDate),
					'MM/dd/yyyy HH:mm' => $this->_itemLineDatetime('MM/dd/yyyy HH:mm', $exampleDate),
					'dd MMM yyyy HH:mm' => $this->_itemLineDatetime('dd MMM yyyy HH:mm', $exampleDate),
					'dd MMMM yyyy HH:mm' => $this->_itemLineDatetime('dd MMMM yyyy HH:mm', $exampleDate),
					'MMMM dd, yyyy HH:mm' => $this->_itemLineDatetime('MMMM dd, yyyy HH:mm', $exampleDate),
					'MMM dd, yyyy HH:mm' => $this->_itemLineDatetime('MMM dd, yyyy HH:mm', $exampleDate),
					'yyyy-MM-dd HH:mm' => $this->_itemLineDatetime('yyyy-MM-dd HH:mm', $exampleDate),

					//'Custom' => [
					'custom' => 'Create a custom format...'
					//]
				]
			],
			'datetimeFormatCustom' => [
				'class' => 'neon\\core\\form\\fields\\Text',
				'label' => 'Datetime Custom format',
				'hint' => 'Can be defined as http://userguide.icu-project.org/formatparse/datetime, you can also use PHP format by prefixing with `php:`',
				'showIf' => ["datetimeFormat", "=", "custom"]
			],
			'nullDisplay' => [
				'class' => 'neon\\core\\form\\fields\\Select',
				'label' => 'Empty display',
				'hint' => 'What to show when no value exists',
				'value' => neon()->formatter->nullDisplay,
 				'items' => [
					' ' => ' ',
					'-' => '-',
					'<span class="notSet">(not set)</span>' => '(not set)',
				]
			]
		];
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Preferences';
	}

	private function _itemLineDatetime($dateFormat, $exampleDate)
	{
		return neon()->formatter->asDatetime($exampleDate, $dateFormat) . "  -  $dateFormat";
	}

	private function _itemLine($dateFormat, $exampleDate)
	{
		return neon()->formatter->asDate($exampleDate, $dateFormat) . "  -  $dateFormat";
	}

	private function _itemLineTime($dateFormat, $exampleDate)
	{
		return neon()->formatter->asTime($exampleDate, $dateFormat) . "  -  $dateFormat";
	}
}
