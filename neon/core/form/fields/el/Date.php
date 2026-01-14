<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 21/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\form\fields\el;

use neon\core\form\fields\Field;
use neon\core\form\fields\el\assets\ElAsset;
use neon\core\helpers\Converter;
use neon\core\helpers\Html;

class Date extends Field
{
	/**
	 * The DDS data type to store the value of the field
	 * @var string
	 */
	public $ddsDataType = 'date';

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		return array_merge(parent::getProperties(), ['format']);
	}

	/**
	 * @inheritdoc
	 */
	public function getFilterField()
	{
		return [
			'class' => 'neon\core\form\fields\el\DateRange'
		];
	}

	/**
	 * Get the picker format
	 * @return string
	 */
	public function getFormat()
	{
		return neon()->formatter->dateFormat;
	}

	/**
	 * @inheritdoc
	 */
	public function getValueDisplay($context='')
	{
		return neon()->formatter->asDate($this->getValue());
	}

	/**
	 * @inheritdoc
	 */
	public function registerScripts($view)
	{
		ElAsset::register($view);
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'El Date', 'group' => 'Date & Time', 'icon' => 'fa fa-calendar-o', 'order' => 350,
		];
	}
}