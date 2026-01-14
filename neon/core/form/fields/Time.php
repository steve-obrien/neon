<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 21/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\form\fields;

use neon\core\form\fields\Field;
use neon\core\grid\query\IQuery;
use neon\core\helpers\Arr;
use neon\core\helpers\Html;
use neon\core\helpers\Str;
use yii\validators\DateValidator;

/**
 * Class Time
 * @package neon\core\form\fields
 *
 * The time field maintains an internal representation of the time in the format 00:00:00 (hh:mm:ss php format of H:i:s)
 */
class Time extends Field
{
	/**
	 * The DDS data type to store the value of the field
	 * @var string
	 */
	public $ddsDataType = 'time';

	/**
	 * Whether to show a seconds input
	 * defaults to false meaning only hours and minutes will be collected and seconds will default to '00'
	 * @var bool
	 */
	public $showSeconds = false;

	/**
	 * Whether to show the time selector as an input or as drop down boxes
	 * Accepted values 'input' | 'select'
	 * @var string
	 */
	public $fieldType = 'input';

	/**
	 * Convert a time expressed as a string into an array containing 'hh', 'mm' and 'ss' keys for hours, minutes and seconds
	 * respectively
	 * If a unit (minutes or seconds) does not exist then it fills with '00'
	 * This will convert times expressed like `12` into `12:00:00`
	 *
	 * @param string $timeString
	 * @return array
	 */
	public static function timeStringToArray($timeString)
	{
		$bits = explode(':', $timeString);
		return [
			'hh' => Arr::get($bits,0, '00'),
			'mm' => Arr::get($bits,1, '00'),
			'ss' => Arr::get($bits,2, '00')
		];
	}

	/**
	 * Takes a time array with 'hh', 'mm' and 'ss' keys and converts it back into a string
	 * in the format of hh::mm:ss - note if a key for a unit does not exist it will fill with `00`
	 * @param array $timeArray
	 * @return string
	 */
	public static function timeArrayToString($timeArray)
	{
		$hh = (isset($timeArray['hh']) && !empty($timeArray['hh'])) ? $timeArray['hh'] : '00';
		$mm = (isset($timeArray['mm']) && !empty($timeArray['mm'])) ? $timeArray['mm'] : '00';
		$ss = (isset($timeArray['ss']) && !empty($timeArray['ss'])) ? $timeArray['ss'] : '00';
		return "$hh:$mm:$ss";
	}

	/**
	 * @inheritdoc
	 *
	 * The set value function accepts time as an array
	 * @param array|string|null - accepts time specified in multiple formats:
	 *
	 * - As an array: containing 'hh', 'mm' and 'ss' keys for hours, minutes and seconds
	 * - As a string in the format of '00:00:00
	 */
	public function setValue($value)
	{
		// set sensible defaults for the string if for example `12` is passed we want to convert this to `12:00:00`
		if (is_string($value)) {
			$value = static::timeStringToArray($value);
		}

		// can be an array with 'hh', 'mm', 'ss' key
		// the array is a special case allowing html fields more control for example a dedicated
		// input box for each segment (hours | minutes)
		if (is_array($value)) {
			$value = static::timeArrayToString($value);
		}

		// can be a string (or null) in the format '00:00:00' where seconds [] is optional
		// as we have validation rules we just set the value to the string received
		// the formatting will be checked later
		return parent::setValue($value);
	}

	/**
	 * @return string
	 */
	public function getValueDisplay($context='')
	{
		// don't show seconds if they are not to be set
		return neon()->formatter->asTime($this->getValue());
	}

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		// make sure the `$this->showSeconds` is saved and retrieved from the definition
		return array_merge(parent::getProperties(), ['showSeconds', 'fieldType']);
	}

	/**
	 * @inheritdoc
	 */
	public function processAsFilter(IQuery $query, $searchData=null)
	{
		$searchData = ($searchData === null) ? $this->getValue() : $searchData;
		if (!empty($searchData)) {
			$query->where($this->getDataKey(), '=', $searchData);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Time', 'icon' => 'fa fa-clock-o', 'group' => 'Date & Time', 'order' => 330
		];
	}
}