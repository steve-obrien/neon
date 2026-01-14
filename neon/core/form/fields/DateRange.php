<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 21/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\form\fields;

use \neon\core\grid\query\IQuery;
use \neon\core\form\fields\Date;
use \neon\core\helpers\Arr;
use \neon\core\form\Form;
use neon\core\helpers\Html;
use neon\core\helpers\Converter;
use \neon\core\assets\JQueryUiAsset;
use neon\core\validators\DateValidator;

/**
 * Class DateRange
 * @package neon\core\form\fields
 */
class DateRange extends Field
{
	/**
	 * The DDS data type to store the value of the field
	 * @var string
	 */
	public $ddsDataType = 'json';

	/**
	 * The format the date should appear to the user - this also sets the date picker format
	 * http://php.net/manual/en/function.date.php
	 * @var string
	 */
	public $_dateUserFormat = 'php:d/m/Y';

	/**
	 * The placeholder to show to the user when filtering
	 * @var string
	 */
	public $placeholder = 'DD/MM/YYYY';

	/**
	 * The range of years to offer as a selection
	 * @var string
	 */
	public $yearRange = 'c-10:c+10';

	/**
	 * @var string
	 */
	protected $dateDatabaseFormat = 'php:Y-m-d';

	/**
	 * @param $date
	 */
	public function setDateUserFormat($date)
	{
	}

	/**
	 * Get the date format that should be displayed to the user
	 * @return string - the date format
	 * @see \neon\core\services\Formatter::$dateFormat
	 */
	public function getDateUserFormat()
	{
		return neon()->formatter->dateFormat;
	}

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		return array_merge(parent::getProperties(), ['datePickerFormat', 'yearRange']);
	}

	/**
	 * Returns a Jquery ui date format string by converting the dateUserFormat property which is a PHP date format string
	 * http://api.jqueryui.com/datepicker/#utility-formatDate
	 * @return string
	 */
	public function getDatePickerFormat()
	{
		return Converter::convertDateFormatToJui($this->dateUserFormat);
	}

	/**
	 * @inheritdoc
	 */
	public function processAsFilter(IQuery $query, $searchData=null)
	{
		$searchData = ($searchData === null) ? $this->getData() : $searchData;
		if ($searchData !== '') {
			$dateFrom = Arr::get($searchData, 'from');
			$dateTo   = Arr::get($searchData, 'to');
			if ($dateFrom)
				$query->where($this->getDataKey(), '>=', $dateFrom);
			if ($dateTo)
				$query->where($this->getDataKey(), '<=', $dateTo);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function registerScripts($view)
	{
		JQueryUiAsset::register($view);
	}

	/**
	 * @inheritDoc
	 */
	public function setValueFromDb($value)
	{
		$this->_value = json_decode($value);
	}

	/**
	 * @inheritdoc
	 */
	public function getData()
	{
		$value = parent::getValue();
		// the result must be an array with 'from'=> and 'to'=>
		if ( is_array($value) && isset($value['from'], $value['to']) ) {
			return [
				'from' => $this->_isDateValid($value['from']) ? $value['from'] : '',
				'to' => $this->_isDateValid($value['to']) ? $value['to'] : ''
			];
		}
		return ['from' => '', 'to' => ''];
	}

	/**
	 * Determine if a given date is in correct format and looks valid
	 * @param string $date
	 * @return bool
	 */
	private function _isDateValid($date)
	{
		return (new DateValidator(['format'=>'php:Y-m-d']))->validate($date);
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Date Range', 'group' => 'Date & Time', 'icon' => 'fa fa-calendar-o', 'order' => 325,
		];
	}

}