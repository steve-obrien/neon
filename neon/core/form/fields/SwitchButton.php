<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use neon\core\helpers\Arr;

/**
 * Class SwitchButton
 * @package neon\core\form
 */
class SwitchButton extends Checkbox
{
	/**
	 * The DDS data type to store the value of the field
	 * @var string
	 */
	public $ddsDataType = "boolean";

	/**
	 * Supported values: 'small' any other value will be tagged onto the switch css class name
	 * @var string
	 */
	public $size = '';

	/**
	 * Override the true and false labels in checkbox
	 * @var string
	 */
	public $trueLabel = 'Yes';
	public $falseLabel = 'No';

	/**
	 * Set the label to display when the switch is in the "on" position
	 *
	 * @param string $value
	 * @return $this
	 */
	public function setOnLabel($value)
	{
		$this->trueLabel = $value;
		return $this;
	}

	/**
	 * Set the label to display when in the "off" position
	 *
	 * @param string $value
	 * @return $this
	 */
	public function setOffLabel($value)
	{
		$this->falseLabel = $value;
		return $this;
	}

	/**
	 * Set switch to be small
	 * @param string $value supported values: 'small' any other value will be tagged onto the switch css class name
	 * @return $this
	 */
	public function setSize($value)
	{
		$this->size = $value;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getFilterField()
	{
		return [
			'class' => 'neon\core\form\fields\Select',
			'items' => [0 => $this->falseLabel, 1 => $this->trueLabel]
		];
	}

	/**
	 * @inheritdoc
	 */
	public function getValueDisplay($context='')
	{
		return $this->value ? $this->trueLabel : $this->falseLabel;
	}

	/**
	 * @return array
	 */
	public function getProperties()
	{
		return Arr::merge(parent::getProperties(), ['trueLabel', 'falseLabel']);
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Switch Two State', 'icon' => 'fa fa-toggle-on', 'group' => 'Choice', 'order' => 450
		];
	}
}