<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 28/03/2018
 * @package neon
 */

namespace neon\core\validators;

class MockModel
{
	private static $mock = null;

	/**
	 * @param string $label
	 * @return MockModel|null
	 */
	public static function get($label='')
	{
		if (self::$mock===null)
			self::$mock = new self();
		self::$mock->label = $label;
		return self::$mock;
	}
	public $label;
	public function getAttributeLabel($attribute) {
		return $this->label;
	}
}