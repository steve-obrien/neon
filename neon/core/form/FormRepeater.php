<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */
namespace neon\core\form;

use http\Exception\InvalidArgumentException;
use neon\core\form\fields\Field;
use neon\core\helpers\Arr;
use neon\core\form\interfaces\IField;
use neon\core\helpers\Hash;

/**
 * Repeaters allow the dynamic creation of sub forms.
 * Repeaters store the template for new sub forms in their template property.
 * Class FormRepeater
 * @package neon\core\form
 */
class FormRepeater extends Form
{
	/**
	 * @var bool
	 */
	public $allowAdd = true;
	/**
	 * Are elements allowed to be removed dynamically?
	 * @var bool
	 */
	public $allowRemove = true;
	/**
	 * Initial count of repeatable forms
	 * @var int
	 */
	public $count = 1;
	/**
	 * Maximum number of sub forms allowed
	 * Zero means no maximum
	 * @var int
	 */
	public $countMax = 0;
	/**
	 * Minimum number required
	 * Zero means no minimum
	 * @var int
	 */
	public $countMin = 0;

	public $inline = true;

	/**
	 * Load form data from the system representations
	 *
	 * Since repeaters add from the data provided and we don't know the number
	 * nor the keys for these, we need to add the instances to the form
	 * 
	 * @param array $data
	 */
	public function loadFromDb($data)
	{
		foreach ($data as $name => $value) {
			if (!$this->hasField($name))
				$this->addInstance($name);
			$this->getField($name)->setValueFromDb($value);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		return Arr::merge(['class'], $this->getRepeaterProperties(), parent::getProperties());
	}

	/**
	 * Get properties specific to this "repeater" class
	 * @return array
	 */
	public function getRepeaterProperties()
	{
		return ['name', 'id', 'allowAdd', 'allowRemove', 'count', 'countMax', 'countMin', 'template'];
	}

	/**
	 * @inheritdoc
	 * We will expect the $value to be an array of forms. For example:
	 * ```php
	 * [
	 *     'wergwergwerg' => [
	 *         'first_name'=> 'Jane'
	 *     ],
	 *     '456345bhshbvderw' => [
	 *         'first_name' => 'Jon'
	 *     ],
	 *     // ...
	 * ]
	 * ```
	 */
	public function setValue($value)
	{
		// throw an exception - should probably cope with this scenario but set
		// a validation to fail rather than throwing toys out of the pram
		if (!is_array($value))
			throw new \InvalidArgumentException('The value should be an array');

		foreach($value as $position => $values) {
			if (!$this->hasField($position)) {
				$this->addInstance($position);
			}
			$this->getField($position)->setValue($values);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getValue()
	{
		$subFormCount = count(parent::getSubForms());
		if ($subFormCount < $this->count) {
			for ($i = $subFormCount; $i <= $this->count-1; $i++) {
				$this->addInstance($i);
			}
		}
		return parent::getValue();
	}

	/**
	 * The fields in the template must register their scripts
	 * @param null $view
	 */
	public function registerScripts($view=null, $mount=true)
	{
		$view = ($view === null) ? $this->getView() : $view;
		foreach ($this->getTemplate()->getFields() as $field)
			$field->registerScripts($view, $mount);
	}

	/**
	 * Add a new instance of the sub form to the form
	 * @param $name
	 * @return Field
	 */
	public function addInstance($name)
	{
		$subForm = $this->getTemplate()->toArray();
		$subForm['name'] = $name;
		$sf = $this->makeField($subForm, ['name' => "$name"]);
		$formInstance = $this->_addField($sf);
		return $formInstance;
	}

	/**
	 * Store the form object we use as a template
	 * @var Form
	 */
	private $_templateForm = null;

	/**
	 * Get the form object that the repeater form uses to create each form instance
	 *
	 * @return Form|null
	 */
	public function getTemplate()
	{
		if ($this->_templateForm === null) {
			// The template form is a standard form and therefore has no concept of repeatable forms properties
			// remove repeater specific properties
			// we only want properties of the parent form
			$this->_templateForm = new Form([
				'class' => '\neon\core\form\Form',
				'form' => $this->getForm(),
				'repeater' => true,
				// gets replaced by javascript with a uuid for the new form row created
				'name' => '__AUTONAME__',
				'inline' => $this->inline
			]);
		}
		return $this->_templateForm;
	}

	/**
	 * Set the template of the instance forms
	 * 'fields' key should be set if passing an array,
	 * otherwise pass in a complete form in the structure you want the repeater to generate
	 * @param array|IField $template
	 * @return $this
	 */
	public function setTemplate($template)
	{
		// if the value is an object then assume this is a form,
		// we can set the private templateForm property.
		// This object will be used to create new instances of sub forms (rows)
		if (is_object($template)) {
			$this->_templateForm = $template;
		}

		if (is_array($template)) {
			foreach ($template as $key => $value) {
				$this->getTemplate()->$key = $value;
			}
		}
		return $this;
	}

	/**
	 * Get fields - also add additional instances as appropriate
	 * @inheritdoc
	 */
	public function getFields($names=null)
	{
		$subFormCount = count(parent::getSubForms());
		if ($subFormCount < $this->count) {
			for ($i = $subFormCount; $i <= $this->count-1; $i++) {
				$this->addInstance(Hash::uuid64());
			}
		}
		return parent::getFields($names);
	}

	/**
	 * Get field path for a repeater will attempt to return the full path to the fields - however repeaters do not
	 * always contain all their instances.  Therefore if the no field exists with the given key it will ignore the
	 * key value and look for the next field on the template form instance.
	 * Because repeaters allow you to create new items - essentially each field of a repeater is a custom field
	 * name with an instance of the repeatable template form inside it.
	 * We may received a path like this: 'myForm.people.d2pffa73bsoFOCHB19dGwX.emails.nshbfv9825tGBw09343rWE.email'.
	 * If the form currently contains this data then the email field from the correct instances will be returned.
	 * This means it would be possible to access its value by called $this->getValue().  However if the keys do not
	 * exist then the email field will still be returned but it will be located by looking at the repeaters template
	 * form. When accessing the path the keys may have been generated by javascript and so do not yet exist in the form.
	 * We need to ignore the custom generated field id and ask the template for the field information.
	 * @param string $path
	 * @return IField|Form|null
	 * @throws \Exception if the first part of the path is not the current form
	 */
	public function getFieldByPath($path)
	{
		// the root path bit can be the the name or the id.
		$bits = explode('.', $path);
		if (!($bits[0] === $this->name))
			throw new \Exception("The first part of the path should be the current repeater name. Repeater form name=$this->name, id=$this->id, path=$path.");

		// if the repeater has an existing instance - then lets return that
		if ($this->hasField($bits[1])) {
			$field = $this->getField($bits[1]);
			// fields within repeaters should always be forms - so this should never happen
			// but just to ensure we trap the error - lets throw an exception
			if (!$field->isForm())
				throw new \InvalidArgumentException('FormRepeaters should only contain form instances.');
			array_splice($bits, 0, 1);
			return $field->getFieldByPath(implode('.', $bits));
		}

		// if it does not exist we delegate to the template
		array_splice($bits, 0, 2);
		$field = $this->getTemplate()->getField($bits[0]);
		if ($field->isForm()) {
			// if this is a form then delegate to the sub form to find its children.
			// this is important for repeaters that manage a collection of forms with unique ids
			return $field->getFieldByPath(implode('.', $bits));
		}
		return $field;
	}
}