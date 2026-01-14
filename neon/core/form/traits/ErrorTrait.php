<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 18/01/2018
 * @package neon
 */
trait ErrorTrait {
	/**
	 * Store an array of errors for this object
	 * @var array
	 */
	private $_errors = [];

	/**
	 * @return array
	 */
	public function getErrors()
	{
		return $this->_errors;
	}

	/**
	 * Returns true if this form or any subform has errors
	 * @return boolean
	 */
	public function hasError()
	{
		return !empty($this->getErrors());
	}

	/**
	 * Adds a new error to the specified attribute.
	 * @param string $error new error message
	 * @return void
	 */
	public function addError($error = '')
	{
		$this->_errors[] = $error;
	}
}