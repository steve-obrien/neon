<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use neon\core\form\fields\Field;
use neon\core\grid\query\IQuery;
use neon\core\helpers\Html;

/**
 * Class Real
 * Stores real numbers (float doubles) with up to
 *   16 digits (float limit) including decimals
 * @package neon\core\form
 */
class Real extends Field
{
	/**
	 * The DDS data type to store the value of the field
	 * @var string
	 */
	public $ddsDataType = 'floatdouble';

	/**
	 * The number of decimal places allowed
	 * @var integer
	 */
	public $decimal = 1;

	/**
	 * @inheritdoc
	 */
	public function setValue($value)
	{
		if (is_numeric($value))
			return parent::setValue(round($value, $this->decimal));
		parent::setvalue(null);
	}

	/**
	 * @inheritdoc
	 */
	public function getValue()
	{
		// prevent returning 0 when casting a null value to a (real) number
		if (!is_numeric($this->_value))
			return null;
		// (real) has been deprecated >= PHP.4 - use float instead
		return (float) $this->_value;
	}

	/**
	 * @inheritdoc
	 */
	public function getValueDisplay($context='')
	{
		$value = $this->getValue();
		if ($this->decimal === 0)
			return neon()->formatter->asInteger($value);
		return neon()->formatter->asDecimal($value, $this->decimal);
	}

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		return array_merge(parent::getProperties(), ['decimal']);
	}

	/**
	 * @inheritdoc
	 */
	public function fake()
	{
		return faker()->randomFloat($this->decimal);
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Real Number', 'icon' => 'fa fa-registered', 'group' => 'Number', 'order' => 710
		];
	}
}