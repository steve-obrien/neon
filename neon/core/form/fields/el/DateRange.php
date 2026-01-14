<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 21/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\form\fields\el;

use neon\core\grid\query\IQuery;
use neon\core\form\fields\Field;
use neon\core\form\fields\el\assets\ElAsset;
use neon\core\helpers\Html;
use neon\core\helpers\Arr;

class DateRange extends DateTimeRange
{
	public $type = 'daterange';

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		return array_merge(parent::getProperties(), ['type', 'format']);
	}

	/**
	 * @inheritdoc
	 */
	public function getValue()
	{
		$value = parent::getValue();
		if (is_array($value))
			return $value;
		return ['from' => '', 'to' => ''];
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
			'label' => 'El Date Range', 'group' => 'Form Only', 'icon' => 'fa fa-calendar-o', 'order' => 910,
		];
	}
}