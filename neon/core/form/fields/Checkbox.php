<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use \neon\core\form\fields\Field;
use \neon\core\helpers\Arr;

/**
 * Class Text
 * @package neon\core\form
 */
class Checkbox extends Field
{

	/**
	 * @inheritdoc
	 */
	public $ddsDataType = 'boolean';

	/**
	 * The label to display when true
	 * @var string
	 */
	public $trueLabel = 'True';

	/**
	 * The label to display when false
	 * @var string
	 */
	public $falseLabel = 'False';

	/**
	 * The text to display adjacent to the checkbox
	 *
	 * @var string
	 */
	private $_checkboxLabel;

	/**
	 * The text to display adjacent to the checkbox
	 *
	 * @param $value
	 * @return $this;
	 */
	public function setCheckboxLabel($value)
	{
		$this->_checkboxLabel = $value;
		return $this;
	}

	/**
	 * Get the text to display adjacent to the checkbox
	 *
	 * @return string
	 */
	public function getCheckboxLabel()
	{
		return $this->_checkboxLabel;
	}

	/**
	 * @inheritdoc
	 */
	public function getData()
	{
		// The value here is evaluating the user input
		// typically this is a string of 0 or 1, however we also support true and false strings
		// by using the truthy function a string of 'false' will return as boolean false
		// (a normal php if statement would evaluate a string of 'false' as true
		return truthy($this->getValue()) ? 1 : 0;
	}

	/**
	 * @inheritdoc
	 */
	public function getValueDisplay($context='')
	{
		return $this->getData() ? $this->trueLabel : $this->falseLabel;
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
	public function getProperties()
	{
		return Arr::merge(parent::getProperties(), ['checkboxLabel']);
	}

	/**
	 * @inheritdoc
	 */
	public function fake()
	{
		return faker()->boolean ? 0 : 1;
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Checkbox', 'group' => 'Choice', 'icon' => 'fa fa-check-square', 'order' => 440,
		];
	}
}