<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 28/03/2018
 * @package neon
 */
namespace neon\core\validators;


/**
 * Class ValidationTrait
 * This is purely here to override functions in the base Validation class
 * For example StringValidator needs to extend from Yii StringValidator however we also want to override
 * the yii validator base class that Yii StringValidator inherits.  Copying this into each child class
 * effectively copy and pastes the over ridden functions into the child neon classes without duplicating the code
 * in every child class
 * @package neon\core\validators
 */
trait ValidationTrait
{
	public function getLabel()
	{
		return \Yii::t('yii', 'This');
	}

	/**
	 * Validates a given value.
	 * You may use this method to validate a value out of the context of a data model.
	 * @param mixed $value the data value to be validated.
	 * @param string $error the error message to be returned, if the validation fails.
	 * @return bool whether the data is valid.
	 */
	public function validate($value, &$error = null)
	{
		$result = $this->validateValue($value);
		if (empty($result)) {
			return true;
		}

		list($message, $params) = $result;
		$params['attribute'] = $this->getLabel();
		if (is_array($value)) {
			$params['value'] = 'array()';
		} elseif (is_object($value)) {
			$params['value'] = 'object';
		} else {
			$params['value'] = $value;
		}
		$error = $this->formatMessage($message, $params);

		return false;
	}

	/**
	 * Get the client options of the validator
	 * This returns an array of options to pass to the client JS
	 * Typically just invokes the getClientOptions of the YiiValidator however because the client options often
	 * also includes the validation error messages we need to pass in a MockModel that correctly resolves the
	 * getAttributeLabel
	 * @return mixed
	 */
	public function getOptions()
	{
		return $this->getClientOptions(MockModel::get($this->getLabel()), '');
	}

	/**
	 * Get an array of options to represent a serialised version of the validator
	 * @return mixed
	 */
	public function toArray()
	{
		return array_merge(
			['class' => $this->getClass()],
			$this->getOptions());
	}

	public function getProperties() {}

}