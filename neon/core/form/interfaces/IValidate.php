<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 19/01/2018
 * @package neon
 */
namespace neon\core\form\interfaces;

interface IValidate
{
	/**
	 * Equivalent to calling ```$this->addValidator('required')```
	 *
	 * @param bool $required
	 * @return $this method can be chained
	 */
	public function setRequired($required=true);

	/**
	 * Add a validator to a form field
	 *
	 * @param string|array|object $validator
	 * Currently supported validator strings:
	 *
	 * - `boolean`: @see \yii\validators\BooleanValidator [[BooleanValidator]]
	 * - `captcha`: @see \yii\validators\BooleanValidator [[\yii\captcha\CaptchaValidator]]
	 * - `compare`: @see \yii\validators\CompareValidator [[CompareValidator]]
	 * - `date`: @see \yii\validators\DateValidator [[DateValidator]]
	 * - `default`: @see \yii\validators\DefaultValueValidator [[DefaultValueValidator]]
	 * - `double`: @see \yii\validators\NumberValidator [[NumberValidator]]
	 * - `each`: @see \yii\validators\EachValidator [[EachValidator]]
	 * - `email`: @see \yii\validators\EmailValidator [[EmailValidator]]
	 * - `exist`: @see \yii\validators\ExistValidator [[ExistValidator]]
	 * - `file`: @see \yii\validators\FileValidator [[FileValidator]]
	 * - `filter`: @see \yii\validators\FilterValidator
	 * - `image`: @see \yii\validators\ImageValidator
	 * - `in`: @see \yii\validators\RangeValidator
	 * - `integer`: @see \yii\validators\NumberValidator [[NumberValidator]]
	 * - `match`: @see \yii\validators\RegularExpressionValidator [[RegularExpressionValidator]]
	 * - `required`: @see \yii\validators\RequiredValidator [[RequiredValidator]]
	 * - `safe`: @see \yii\validators\SafeValidator [[SafeValidator]]
	 * - `string`: @see \yii\validators\StringValidator [[StringValidator]]
	 * - `trim`: @see \yii\validators\FilterValidator [[FilterValidator]]
	 * - `unique`: @see \yii\validators\UniqueValidator [[UniqueValidator]]
	 * - `url`: @see \yii\validators\UrlValidator [[UrlValidator]]
	 * - `ip`: @see \yii\validators\IpValidator [[IpValidator]]
	 *
	 * @param array $options options for the validator object
	 * @see \neon\core\validators\Validator
	 *
	 * @return $this Is a chainable function
	 */
	public function addValidator($validator, $options=[]);

	/**
	 * @param array $validators
	 * set a bulk of validators in array format instead of individually writing addValidator() commanda
	 * the format of the array is like so:
	 *
	 * ```php
	 *     [
	 *         'string' => ['max' => 10],
	 *         'required',
	 *         ['string', ['max' => 50]],
	 *         ['class' => 'my/validator', 'option1' => 1],
	 *         'hash-key-md5-or-uuid' => [
	 *              'class' => 'my/validator', 'option1' => 1,
	 *          ]
	 *     ]
	 * ```
	 * @return $this
	 */
	public function setValidators($validators);

	/**
	 * Removes all validators of a particular class type
	 *
	 * @param string $class full class name of alias
	 */
	public function removeValidator($class);

	/**
	 * returns a boolean indicating whether the validator exists on this field
	 * only checks if a class of the same type exists for example a required validator
	 * or a string validator
	 *
	 * @param string $class full class name of alias
	 * @return bool
	 */
	public function hasValidator($class);

	/**
	 * Returns an array of validator objects
	 *
	 * @see \neon\core\validators\Validator
	 * @return array key => validator object
	 */
	public function getValidators();

	/**
	 * Returns a value indicating whether there is any validation error.
	 *
	 * @return boolean whether there is any error.
	 */
	public function hasError();

	/**
	 * Add an error
	 *
	 * @param string $message - The error message
	 * @return array
	 */
	public function addError($message);

	/**
	 * Get all errors
	 *
	 * @return array
	 */
	public function getErrors();

	/**
	 * Run the validations and return true / false
	 *
	 * @return boolean
	 */
	public function validate();
}