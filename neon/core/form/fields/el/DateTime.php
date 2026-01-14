<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 21/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\form\fields\el;

use neon\core\form\fields\Field;
use neon\core\form\fields\el\assets\ElAsset;
use neon\core\helpers\Html;

class DateTime extends Field
{
	/**
	 * The DDS data type to store the value of the field
	 * @var string
	 */
	public $ddsDataType = 'datetime';

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
	public function getProperties()
	{
		return array_merge(parent::getProperties(), ['format']);
	}

	/**
	 * Get the picker format
	 * @return string
	 */
	public function getFormat()
	{
		return neon()->formatter->datetimeFormat;
	}

	/**
	 * @inheritdoc
	 */
	public function getValueDisplay($context='')
	{
		return neon()->formatter->asDatetime($this->getValue());
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'El Date Time', 'group' => 'Date & Time', 'icon' => 'fa fa-calendar-o', 'order' => 360,
		];
	}
}