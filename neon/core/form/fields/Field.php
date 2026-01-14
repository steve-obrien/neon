<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;


use InvalidArgumentException;
use Neon;
use neon\core\form\Deprecated;
use neon\core\form\exceptions\InvalidNameFormat;
use neon\core\form\Form;
use neon\core\form\FormField;
use neon\core\form\interfaces\IField;
use neon\core\form\Model;
use neon\core\grid\query\IQuery;
use neon\core\helpers\Arr;
use neon\core\helpers\Html;
use neon\core\traits\PropertiesTrait;
use yii\base\NotSupportedException;
use yii\base\UnknownPropertyException;

/**
 * Class Field
 * @package neon\core\forms
 * @property string $name
 * @property Model $model
 * @property array $options
 * @property string $hint
 * @property string $label
 * @property string $value
 * @property Form $form
 */
class Field extends FormField implements IField
{
	use PropertiesTrait;

	/**
	 * @var array the default options for the input tags. The parameter passed to individual input methods
	 * (e.g. [[textInput()]]) will be merged with this property when rendering the input tag.
	 *
	 * If you set a custom `id` for the input element, you may need to adjust the [[$selectors]] accordingly.
	 *
	 * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
	 */
	public $inputOptions = [];

	public $readOnly = false;

	public $disabled = false;

	public $printOnly = false;

	public $mapField = false;

	public $showIf = [];

	public $placeholderLabel = false;

	/**
	 * Get the parent form that this item belongs to
	 * @return Form
	 */
	public function getForm()
	{
		$form = parent::getForm();
		if ($form === null) {
			throw new InvalidArgumentException('The field must have a form object defined. This happens when the field is added to a form object');
		}
		return $form;
	}

	/**
	 * @var array the default options for the error tags. The parameter passed to [[error()]] will be
	 * merged with this property when rendering the error tag.
	 * The following special options are recognized:
	 *
	 * - tag: the tag name of the container element. Defaults to "div".
	 * - encode: whether to encode the error output. Defaults to true.
	 *
	 * If you set a custom `id` for the error element, you may need to adjust the [[$selectors]] accordingly.
	 *
	 * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
	 */
	public $errorOptions = ['class' => 'help-block', 'tag' => 'span'];
	/**
	 * @var array the default options for the label tags. The parameter passed to [[label()]] will be
	 * merged with this property when rendering the label tag.
	 * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
	 */
	public $labelAttributes = ['class' => 'control-label from-group__label'];
	/**
	 * @var array the default options for the hint tags. The parameter passed to [[hint()]] will be
	 * merged with this property when rendering the hint tag.
	 * The following special options are recognized:
	 *
	 * - tag: the tag name of the container element. Defaults to "div".
	 *
	 * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
	 */
	public $hintOptions = ['class' => 'hint-block hint-top'];
	/**
	 * @var bool whether to enable client-side data validation.
	 * If not set, it will take the value of [[ActiveForm::enableClientValidation]].
	 */
	public $enableClientValidation;
	/**
	 * @var bool whether to enable AJAX-based data validation.
	 * If not set, it will take the value of [[ActiveForm::enableAjaxValidation]].
	 */
	public $enableAjaxValidation;
	/**
	 * @var bool whether to enable AJAX-based form submission
	 */
	public $enableAjaxSubmission;
	/**
	 * @var bool whether to perform validation when the value of the input field is changed.
	 * If not set, it will take the value of [[ActiveForm::validateOnChange]].
	 */
	public $validateOnChange;
	/**
	 * @var bool whether to perform validation when the input field loses focus.
	 * If not set, it will take the value of [[ActiveForm::validateOnBlur]].
	 */
	public $validateOnBlur;
	/**
	 * @var bool whether to perform validation while the user is typing in the input field.
	 * If not set, it will take the value of [[ActiveForm::validateOnType]].
	 * @see validationDelay
	 */
	public $validateOnType;
	/**
	 * @var int number of milliseconds that the validation should be delayed when the user types in the field
	 * and [[validateOnType]] is set true.
	 * If not set, it will take the value of [[ActiveForm::validationDelay]].
	 */
	public $validationDelay;
	/**
	 * @var array the jQuery selectors for selecting the container, input and error tags.
	 * The array keys should be "container", "input", and/or "error", and the array values
	 * are the corresponding selectors. For example, `['input' => '#my-input']`.
	 *
	 * The container selector is used under the context of the form, while the input and the error
	 * selectors are used under the context of the container.
	 *
	 * You normally do not need to set this property as the default selectors should work well for most cases.
	 */
	public $selectors = [];
	/**
	 * @var array different parts of the field (e.g. input, label). This will be used together with
	 * [[template]] to generate the final field HTML code. The keys are the token names in [[template]],
	 * while the values are the corresponding HTML code. Valid tokens include `{input}`, `{label}` and `{error}`.
	 * Note that you normally don't need to access this property directly as
	 * it is maintained by various methods of this class.
	 */
	public $parts = [];

	protected $_value = null;
	protected $_sanitisedValue = null;
	protected $_name;
	protected $_hint;
	protected $_label = null;
	protected $_classLabel;

	/**
	 * By default the form fields will strip tags before setting the fields value
	 * you can override this property in child Field classes to allow specific tags
	 *
	 * @var string|null - default null will remove all tags
	 * specify allowed tags for example set this variable to be "<br><a><strong>" to allow all br, a and strong tags
	 * ```php
	 * protected $allowableTags = "<br><a><strong>"
	 * ```
	 */
	protected $allowableTags = '<br><em><i><u><b><s><mark><strong>';

	/**
	 * @var string the key to use when outputting the fields data
	 */
	protected $_dataKey;

	/**
	 * Whether this field is required
	 * @var bool
	 */
	protected $_required = false;

	public $attributes = [];

	/**
	 * Field constructor.
	 * @param array|string $name
	 * @param array $config
	 * @throws InvalidNameFormat
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
		// -----
		// By this point $config should be the required minimum config
		// i.e. has a `name` property
		// -----
		if (isset($config['name']))
			$this->setName($config['name']);

		// Configure the field - the name property must be set by this point.
		// as many configuration options such as adding Validators and attaching
		// the file to the form use the name as an index
		if (!empty($config)) {
			Neon::configure($this, $config);
		}

		// check the Field has a sensible configuration
		$this->preInitCheck();

		$this->init();
	}

	/**
	 * Check the configuration and throw exceptions
	 * @throws InvalidArgumentException
	 */
	public function preInitCheck()
	{
		// check the Field has a sensible configuration
		if ($this->_name == null) {
			throw new InvalidArgumentException('You must set a name property for a form field');
		}
	}

	/**
	 * @inheritdoc
	 */
	public function init()
	{}

	/**
	 * @inheritdoc
	 * @return string
	 */
	public function run()
	{
		$this->registerScripts($this->getView());
		return Html::tag($this->getComponentName(), '', ['v-bind' => $this->toJson()]);
	}

	/**
	 * This function is old - before the form rendering was handled by vue
	 * The idea is that is returns just the input field allowing you within a template file
	 * to place labels and errors your self.
	 * Really you can do this by simply hiding elements in css rather than changing the forms
	 * output html.  Therefore this function could apply a class or have an option instructing vue
	 * to hide the labels and errors etc.
	 */
	public function getFieldHtml()
	{
		return $this->run();
	}

	/**
	 * Set the js/html component name
	 * defaults to the class name with dashes instead of backslashes
	 * @param string $value
	 * @return $this
	 */
	public function setComponentName($value)
	{
		$this->_componentName = $value;
		return $this;
	}

	/**
	 * Get the js/html component name for this field
	 * @return mixed|null
	 */
	public function getComponentName()
	{
		if ($this->_componentName === null)
			$this->_componentName = str_replace('\\', '-', get_class($this));
		return $this->_componentName;
	}

	/**
	 * @var string - component name
	 * @see setComponentName
	 * @see getComponentName
	 */
	protected $_componentName = null;

	/**
	 * @inheritdoc
	 */
	public function registerScripts($view)
	{
	}

	/**
	 * Used when outputting form data via the getData method. Sets the key in the output array for this fields data
	 * If not specified will return the fields name as the key for the data
	 * @see \neon\core\form\Form::getData()
	 * @return mixed
	 */
	public function getDataKey()
	{
		if ($this->_dataKey === null)
			return $this->getName();
		return $this->_dataKey;
	}

	/**
	 * Set the data key
	 *
	 * @param string $key
	 *
	 * @return $this is a chainable method
	 */
	public function setDataKey($key)
	{
		$this->_dataKey = $key;
		return $this;
	}

	/**
	 * This function represents the formatted data of the fields value
	 * the getValue represents the field value in the context of the input control and may have different formatting to
	 * the data wished to be returned by the form.
	 * Override this function in children Field type classes to change the output
	 * This function is called when the parent forms getData function is called
	 *
	 * @return mixed
	 */
	public function getData()
	{
		return $this->getValue();
	}

	/**
	 * @inheritdoc
	 */
	public function getValue()
	{
		// return sanitised data only
		if ($this->_sanitisedValue === null)
			$this->_sanitisedValue = $this->sanitiseInput($this->getUnsafeValue());
		return $this->_sanitisedValue;
	}

	/**
	 * Gets the raw, unsanitised value as posted by the user input
	 * @return null
	 */
	protected function getUnsafeValue()
	{
		return $this->_value;
	}

	/**
	 * @inheritdoc
	 */
	public function setValue($value)
	{
		$this->_value = $value;
		// note that data needs to be sanitised if we are going to use it
		$this->_sanitisedValue = null;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function setValueFromDb($value)
	{
		$this->setValue($value);
		// database data should have already been sanitised
		$this->_sanitisedValue = $value;
		return $this;
	}

	/**
	 * Sanitise the value
	 * @param string $value
	 * @return string sanitised
	 */
	protected function sanitiseInput($value)
	{
		if (empty($value))
			return $value;

		profile_begin('form sanitise');
		$sanitise = Html::sanitise($value, $this->allowableTags);
		profile_end('form sanitise');
		return $sanitise;
	}

	/**
	 * Purify the value - it is run through HTMLPurifier and then strip tags
	 * @param string $value
	 * @return string sanitised
	 */
	protected function purifyInput($value)
	{
		if (empty($value))
			return $value;

		profile_begin('form purify');
		$sanitise = Html::purify($value, $this->allowableTags);
		profile_end('form purify');
		return $sanitise;
	}

	/**
	 * @inheritdoc
	 */
	public function getHint()
	{
		return $this->_hint;
	}

	/**
	 * @inheritdoc
	 */
	public function setHint($hint)
	{
		$this->_hint = $hint;
		return $this;
	}

	/**
	 * Set the placeholder text for the field
	 *
	 * @param $placeholder
	 *
	 * @return $this
	 */
	public function setPlaceholder($placeholder)
	{
		$this->_placeholder = $placeholder;
		return $this;
	}

	protected $_placeholder = '';

	/**
	 * Get the string placeholder
	 * @return string
	 */
	public function getPlaceholder()
	{
		return $this->_placeholder;
	}

	/**
	 * Get an option from the internal options array
	 *
	 * @param string $name the option key to look up
	 * @param mixed  $default value to return if there is no options with the key of $name
	 *
	 * @return mixed
	 */
	public function getOption($name, $default = '')
	{
		return array_key_exists($name, $this->inputOptions) ? $this->inputOptions[$name] : $default;
	}

	/**
	 * @inheritdoc
	 */
	public function isForm()
	{
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function getLabel()
	{
		return $this->_label;
	}

	/**
	 * @inheritdoc
	 */
	public function setLabel($label)
	{
		$this->_label = $label;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getClassLabel()
	{
		return $this->_classLabel;
	}

	/**
	 * @inheritdoc
	 */
	public function setClassLabel($label)
	{
		$this->_classLabel = $label;
		return $this;
	}

	/**
	 * Get the html for the field label
	 *
	 * @return string will return an empty string if no label is specified
	 */
	public function getLabelHtml()
	{
		return $this->getLabel() ? Html::activeLabel($this->getForm(), $this->getName(), $this->labelAttributes ) : '';
	}

	/**
	 * Render the hint HTML
	 *
	 * @return string html
	 */
	public function getHintHtml()
	{
		$tag = Arr::remove($this->hintOptions, 'tag', 'div');
		return Html::tag($tag, $this->getHint(), $this->hintOptions);
	}

	/**
	 * @return string html representing the error
	 */
	public function getErrorHtml()
	{
		$error = $this->getFirstError();
		if ($error === '')
			return '';
		$options = $this->errorOptions;
		$tag = Arr::remove($options, 'tag', 'div');
		$encode = Arr::remove($options, 'encode', true);
		return Html::tag($tag, $encode ? Html::encode($error) : $error, $options);
	}

	/**
	 * @return array of options for the field, this is usually piped into the html render functions within the Html
	 * helper class which typically calle \neon\core\helpers\Html::renderTagAttributes()
	 */
	public function getOptions()
	{
		return $this->inputOptions;
	}

	/**
	 * set options for the field - will merge by the default options
	 *
	 * @param array   $options
	 * @param bool $replace by default options are merged if you wish to overwrite all options set this to true
	 *
	 * @return $this - is chainable method
	 */
	public function setOptions($options, $replace = false)
	{
		if (!$replace) {
			$options = Arr::merge($this->inputOptions, $options);
		}
		// TODO - consolidate on inputOptions or attributes?
		// inputOptions came before, but
		// attributes is the terminology used in Form.php
		$this->inputOptions = $options;
		$this->attributes = $options;
		return $this;
	}

	/**
	 * Returns the JS options for the field.
	 * Ugly Yii function
	 *
	 * @return array the JS options
	 */
	public function getClientOptions()
	{
		$options = [];
		// only get the options that are different from the default ones (set in yii.activeForm.js)
		return array_diff_assoc($options, [
			'validateOnChange' => true,
			'validateOnBlur' => true,
			'encodeError' => true,
			'error' => '.help-block',
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function validate()
	{
		$validators = $this->getValidators();
		if (count($validators)==0)
			return true;

		$isEmpty = empty($this->getValue());
		$hasErrors = $this->hasError();
		$form = $this->getForm();
		$attribute = $this->getName();
		$unsafeValue = $this->getUnsafeValue();

		foreach ($validators as $validator) {
			// see if we can skip this validator
			if (($validator->skipOnEmpty && $isEmpty) || ($validator->skipOnError && $hasErrors)) {
				continue;
			}

			// ok - now apply the validator
			try {
				if ($validator->when == null || call_user_func($validator->when, $form, $attribute)) {
					if (!$validator->validate($unsafeValue, $error)) {
						$this->addError($error);
					}
				}
			} catch (NotSupportedException $e) {
				try {
					// in this case we need to try validateAttribute which will set
					// the error back on this field via the form addError method
					$validator->validateAttribute($form, $attribute);
				} catch (UnknownPropertyException $e) {
					throw new UnknownPropertyException(
						'You are using a validator ' . get_class($validator) . ' that has a when clause or needs to use validateAttribute. '
						. 'This requires you to have <br><br>'
						. 'public function get' . ucfirst($attribute) . "<br>{ <br>&nbsp;&nbsp;return \$this->getField(&quot;$attribute&quot;)->getValue()); <br>}"
						. '<br><br>on the form. You may also need the equivalent set' . ucfirst($attribute) . ' method. <br>Error was ' . $e->getMessage());
				}
			}
		}

		return !$this->hasError();
	}

	/**
	 * @return array
	 * @deprecated use getProperties()
	 * @alias getProperties
	 */
	public function fields()
	{
		return $this->getProperties();
	}

	/**
	 * Return a list of properties that are serializable
	 * Each property should be accessible via $object->{propertyName}
	 * @return array
	 */
	public function getProperties()
	{
		return [
			'class', 'classLabel', 'id', 'name', 'label', 'hint', 'value',
			'required', 'dataKey', 'validators', 'placeholder', 'deleted', 'disabled',
			'order', 'mapField', 'errors', 'visible', 'inline', 'readOnly',
			'attributes', 'printOnly', 'showIf', 'placeholderLabel', 'enableClientValidation'
		];
	}

	/**
	 * @return array
	 */
	public function getPropertiesToSave()
	{
		$props = $this->toArray();
		Arr::remove($props, 'id');
		return $props;
	}

	/**
	 * Get the full class name
	 * @return string
	 */
	public function getClass()
	{
		return get_class($this);
	}

	/* ============================================================================================
	 * Phoebe and Daedalus specific functions
	 * ============================================================================================
	 */

	/**
	 * The DDS data type to store the value of the field
	 * @var string
	 */
	public $ddsDataType = 'textshort';

	/**
	 * Get the definition of a field for external storage
	 * Override this to remove anything that shouldn't make it
	 * as part of the definition
	 * @return array [
	 *   name - the field name
	 *   dataType - the daedalus data type
	 *   label - a label for the field
	 *   description - a user facing description for the field
	 *   choices - the set of associated choice (if any)
	 *   linkClass - the type of class linked to (if any)
	 *   mapField - whether or not this is a field used in maps
	 *   widget - a serialised definition of the widget
	 * ]
	 */
	public function exportDefinition()
	{
		$fieldDefinition = [
			'memberRef' => $this->getName(),
			'name' => $this->getName(),
			'dataType' => $this->ddsDataType,
			'label' => $this->getLabel(),
			'description' => $this->getHint(),
			'choices' => $this->extractChoices(),
			'linkClass' => $this->extractLinkClass(),
			'mapField' => $this->mapField,
			'deleted' => $this->deleted,
			// we must ensure that fields used for form state between requests 
			// are not permanently stored in the definition
			'definition' => $this->toArray(['errors'])
		];

		return $fieldDefinition;
	}

	/**
	 * extract the choice for a particular field.
	 * Override this if you need to provide that functionality
	 * @return null|array  an array of [key=>value] pairs
	 */
	protected function extractChoices()
	{
		return null;
	}

	/**
	 * extract the link class for a particular field
	 * Override this if you need to provide that functionality
	 * @return null|string  the link class
	 */
	protected function extractLinkClass()
	{
		return null;
	}

	// TODO: remove the need for this function!
	public function getDdsName()
	{
		return preg_replace('/[^a-z0-9_]/', '', strtolower(preg_replace('/ +/', '_', $this->getName())));
	}

// endregion

	/**
	 * Get a displayable representation of the fields value to be output to html
	 * @return string
	 */
	public function getValueDisplay($context='')
	{
		return Html::encode($this->getValue());
	}

// region: Filter Field
// ============================================================================

	/**
	 * @inheritdoc
	 */
	public function getFilterField()
	{
		$field = $this->toArray();
		return Arr::except($field, ['name', 'label', 'hint', 'placeholderLabel']);
	}

	/**
	 * @inheritdoc
	 */
	public function processAsFilter(IQuery $query, $searchData=null)
	{
		$searchData = ($searchData === null) ? $this->getValue() : $searchData;
		// Note: we can not use empty here
		// empty will return true for '0' meaning filtering for booleans
		// false passed over request (particularly for checkboxes) becomes '0'
		$query->where($this->getDataKey(), '=', $searchData);
	}
// endregion

	/**
	 * Whether this field represents a valid input field and will appear in data output
	 * Headings and section fields will not be inputs and therefore not clutter up data output
	 * @return bool
	 */
	public function getIsInput()
	{
		return !($this->ddsDataType === null);
	}

	/**
	 * Reset the form field's value
	 * @return $this;
	 */
	public function reset()
	{
		$this->_value = null;
		$this->_sanitisedValue = null;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		if (get_class($this) == get_called_class()) {
			return false;
		}
		return [
			'icon' => 'fa fa-cubes',
			'order' => 1000
		];
	}


	/**
	 * !!!!!!!!!!!!!!!!!!
	 * !!! DEPRECATED !!!
	 * !!!!!!!!!!!!!!!!!!
	 *
	 * Saves the current field object as a member of the DDS class
	 * The DDS class can be specified by the $classType param,
	 * if not defined it will use the parent form's' name as the classType
	 *
	 * @param string|null $classType
	 * @param array|null $ddsMembers if not specified it will look up the members of the classType
	 * @deprecated
	 */
	public function ddsSaveDefinition($classType=null, $ddsMembers=null)
	{
		Deprecated::ddsSaveDefinitionField($this, $classType, $ddsMembers);
	}
}
