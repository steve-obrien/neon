<?php
/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 31/10/2018
 * Time: 10:33
 */

namespace neon\core\services;


use neon\core\helpers\Converter;

class Formatter extends \yii\i18n\Formatter
{
	/**
	 * @inheritdoc
	 */
	public $dateFormat = 'dd/MM/yyyy';

	/**
	 * @inheritdoc
	 */
	public $timeFormat = 'HH:mm';

	/**
	 * @inheritdoc
	 */
	public $nullDisplay = '-';

	/**
	 * @inheritdoc
	 */
	public $datetimeFormat = 'dd/MM/yyyy HH:mm';

	/**
	 * {@inheritdoc}
	 */
	public function init()
	{
		// The parent init sets timezone and language from main config
		parent::init();
		// the main config can set this - but it can also be loaded by application settings (and possibly user setting in future)
		$this->dateFormat = setting('settings', 'dateFormat', $this->dateFormat);
		if ($this->dateFormat === 'custom')
			$this->dateFormat = setting('settings', 'dateFormatCustom', $this->dateFormat);

		$this->timeFormat = setting('settings', 'timeFormat', $this->timeFormat);
		$this->nullDisplay = setting('settings', 'nullDisplay', $this->nullDisplay);
		$this->datetimeFormat = setting('settings', 'datetimeFormat', $this->datetimeFormat);
		if ($this->datetimeFormat === 'custom')
			$this->datetimeFormat = setting('settings', 'datetimeFormatCustom', $this->datetimeFormat);
	}

	/**
	 * Formats the value as Json.
	 * The value will be purified using [[HtmlPurifier]] to avoid XSS attacks.
	 * Use [[asRaw()]] if you do not want any purification of the value.
	 * @param string $value the value to be formatted.
	 * @param array|null $config the configuration for the HTMLPurifier class.
	 * @return string the formatted result.
	 */
	public function asJson($value, $config = null)
	{
		if ($value === null) {
			return $this->nullDisplay;
		}
		return 'Json';
	}
}