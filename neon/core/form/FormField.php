<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 19/01/2018
 * @package neon
 */

namespace neon\core\form;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use neon\core\form\exceptions\InvalidNameFormat;
use neon\core\form\interfaces\IField;
use neon\core\form\interfaces\IValidate;
use neon\core\validators\Validator;
use neon\core\helpers\Arr;
use yii\base\Widget;

/**
 * Base class for both forms and form fields
 * Class FormField
 * @package neon\core\form
 */
abstract class FormField extends Widget
	implements IValidate, IField
{
	/**
	 * Toggle the field or forms visibility
	 * @var bool
	 */
	public $visible = true;

	/**
	 * Whether to display the form elements inline (display inline) opposed to block
	 * When true will attempt to align fields on one line
	 * @var bool
	 */
	public $inline = false;

	/**
	 * An integer value in which to order this field by
	 * @var integer
	 */
	public $order = 1;

	/**
	 * @var boolean - soft deletes this field inside a form
	 * the field object will still be returned by getFields() but it will not appear in data
	 * output or be sent to persist in any form persistence back end
	 */
	public $deleted = false;

	/**
	 * The default implementation throws an exception when it comes across read only properties
	 * we just want to ignore read only properties
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		$setter = 'set' . $name;
		if (method_exists($this, $setter)) {
			$this->$setter($value);
		}
	}

	/**
	 * @var string items name - also acts as items key
	 */
	protected $_name;

	/**
	 * @inheritdoc
	 */
	public function setName($name)
	{
		if (!preg_match('/^[A-Za-z0-9_-]+$/', $name, $matches)) {
			throw new InvalidNameFormat($name);
		}
		$this->_name = $name;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		if (empty($this->_name)) {
			throw new InvalidArgumentException('A name property has not been defined. Make sure you set the components "name" property');
		}
		return $this->_name;
	}

	/**
	 * @var null reference to the parent form
	 */
	protected $_form = null;

	/**
	 * @inheritdoc
	 */
	public function setForm($form)
	{
		$this->_form = $form;
		return $this;
	}

	/**
	 * @return \neon\core\form\Form|null
	 */
	public function getParentForm()
	{
		return $this->_form;
	}

	/**
	 * @return \neon\core\form\Form|null
	 */
	public function getForm()
	{
		return $this->_form;
	}

	/**
	 * whether this form is a sub form
	 * @return bool
	 */
	public function isSubForm()
	{
		return ($this->isForm() && $this->hasForm());
	}

	/**
	 * Whether this form represents the root form node
	 * @return bool
	 */
	public function isRootForm()
	{
		// the root form will not have a parent form reference
		return ($this->isForm() && !$this->hasForm());
	}

	/**
	 * @return bool
	 */
	public function hasForm()
	{
		return $this->_form === null ? false : true;
	}

	/**
	 * Whether this is a form - defaults to false
	 * @return bool
	 */
	public function isForm()
	{
		return false;
	}

	/**
	 * @return string
	 */
	public function getInputName()
	{
		if ($this->hasForm()) {
			return $this->getForm()->getInputName() . '[' . $this->getName() . ']';
		}
		return $this->getName();
	}

	/**
	 * Store an array of errors for this object
	 * @var array
	 */
	private $_errors = [];

	/**
	 * @inheritdoc
	 */
	public function getErrors()
	{
		return $this->_errors;
	}

	/**
	 * @inheritdoc
	 */
	public function hasError()
	{
		return !empty($this->getErrors());
	}

	/**
	 * @inheritdoc
	 */
	public function addError($error = '')
	{
		$this->_errors[] = $error;
	}

	/**
	 * Get the first error
	 * @return string error message or empty string
	 */
	public function getFirstError()
	{
		return Arr::get($this->_errors, 0, '');
	}

	public function setErrors($errors=[])
	{
		$this->_errors = $errors;
	}

	/**
	 * @param bool $required
	 * @return $this
	 */
	public function setRequired($required = true)
	{
		// At the moment the form builders and definition expect a required property of true and false on a fields definition.
		// ideally this would be a virtual property the form fields can create based on ONE source of truth - the actual
		// validators array. However the form definitions do not YET build correct validator definitions
		// and so we can get in to conditions where a required validator is present but the form field's required property is false and visa versa
		// this is because validators will also be stored in the definition when the form serialises.
		// this is a real pain - and due a refactor however there is no neat fix until the front end js can produce correct
		// validator definitions and understand how to know if the required validator exists
		if ($required) {
			$this->addValidator('required');
		} else {
			$this->removeValidator('required');
		}
		return $this;
	}

	/**
	 * Whether this field is required
	 *
	 * @return boolean
	 */
	public function isRequired()
	{
		return $this->hasValidator('required');
	}

	/**
	 * Whether the component has a required validator
	 *
	 * @alias $this->isRequired()
	 * @return boolean
	 */
	public function getRequired()
	{
		return $this->isRequired();
	}

	/**
	 * @var \neon\core\validators\Validator[]
	 */
	protected $_validators = [];

	/**
	 * @inheritdoc
	 */
	abstract public function validate();

	/**
	 * @inheritdoc
	 */
	public function getValidators()
	{
		return $this->_validators;
	}

	/**
	 * @inheritdoc
	 */
	public function setValidators($validators, $overwrite=true)
	{
		if ($overwrite)
			$this->_validators = [];
		if (is_array($validators)) {
			foreach ($validators as $key => $validator) {
				if (is_numeric($key)) {
					$this->addValidator($validator);
				}
				// handles:
				// $this->setValidators(['string' => ['max' => 5]])
				// $this->setValidators(['ewefewfer43432' => Object])
				else if (is_string($key)) {
					if (is_array($validator)) {
						// so the validator key cooouuuullldd be the class alias key e.g. 'string', 'required', 'date' etc
						// but it could also be an md5 hash of the validator class and settings - this allows multiple
						// of the same validators to exist as long as they have different settings.
						// lets check if the key exists
						if (!isset($validator['class'])) {
							// only trust the key if we do not have an explicit class property set
							// the system will replace this short had method of defining validators later with a md5 hashed
							// key of the validator and its options
							$validator['class'] = $key;
						}
					}
					$this->addValidator($validator, [], $key);
				}
			}
		}
		return $this;
	}

	/**
	 * There are about a million formats validators can be defined in
	 * This function figures them out and returns a standard one.
	 * @param array|string $validator
	 * @return array['class' => string]
	 */
	public function normaliseValidatorArrayDefinition($validator)
	{
		$ret = [];
		// $this->setValidators([ ['string', ['max'=>5]], ]) - yii format
		// deprecate this ugly and stupid bloody format
		if (is_array($validator) && !Arr::isAssoc($validator)) {
			$validatorName = $validator[0];
			$options = isset($validator[1]) ? $validator[1] : [];
			$options['class'] = $validatorName;
			$ret = $options;
		}
		// $this->setValidators([ ['class' => '\yii\validators\StringValidator', 'max' => 5], ])
		else if (is_array($validator) && isset($validator['class'])) {
			$ret = $validator;
		}
		// $this->setValidators(['email'])
		else if (is_string($validator)) {
			$ret = ['class' => $validator];
		} else {
			throw new \Exception("Invalid validator information. Probably missing neon class. ".print_r($validator,true));
		}

		// finally normalise the validator class
		$ret['class'] = $this->_getValidatorClass($ret['class']);
		return $ret;
	}

	/**
	 * @inheritdoc
	 */
	public function addValidator($validator, $options = [], $fixedKey=null)
	{
		$validatorObject = $this->createValidator($validator, $options, $key);
		$this->_validators[$fixedKey ? $fixedKey : $key] = $validatorObject;
		return $this;
	}

	/**
	 * Create a validator object
	 * @param string|array|object $validator - string validator name, a validator array definition or a validator object
	 * @param array $options - when the validator is a string this represents options on the validator
	 * @param string $key - a key to identify this validator - an md5 hash of the validator and its options
	 * @return \yii\validators\Validator
	 */
	public function createValidator($validator, $options=[], &$key='')
	{
		if (is_object($validator)) {
			$validatorObject = $validator;
		} else if (is_string($validator)) {
			$validatorObject = Validator::createValidator($validator, $this, $this->getName(), $options);
		} else {
			$definition = $this->normaliseValidatorArrayDefinition($validator);
			$validatorObject = Validator::createValidator($definition['class'], $this, $this->getName(), $definition);
		}
		$key = md5(json_encode($validatorObject, JSON_FORCE_OBJECT));
		return $validatorObject;
	}

	/**
	 * @inheritdoc
	 */
	public function removeValidator($validator)
	{
		$class = $this->_getValidatorClass($validator);
		foreach($this->_validators as $key => $validatorObject) {
			if ($validatorObject instanceof $class) {
				unset($this->_validators[$key]);
			}
		}
	}

	/**
	 * Fetch all validators of a particular type
	 * @param string $class full class name of alias of validator
	 * @return array
	 */
	public function getValidator($class)
	{
		$class = $this->_getValidatorClass($class);
		return collect($this->getValidators())->filter(function($value) use ($class) {
			return $value instanceof $class;
		})->all();
	}

	/**
	 * Converts alias validators to full validator class names
	 * for e.g. 'required' becomes '\neon\core\validators\RequiredValidator'
	 * @param string $class
	 * @return mixed
	 */
	protected function _getValidatorClass($class)
	{
		if (is_array($class) && isset($class['class']))
			$class = $class['class'];
		if (is_object($class))
			$class = get_class($class);

		if (array_key_exists($class, Validator::$builtInValidators)) {
			$class = Validator::$builtInValidators[$class]['class'];
		}
		return $class;
	}

	/**
	 * @inheritdoc
	 */
	public function hasValidator($class)
	{
		// lookup string in built in validators, to find the correct class
		// for example 'required' gives ['class'=>'\yii\validators\Required']
		// otherwise assume a validator class string has been passed in
		$class = $this->_getValidatorClass($class);
		$value = collect($this->getValidators())->first(function ($validatorObject) use ($class) {
			return $validatorObject instanceof $class;
		}, false);

		return $value !== false;
	}

	/**
	 * @param string $value
	 * @return $this
	 */
	public function setId($value)
	{
		$this->_id = $value;
		return $this;
	}

	/**
	 * The field id
	 *
	 * @return string
	 */
	public function getId($autoGenerate = true)
	{
		return $this->_id;
	}

	/**
	 * @inheritdoc
	 */
	public function getIdPath($delimiter='.')
	{
		// set the form id if not already set to something else
		if ($this->isRootForm())
			return $this->getId() ?: $this->getName();
		return $this->hasForm()
			? $this->getForm()->getIdPath($delimiter) . $delimiter . $this->getName()
			: $this->getName();
	}

	public function fake()
	{
		return $this->getValue();
	}

	/**
	 * @var string|null
	 */
	protected $_id;

}
