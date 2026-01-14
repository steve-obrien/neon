<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016-18 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */
namespace neon\core\form;

use neon\core\form\fields\Field;
use neon\core\grid\query\IQuery;
use neon\core\helpers\Collection;
use neon\core\helpers\Hash;
use neon\core\traits\PropertiesTrait;
use neon\core\form\traits\BuilderTrait;
use ReflectionClass;
use yii\base\InvalidCallException;
use yii\base\UnknownPropertyException;
use neon\core\helpers\Arr;
use neon\core\helpers\Html;
use neon\core\form\assets\FormAsset;
use neon\core\form\interfaces\IField;

class Form extends FormField implements IField, \ArrayAccess
{
	use PropertiesTrait;
	use BuilderTrait;

	/**
	 * The form submission method, such as "post", "get", "put", "delete" (case-insensitive).
	 * Since most browsers only support "post" and "get", if other methods are given, they will
	 * be simulated using "post", and a hidden input will be added which contains the actual method type.
	 * @see \yii\web\Request::$methodParam for more details.
	 * @var string
	 */
	public $method = 'post';
	/**
	 * @var bool
	 */
	public $readOnly = false;
	/**
	 * @var bool
	 */
	public $printOnly = false;
	/**
	 * @var boolean whether to enable client-side data validation.
	 */
	public $enableClientValidation = true;
	/**
	 * @var boolean whether to enable AJAX-based data validation.
	 */
	public $enableAjaxValidation = true;
	/**
	 * @var boolean whether to enable AJAX-based form submission.
	 */
	public $enableAjaxSubmission = false;
	/**
	 * @var array|string the URL for performing AJAX-based validation. This property will be processed by
	 * [[Url::to()]]. Please refer to [[Url::to()]] for more details on how to configure this property.
	 * If this property is not set, it will take the value of the form's action attribute.
	 */
	public $validationUrl;
	/**
	 * @var string the name of the GET parameter indicating the validation request is an AJAX request.
	 */
	public $ajaxParam = 'ajax';
	/**
	 * @var string the type of data that you're expecting back from the server.
	 */
	public $ajaxDataType = 'json';
	/**
	 * @var boolean whether to scroll to the first error after validation.
	 * @since 2.0.6
	 */
	public $scrollToError = true;
	/**
	 * @var bool
	 */
	public $forceUniqueFieldNames = true;
	/**
	 * @var string  the name of the button used to submit the form
	 */
	public $submittedButton = null;

	/**
	 * @var bool Whether the form has been submitted - alters the js rendering as to whether errors should display
	 */
	public $isSubmitted = false;

	/**
	 * An array of form html attributes to be applied to the form tag
	 * @see \neon\core\helpers\Html::renderTagAttributes()
	 * @var array
	 */
	protected $_attributes = [];

	/**
	 * Sets html attributes where the key is the attribute name and the value is the value
	 * Will merge certain properties like 'class' use replace if you do not want the merge behaviour
	 * @param $attrs
	 * @param bool $replace - will replace any existing attributes
	 * @return $this - chainable interface
	 */
	public function setAttributes($attrs, $replace=false)
	{
		if ($replace) {
			$this->_attributes = $attrs;
		} else {
			if (isset($attrs['class'])) {
				$this->_attributes['class'] = ' ' . $attrs['class'];
				unset($attrs['class']);
			}
			$this->_attributes = array_merge($this->_attributes, $attrs);
		}
		return $this;
	}

	/**
	 * Returns all currently defined html attributes
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->_attributes;
	}

	/**
	 * @inheritDoc
	 */
	public static $autoIdPrefix = 'neonForm';

	/**
	 * If this form is a sub form and repeater is true
	 * then this is a repeatable form instance
	 * @var bool
	 */
	public $repeater = false;

	// protected props configurable via getters and setter
	protected $_template = 'form.tpl';

	protected $_hint;
	protected $_label;
	protected $_classLabel;
	protected $_dataKey = null;

	/**
	 * Constructor.
	 * @inheritdoc
	 * @param array|string $config  - name value pairs that will be used to initialize the object properties or a string name
	 * if a string is provided then it is used as the name property
	 * @param array $config additional configuration
	 */
	public function __construct($name=[], array $config=[])
	{
		// The $name field can also be a configuration array
		if (is_array($name)) {
			$config = $name;
		}
		// if a name has been specified then add this to the config
		elseif (is_string($name)) {
			$config['name'] = $name;
		}
		// call the parent object construct function - this will configure the form object with the config properties
		parent::__construct($config);
	}

	/**
	 * initialise the form
	 * @return void
	 */
	public function init()
	{
	}

	/**
	 * @return string
	 */
	public function formName()
	{
		return $this->getInputName();
	}

	/**
	 * Set the form action url
	 * @param array|string $action - suitable format for Url::to
	 */
	public function setAction($action)
	{
		$this->_attributes['action'] = $action;
	}

	/**
	 * Return the action url - should be in format suitable for Url::to
	 * @return array|string
	 */
	public function getAction()
	{
		return isset($this->_attributes['action']) ? $this->_attributes['action'] : '';
	}

	/**
	 * @inheritdoc
	 */
	public function registerScripts($view=null, $mount=true)
	{
		$view = ($view === null) ? $this->getView() : $view;
		FormAsset::register($view);
		foreach ($this->getFields() as $field)
			$field->registerScripts($view);
		if ($this->isRootForm()) {
			if ($mount)
				$view->registerJs('(new Vue()).$mount("#' . $this->id . '");', $view::POS_END, $this->id . 'vue');
		}
	}

	/**
	 * run the widget - essentially render the form
	 * This follows the standard widget convention of calling run.
	 * Render can not be used as this is reserved in Yii widgets for rendering view template files
	 * @return string
	 */
	public function run()
	{
		$this->registerScripts($this->getView());
		return $this->renderFormHtml();
	}

	/**
	 * Render the forms html output
	 * @return string
	 */
	public function renderFormHtml()
	{
		$data = ['form' => $this, 'token' => $this->getObjectToken()];
		if ($this->isSubForm()) {
			$tpl = $this->repeater ? 'repeater-instance.tpl' : $this->_template;
			$data['parentFormId'] = $this->getForm()->getId();
			return $this->render($tpl, $data);
		}
		return $this->render($this->_template, $data);
	}

// region: Validation and error specific functions
// ============================================================================

	/**
	 * Performs the data validation.
	 *
	 * @See \yii\base\Model
	 *
	 * @param array   $names list of attribute names that should be validated.
	 * If this parameter is empty, it means any attribute listed in the applicable
	 * validation rules should be validated.
	 * @param boolean $clearErrors whether to call [[clearErrors()]] before performing validation
	 *
	 * @return boolean whether the validation is successful without any error.
	 * @throws UnknownPropertyException - if a validator attempts to access an unknown property
	 */
	public function validate($names = null, $clearErrors = true)
	{
		$valid = true;
		foreach($this->getFields($names) as $key => $field) {
			$valid = $field->validate() && $valid;
		}
		// if validation has run - you will want to see it when the form is loaded
		$this->isSubmitted = true;
		return $valid;
	}

	/**
	 * Validates one or several models and returns an error message array indexed by the attribute IDs.
	 * This is a helper method that simplifies the way of writing AJAX validation code.
	 * For example, you may use the following code in a controller action to respond
	 * to an AJAX validation request:
	 * ```php
	 * $model = new Post;
	 * $model->load($_POST);
	 * if (Yii::$app->request->isAjax) {
	 *     Yii::$app->response->format = Response::FORMAT_JSON;
	 *     return ActiveForm::validate($model);
	 * }
	 * // ... respond to non-AJAX request ...
	 * ```
	 * To validate multiple models, simply pass each model as a parameter to this method, like
	 * the following:
	 * ```php
	 * ActiveForm::validate($model1, $model2, ...);
	 * ```
	 * If this parameter is empty, it means any attribute listed in the applicable
	 * validation rules should be validated.
	 * When this method is used to validate multiple models, this parameter will be interpreted
	 * as a model.
	 * @throws UnknownPropertyException - if a validator attempts to access an unknown property
	 * @return array the error message array indexed by the attribute IDs.
	 */
	public function getValidationData()
	{
		$this->validate();
		$result = $this->_getFormErrors($this);
		return $result;
	}

	/**
	 * Get all form errors indexed by field id
	 *
	 * @param \neon\core\form\Form $form - A form object
	 * @param array $errors
	 * @return array
	 */
	protected function _getFormErrors($form, &$errors=[])
	{
		foreach ($form->getFields() as $field) {
			if ($field->isForm()) {
				$this->_getFormErrors($field, $errors);
			} else {
				$errors[$field->getIdPath()] = $field->getErrors();
			}
		}
		return $errors;
	}

	/**
	 * Store an array of errors for this object
	 * @var array
	 */
	private $_errors = [];

	/**
	 * Get all errors from this form and any fields
	 * @return array
	 */
	public function getErrors()
	{
		$errors = [];
		foreach($this->getFields() as $name => $field) {
			if ($field->hasError())
				$errors[$name] = $field->getErrors();
		}
		return array_merge($this->_errors, $errors);
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
	 * @alias of hasError()
	 * Required by some Yii validation plugins
	 * @return boolean
	 */
	public function hasErrors()
	{
		return $this->hasError();
	}

	/**
	 * Adds a new error to this form.
	 *
	 * This method is dual purposed so that errors can be set directly on the form
	 * or, if during validation of a field that needed to pass in the form model,
	 * we can add the error to that field instead. This is a bit of a hack but
	 * required because of how validateAttribute works in Yii Validators.
	 *
	 * @param string $fieldNameOrError  if $error is not null, then this is
	 *   the form field name, otherwise it is the error
	 * @param string $error  if this is not null then it is the error
	 * @return void
	 */
	public function addError($fieldNameOrError='', $error=null)
	{
		if ($error === null)
			$this->_errors[] = $fieldNameOrError;
		else
			$this->getField($fieldNameOrError)->addError($error);
	}

// ============================================================================
// endregion

	/**
	 * Gets a field label - this function matches the structure of yii models so the form can
	 * be used as the model input for Yii validation and render functions
	 * @param string $attribute - the field name
	 * @return String
	 */
	public function getAttributeLabel($attribute)
	{
		return $this->getField($attribute)->getLabel();
	}

	/**
	 * Shortcut function to load in post data and validate the form
	 * @return bool
	 */
	public function validatePost()
	{
		if ($this->getRequest()->getIsPost()) {
			$this->load();
			return $this->validate();
		}
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function getViewPath()
	{
		return __DIR__ . DIRECTORY_SEPARATOR . 'views';
	}

	/**
	 * Generate a name that is unique in the context of the other fields in the form
	 * for e.g. if you have one field with a name of "field" in your form and you call this function with the same name of
	 * "field" it will return "field_1" and so on to ensure the name is unique
	 * @param string $name the name of the field - it will be appended with a incremented to number to enforce uniqueness
	 *
	 * @return string the unique name
	 */
	public function generateUniqueFieldName($name)
	{
		$i = 1;
		while ($this->hasField($name)) {
			if (preg_match('/.*(_[0-9]+)/', $name, $matches)) {
				$name = str_replace($matches[1], '_' . $i++, $name);
			} else {
				$name .= '_' . $i++;
			}
		}
		return $name;
	}

// region: Field Management
// ============================================================================
	/**
	 * @var array of IField Objects
	 */
	protected $_fields = [];

	/**
	 * Get field by its name
	 *
	 * @param string $name the name of the field
	 * @throws InvalidCallException if no field exists with name
	 * @return fields\Field|Form
	 */
	public function getField($name)
	{
		if (!isset($this->_fields[$name])) {
			throw new InvalidCallException("No field with the name '$name' exists in the form");
		}
		return $this->_fields[$name];
	}

	/**
	 * Get a field by its path
	 * @param string $path - '.' delimited path to field - the first item should be the current form name or id
	 * @return IField|Form|null
	 * @throws \Exception if the first part of the path is not the current form
	 */
	public function getFieldByPath($path)
	{
		// the root path bit can be the the name or the id of the form
		// all children of the root will use the name property
		// multiple versions of the same form can exist on a page
		// in these scenarios the forms each can have different root form ids.
		$bits = explode('.', $path);
		if (!($bits[0] === $this->name || ($this->isRootForm() && $bits[0] === $this->id)))
			throw new \Exception("The first part of the path should be the form name or the id if this is a root form. The forms name and id is: name={$this->name}, id={$this->id}. The path you used was: path=$path.");
		// remove first item and reindex array : note unset does not update array indexes
		array_splice($bits, 0, 1);
		$field = $this->getField($bits[0]);
		if ($field->isForm()) {
			// if this is a form then delegate to the sub form to find its children.
			// this is important for repeaters that manage a collection of forms with unique ids
			return $field->getFieldByPath(implode('.', $bits));
		}
		return $field;
	}

	/**
	 * Returns boolean whether the field exists
	 *
	 * @param string $name
	 * @return bool
	 */
	public function hasField($name)
	{
		return isset($this->_fields[$name]);
	}

	/**
	 * Return an array of form field objects indexed by the field name
	 *
	 * @param array $names | null provide an array of names to return array of only the specified fields default is null
	 * which will return all fields
	 * @throws InvalidCallException if a name key specified in the $names array does not exist as a field
	 * @return \neon\core\form\fields\Field[] [name => field object]
	 */
	public function getFields($names=null)
	{
		$this->orderFields();
		if ($names === null)
			return $this->_fields;

		return Arr::only($this->_fields, $names);
	}

	/**
	 * Get an array of child sub forms
	 *
	 * @return \neon\core\form\Form[] indexed by form name
	 */
	public function getSubForms()
	{
		$ret = [];
		foreach ($this->_fields as $key => $field) {
			if ($field instanceof Form) {
				$ret[$key] = $field;
			}
		}
		return $ret;
	}

	/**
	 * Get a sub form
	 * @param string $name
	 * @throws \Exception if no subform exists with the name specified
	 * @return Form
	 */
	public function getSubForm($name)
	{
		if (! $this->hasField($name)) {
			throw new \Exception("No sub form exists with the name '$name'");
		}
		return $this->getField($name);
	}

	/**
	 * Order the fields by their `order` property values
	 */
	public function orderFields()
	{
		Arr::multisort($this->_fields, 'order');
	}

	/**
	 * Takes a configuration array which is an array of field configuration arrays
	 * This would look something like this:
	 *
	 * ```php
	 *  [
	 *      [
	 *           'class' => '\neon\core\form\fields\Text'
	 *           'name' => 'first_name'
	 *           // 'validators' => ...
	 *           // ... (other config options)
	 *      ],
	 *      [
	 *           'class' => '\neon\core\form\fields\Text'
	 *           'name' => 'last_name'
	 *           "validators" : [
	 *               ["string", ["max" : 10]]
	 *           ]
	 *      ],
	 *      'dataKey' => [
	 *           'class' => '\neon\core\form\fields\Text',
	 *           'label' => 'Thing'
	 *      ]
	 *  ]
	 * ```
	 *
	 * **Note** that if a key is defined for a field and no name or dataKey property exists in the config array
	 * then the key will be used as the ```dataKey``` and the ```name``` property for the field.
	 *
	 * Therefore given a config array of:
	 *
	 * ```php
	 *
	 * [
	 *     'myField' => [
	 *          'class' => 'neon\core\form\fields\Text'
	 *     ]
	 * ]
	 * ```
	 *
	 * Will add a field with the config:
	 *
	 * ```php
	 * [
	 *      'class' => 'neon\core\form\fields\Text',
	 *      'name' => 'myField',
	 *      'dataKey' => 'myField'
	 * ]
	 * ```
	 * @param array $config
	 * @return $this - make chainable method
	 */
	public function setFields($config)
	{
		foreach ($config as $key => $fieldConfig) {
			// if a key is defined in the array then assume we want this to become the name and the data key for the field element
			// this will be ignored if 'name' or 'dataKey' is explicitly defined in $fieldConfig
			if (is_string($key)) {
				$fieldConfig = Arr::merge(['name' => $key, 'dataKey' => $key], $fieldConfig);
			}
			$this->add($fieldConfig);
		}
		return $this;
	}

	/**
	 * Remove a field
	 * @param string $name
	 */
	public function removeField($name)
	{
		unset($this->_fields[$name]);
	}

	/**
	 * A generic add function to add fields to a form
	 *
	 * ```PHP
	 * $field = new \neon\core\form\fields\Email(['name' => 'email']);
	 * $form->add($field);
	 * ```
	 *
	 * @param array|IField $field a configuration array or an IField object
	 * @param array $defaults (optional) - This is ignored if the $field parameter is an object otherwise
	 *   it represents defaults for the configuration array
	 * @param boolean $overwrite (optional) - whether to overwrite an existing field of the same name defaults to false
	 * @throws exceptions\UnknownFieldClass - if the field class can not be created
	 * @return fields\Field
	 */
	public function add($field, $defaults=[], $overwrite=false)
	{
		$object = $this->makeField($field, $defaults);
		return $this->_addField($object, $overwrite);
	}

	/**
	 * Add a form field to this form
	 * @param  IField  $object instance of a form Field
	 * @param boolean $overwrite (optional) - Whether to overwrite any existing fields with the same name default is false
	 *
	 * @return \neon\core\form\fields\Field
	 */
	protected function _addField(IField $object, $overwrite=false)
	{
		// pass the field a reference to its parent form
		$object->setForm($this);
		if ($object->getName() == '') {
			throw new \InvalidArgumentException('A field object must have a name set before adding it to the form. You tried to add ' . print_r($object->toArray(), true));
		}
		if ($object instanceof Field || $object instanceof Form) {
			// the field object seems to be valid so before we add it we just check this is not a conflict with an existing field
			// if overwrite is true then we overwrite with the new field
			if (!$overwrite && array_key_exists($object->getName(), $this->_fields)) {
				throw new \InvalidArgumentException('The field with the key "'.$object->getName().'" already exists in the forms field array');
			}
			// add this form as the parent form reference
			$this->_fields[$object->getName()] = $object;
		} else {
			throw new \InvalidArgumentException('The $object parameter was not a valid \neon\core\form\Field or \neon\core\form\Form object "' . print_r($object, true) . '" given');
		}
		return $object;
	}

// endregion

// region: Render functions
// ============================================================================

	/**
	 * render the form header html
	 * @return string
    */
	public function renderHeader()
	{
		$options = $this->toArray();
		unset($options['fields']);
		return "<neon-core-form-form v-bind='{$this->jsonEncode($options)}' id='{$this->id}'>";
	}

	/**
	 * Render the csrf validation token prevent - cross site request forgery
	 * @return string
	 */
	public function renderCsrfField()
	{
		return '<input type="hidden" name="'.$this->getRequest()->csrfParam.'" value="' . $this->getRequest()->getCsrfToken() . '" />';
	}

	/**
	 * Get the CSRF param string - this is the parameter name that the request expects the csrf token to be in
	 * @see $this->getCsrfToken()
	 * @return string
	 * @deprecated use $this->getRequest()->csrfParam
	 */
	public function getCsrfParam()
	{
		// this method assumes the form has been created via a request
		// which is not always true.
		try {
			return $this->getRequest()->csrfParam;
		} catch (\Exception $e) {
			return null;
		}
	}

	/**
	 * Get the csrf request token string
	 * @see $this->getCsrfParam()
	 * @return string
	 * @deprecated use $this->getRequest()->getCsrfToken
	 */
	public function getCsrfToken()
	{
		// this method assumes the form has been created via a request
		// which is not always true.
		try {
			return $this->getRequest()->csrfToken;
		} catch (\Exception $e) {
			return null;
		}
	}

	/**
	 * render the form footer html
	 * @return string
	 */
	public function renderFooter()
	{
		$this->registerScripts($this->getView());
		return '</neon-core-form-form>';
	}

// endregion


	/**
	 * Check to see if there is request data for this form
	 * If there is then this will return that data
	 * @throws \yii\base\InvalidConfigException getBodyParams may throw this error if a registered parser does not implement the [[RequestParserInterface]].
	 * @return bool  true if there is request data
	 */
	public function hasRequestData()
	{
		if ($this->shouldProcess()) {
			$params = $this->getRequest()->getBodyParams();
			return isset($params[$this->getName()]);
		}
		return false;
	}

	/**
	 * Get hold of the raw unprocessed form request data. To get the
	 * actual form data use @see getData
	 * @param $key  if passed in, select data[$key]
	 * @throws \yii\base\InvalidConfigException getBodyParams may throw this error if a registered parser does not implement the [[RequestParserInterface]].
	 * @return array  the request data
	 */
	public function getRawRequestData($key=null)
	{
		$params = $this->getRequest()->getBodyParams();
		$data = isset($params[$this->getName()]) ? $params[$this->getName()] : [];
		if (!empty($key)) {
			return isset($data[$key]) ? $data[$key] : [];
		}
		return $data;
	}

	/**
	 * Load data into the form
	 * typical example ```$form->load(neon()->request->post())```
	 * The passed in array can be indexed by the form name. e.g. [[FormName] => ['field_1' => 'val', 'field2' => 'val']]
	 * Or simply be a list of fields the load function checks to see if the key is its form name or a name of one of its
	 * fields.
	 *
	 * Load is really only a short cut method when you do not want to do the following:
	 *
	 * ```php
	 * // if you call load without parameters
	 * $this->load(); // This will look for the form name as a post parameter key which is equivalent to the following:
	 * $this->setValue(neon()->request->post('my_form_name'));
	 * // equivalent of:
	 * $this->load(neon()->request->post('my_form_name'));
	 * ```
	 *
	 * @param array|null $data  if null will attempt to load the form from the current request data
	 * @throws \Exception - if request object is configured incorrectly
	 * @return $this
	 */
	public function load($data=[])
	{
		if (empty($data) && $this->hasRequestData()) {
			$data = $this->getRawRequestData();
		}
		$this->setValue($data);
		return $this;
	}

	/**
	 * Load form data from the system representations - these will be data in
	 * the same format as is generated by the getData() function
	 *
	 * @param array $data
	 */
	public function loadFromDb($data)
	{
		foreach ($data as $name => $value) {
			if ($this->hasField($name)) {
				$this->getField($name)->setValueFromDb($value);
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getData()
	{
		$data = [];
		foreach ($this->getFields() as $field) {
			if ($field->deleted) continue;
			if ($field->getIsInput())
				$data[$field->getDataKey()] = $field->getData();
		}
		return $data;
	}

	/**
	 * Gets a representation of the forms data by its value display
	 * Typically this will use the human readable output for the fields of the form
	 * @return array
	 */
	public function getDataDisplay()
	{
		$data = [];
		foreach ($this->getFields() as $field) {
			if ($field->getIsInput())
				$data[$field->getName()] = $field->getValueDisplay();
		}
		return $data;
	}

// region: implement the IField interface - this allows forms to be used as a field within a parent form
// =================================================================================================================

	/**
	 *  @inheritdoc
	 */
	public function getDataKey()
	{
		if ($this->_dataKey !== null)
			return $this->_dataKey;
		return $this->getName();
	}

	/**
	 * @inheritdoc
	 */
	public function setDataKey($key)
	{
		$this->_dataKey = $key;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		if ($this->_name == null) {
			$reflector = new ReflectionClass($this);
			$this->_name = $reflector->getShortName();
		}
		return $this->_name;
	}

	/**
	 * Root forms always require an id
	 * @return String
	 */
	public function getId($autoGenerate=false)
	{
		if ($this->_id)
			return $this->_id;
		return $this->getName();
	}

	/**
	 * @inheritDoc
	 */
	public function setValue($value)
	{
		foreach ($value as $name => $val) {
			if ($this->hasField($name)) {
				$this->getField($name)->setValue($val);
			}
		}
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function setValueFromDb($value)
	{
		$this->loadFromDb($value);
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getValue()
	{
		$value = [];
		foreach ($this->getFields() as $field) {
			if (!$field->deleted) {
				if ($field->getIsInput())
					$value[$field->getDataKey()] = $field->getValue();
			}
		}
		return $value;
	}

	/**
	 * Get the hint message for this field
	 * @return string
	 */
	public function getHint()
	{
		return $this->_hint;
	}

	/**
	 * Set the hint for this field
	 *
	 * @param string $hint
	 *
	 * @return $this
	 */
	public function setHint($hint)
	{
		$this->_hint = $hint;
		return $this;
	}

	/**
	 * Set the label for the field
	 *
	 * @param string $label
	 * @return $this is a chainable method
	 */
	public function setLabel($label)
	{
		$this->_label = $label;
		return $this;
	}

	/**
	 * Return the label for this field
	 * @return $this is a chainable method
	 */
	public function getLabel()
	{
		return $this->_label;
	}

	/**
	 * Set the classLabel for the field
	 *  a user friendly name for the field class
	 * @param string $label
	 * @return $this is a chainable method
	 */
	public function setClassLabel($label)
	{
		$this->_classLabel = $label;
		return $this;
	}

	/**
	 * Return the class label for this field
	 * @return $this is a chainable method
	 */
	public function getClassLabel()
	{
		return $this->_classLabel;
	}

	/**
	 * Whether this is a form
	 * @return bool
	 */
	public function isForm()
	{
		return true;
	}

	/**
	 * @inheritdoc - IField implementation,
	 * Possibly applicable to the form - perhaps if set the form will complain unless all its children are set??
	 */
	public function setRequired($required=true)
	{}
// endregion

	/**
	 * Helper function to reduce controller code
	 * if the form supports ajax validation this will return
	 * the appropriate validation array data and set the response object to process as json
	 * note the result of this function should be returned by the controller action.
	 *
	 * Inside the controller action:
	 *
	 * ~~~php
	 * if (neon()->request->isAjax) {
	 *     return $form->ajaxValidation()
	 * }
	 * ~~~
	 *
	 * Steve: Personally I dislike this code - I dislike the current yii ajax form validation clogging up my controller actions
	 * The ajax validation is so tightly coupled to the Form and the JQuery yiiActiveForm plugin that the controller
	 * just gets in the way - its only job is to know its ajax - perhaps setting up a form object convention so we can
	 * have a more generic ajax validation route for forms would remove this layer of cruft from every single controller action
	 * @throws UnknownPropertyException - if a validator attempts to access an unknown property
	 *
	 * @return array
	 */
	public function ajaxValidation()
	{
		$this->load();
		$this->validate();
		return $this->getValidationData();
	}

	/**
	 * Process the form in the context of http request
	 * The form class knows generally how it sends data via its method property
	 * Therefore process the data in the context of the form - load the data and validate against the form fields
	 * @return boolean true if validate if data is loaded and validates successfully
	 */
	public function processRequest()
	{
		if ($this->hasRequestData()) {
			$this->load();
			return $this->validate();
		}
		return false;
	}

	/**
	 * Whether the form should process the request.
	 * Essentially if the current request method matches the form method.
	 * Typically if the request is POST and the form method is POST this function will return true.
	 * @return bool
	 */
	public function shouldProcess()
	{
		return $this->getRequest()->getMethod() == strtoupper($this->method);
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->run();
	}

	/**
	 * Array serialize
	 */
	public function getProperties()
	{
		$props = [
			'class', 'id', 'name', 'enableAjaxValidation','enableAjaxSubmission',
			'label', 'hint', 'inline', 'visible', 'order', 'action', 'readOnly', 'printOnly', 'validationUrl',
			'attributes', 'fields', 'objectToken', 'isSubmitted', 'errors'
		];
		if ($this->isRootForm()) {
			$props[] = 'csrfParam';
			$props[] = 'csrfToken';
		}
		return $props;
	}

	/**
	 * Get the full class name of this class
	 * @return string
	 */
	public function getClass()
	{
		return get_class($this);
	}

	/**
	 * Return the rendered output of the form
	 * @return string
	 */
	public function getValueDisplay($context='')
	{
		return $this->run();
	}

	/* =========================================================================
	 * Creation and Extraction of forms by Definition
	 * =========================================================================
	 *
	 * The form definition is defined partially by the form and partially
	 * by the systems that will deal with storing this information
	 *
	 * =========================================================================
	 */

	/**
	 * Get the definition of this form suitable for use elsewhere.
	 * This is not the same as getting the toArray version of this form for
	 * the following reasons:
	 *
	 * 1. there are additional values that whilst they could be extracted out from
	 * the toArray data elsewhere puts the responsibility for knowing how to in
	 * the wrong place (it forces data scrapping). A change in definition here
	 * should not break code elsewhere.
	 *
	 * 2. there are irrelevant fields on the form that should be removed. These
	 * are any that are to do with security between client and server but which
	 * don't change the meaning of the form. Only those values that are properly
	 * part of the form definition should be returned.
	 */
	public function exportDefinition()
	{
		$definition = $this->toArray();
		// remove any irrelevant fields or ones that will be created separately
		unset($definition['csrfParam']);
		unset($definition['csrfToken']);
		unset($definition['fields']);
		unset($definition['errors']);

		$formDefinition = [
			'name' => $this->getName(),
			'label' => $this->getLabel(),
			'description' => $this->getHint(),
			'definition' => $definition
		];
		$fields = [];
		foreach ($this->getFields() as $key => $field) {
			$fieldDefinition = $field->exportDefinition();

			if (! is_null($field->ddsDataType) && ! is_null($fieldDefinition) )
				$fields[$key] = $fieldDefinition;
		}
		$formDefinition['definition']['fields'] = $fields;
		return $formDefinition;
	}

	// TODO: remove the need for this function!
	public function getDdsName()
	{
		return preg_replace("/[^a-z0-9_]/", '', strtolower(preg_replace("/ +/", '_', $this->getName())));
	}

// endregion

// region: ArrayAccess implementation methods
// ============================================================================

	/**
	 * @inheritDoc
	 */
	public function offsetExists($offset): bool
	{
		return $this->hasField($offset);
	}

	/**
	 * @inheritDoc
	 */
	public function offsetGet($offset): IField
	{
		return $this->getField($offset);
	}

	/**
	 * @inheritDoc
	 */
	public function offsetSet($offset, $value): void
	{
		$object = $this->makeField($value);
		$object->setName($offset);
		$this->add($object, [], true);
	}

	/**
	 * @inheritDoc
	 */
	public function offsetUnset($offset): void
	{
		$this->removeField($offset);
	}
// endregion

// region: Filter Form
// ============================================================================
	/**
	 * Asks each field member for a suitable field to represent its search data.  Then returns this as a form object.
	 * Some member fields will have different fields to represent them in a search form.
	 * For example a Date field may return a DateRange field in order to search.
	 * A checkbox may return a drop down list of search choices e.g. ("on" | "off" | "all")
	 *
	 * @param array $config
	 *
	 * @return Form
	 */
	public function getFilterForm($config=[])
	{
		$searchForm = new Form($config);
		foreach($this->getFields() as $field) {
			$filterField = $field->getFilterField();

			if ($filterField !== false) {
				$searchForm->add($field->getFilterField(), ['name' => $field->getName()]);
			}
		}
		return $searchForm;
	}

	/**
	 * @inheritDoc
	 */
	public function getFilterField()
	{
		return $this->getFilterForm();
	}

	/**
	 * @inheritDoc
	 */
	public function processAsFilter(IQuery $query, $searchData = null)
	{
		$searchData = ($searchData === null) ? $this->getValue() : $searchData;
		foreach($this->getFields() as $key => $field) {
			if (isset($searchData[$key])) {
				$field->processAsFilter($query, $searchData[$key]);
			}
		}
		return $query;
	}
// endregion

	/**
	 * @var \neon\core\web\Request
	 */
	protected $_request;

	/**
	 * Returns the request object
	 * @return \neon\core\web\Request
	 */
	public function getRequest()
	{
		if ($this->_request === null)
			$this->_request = neon()->request;
		return $this->_request;
	}

	/**
	 * Set the request object the form should use
	 *
	 * @param \neon\core\web\Request $request
	 */
	public function setRequest($request)
	{
		$this->_request = $request;
	}

	/**
	 * Reset all the values within the form
	 */
	public function reset()
	{
		foreach($this->getFields() as $field)
			$field->reset();
	}

	/**
	 * @inheritdoc
	 */
	public function getIsInput()
	{
		return true;
	}

	/**
	 * Generate fake data
	 * @return array
	 */
	public function fake()
	{
		$fake = [];
		foreach($this->getFields() as $key => $field) {
			$val = $field->fake();
			if (empty($val)) continue;
			$fake[$key] = $val;
		}
		return $fake;
	}

	/**
	 * @deprecated should be using phoebe this should be replaced with using the loadFromDefinition via phoebe
	 * @see Deprecated::ddsLoadFrom
	 */
	public function ddsLoadFrom($classType, $includeDeleted=false)
	{
		return Deprecated::ddsLoadFrom($this, $classType, $includeDeleted);
	}

	/**
	 * @deprecated - should be using phoebe
	 * @see Deprecated::ddsSaveDefinition
	 */
	public function ddsSaveDefinition($classType=null, $ddsMembers=null)
	{
		Deprecated::ddsSaveDefinition($this, $classType, $ddsMembers);
	}

	/**
	 * @deprecated should be using phoebe
	 * @see Deprecated::ddsAddField
	 */
	public function ddsAddField($member)
	{
		Deprecated::ddsAddField($this, $member);
	}

	/**
	 * Generate a token pointing to a serialised version of the form
	 * This can then be unserialised using Hash::getObjectFromToken($token);
	 * We only ever seralize the root form token - todo - seems to break properties trait
	 * @return string
	 * @throws \ReflectionException
	 */
	public function getObjectToken()
	{
		return Hash::setObjectToToken($this);
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return false;
	}

	/**
	 * Alias of getField
	 * @param $name
	 * @return Field|Form
	 */
	public function get($name)
	{
		return $this->getField($name);
	}
}
