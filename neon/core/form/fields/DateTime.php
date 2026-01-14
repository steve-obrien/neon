<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 21/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\form\fields;

use neon\core\helpers\Arr;
use neon\core\helpers\Converter;
use neon\core\helpers\Html;
use neon\core\validators\DateValidator;

/**
 * Class DateTime
 * Stores the value as a mysql datetime format.
 * @package neon\core\form\fields
 */
class DateTime extends Date
{
	/**
	 * The DDS data type to store the value of the field
	 * @var string
	 */
	public $ddsDataType = 'datetime';

	/**
	 * Options passed to the time field - @see Time
	 * @var array
	 */
	public $timeOptions = [];

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		return array_merge(parent::getProperties(), ['timeOptions']);
	}

	/**
	 * @inheritdoc
	 */
	public function getData()
	{
		$value = $this->getValue();
		// check it is in mysql format
		if ($value !== null && (new DateValidator(['format'=>'php:Y-m-d H:i:s', 'type' => DateValidator::TYPE_DATETIME]))->validate($value)) {
			return $value;
		}
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function init() {
		$this->addValidator(
			['class' => DateValidator::class, 'format'=>'php:Y-m-d H:i:s', 'type' => DateValidator::TYPE_DATETIME]
		);
	}

	/**
	 * @inheritdoc
	 */
	public function getValueDisplay($context='')
	{
		return neon()->formatter->asDatetime($this->getData());
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Date Time', 'group' => 'Date & Time', 'icon' => 'fa fa-calendar-o', 'order' => 310,
		];
	}
}