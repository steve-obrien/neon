<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 21/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\form\fields;

use Carbon\Carbon;
use neon\core\form\fields\Field;
use neon\core\grid\query\IQuery;
use neon\core\helpers\Arr;
use neon\core\helpers\Converter;
use neon\core\helpers\Html;
use \neon\core\assets\JQueryUiAsset;
use neon\core\helpers\Str;
use neon\core\validators\DateValidator;

class Date extends Field
{
	/**
	 * The DDS data type to store the value of the field
	 * @var string
	 */
	public $ddsDataType = 'date';

	/**
	 * The placeholder to show to the user when filtering
	 * @var string
	 */
	public $placeholder = '';

	/**
	 * The range of years to offer as a selection
	 * @var string
	 */
	public $yearRange = 'c-10:c+10';

	/**
	 * @var string | null - date string in mysql format YYYY-MM-DD or null (no min date)
	 */
	protected $_minDate = null;

	/**
	 * Set the minimum date shown - affects the display only
	 * @param string | null $value - date string in mysql Set the minimum date allowable
	 */
	public function setMinDate($value)
	{
		$this->_minDate = $value;
	}

	/**
	 * Get the min date allowable
	 * @return null|string
	 */
	public function getMinDate()
	{
		return $this->_minDate;
	}

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		return Arr::merge(parent::getProperties(), ['minDate', 'datePickerFormat', 'yearRange']);
	}

	/**
	 * Returns a Jquery ui date format string by converting the dateUserFormat property which is a PHP date format string
	 * http://api.jqueryui.com/datepicker/#utility-formatDate
	 * @return string
	 */
	public function getDatePickerFormat()
	{
		return Converter::convertDateFormatToJui(neon()->formatter->dateFormat);
	}

	/**
	 * @inheritdoc
	 */
	public function registerScripts($view)
	{
		JQueryUiAsset::register($view);
	}

	/**
	 * Typically you want to search for a date range rather than a specific date equality
	 * so we set the filter to use a date range
	 * @inheritdoc
	 */
	public function getFilterField()
	{
		return [
			'class' => 'neon\core\form\fields\DateRange',
			'datePickerFormat' => $this->datePickerFormat
		];
	}

	/**
	 * @inheritdoc
	 */
	public function processAsFilter(IQuery $query, $searchData=null)
	{
		$searchData = ($searchData === null) ? $this->getValue() : $searchData;
		if ($searchData !== '') {
			$query->where($this->getDataKey(), '=', $searchData);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getData()
	{
		$value = $this->getValue();
		// check it is in mysql format
		if ((new DateValidator(['format'=>'php:Y-m-d']))->validate($value ?? '')) {
			return $value;
		}
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function getValueDisplay($context='')
	{
		// the asDate function sets all empty values to 0 meaning it returns a formatted version of 1970-01-01
		if (empty($this->getData()))
			return neon()->formatter->nullDisplay;
		return neon()->formatter->asDate($this->getData());
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Date', 'group' => 'Date & Time', 'icon' => 'fa fa-calendar-o', 'order'=>320,
		];
	}
}
