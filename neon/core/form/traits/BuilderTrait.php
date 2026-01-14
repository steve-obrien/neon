<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 16/01/2018
 * @package neon
 */
namespace neon\core\form\traits;


use neon\core\form\exceptions\UnknownFieldClass;
use \neon\core\form\Form;
use \neon\core\form\fields;
use neon\core\form\FormRepeater;
use \neon\core\helpers\Arr;
use \neon\core\form\interfaces\IField;


trait BuilderTrait
{
	/**
	 * A convenience array to access known core field type class names.
	 * If the alias is not here but the lower case version of the file
	 * in \neon\core\form\fields, it will be found by other methods
	 * @var array
	 */
	protected $fieldAliases = [
		'checkbox' => '\neon\core\form\fields\Checkbox',
		'checklist' => '\neon\core\form\fields\CheckList',
		'choice' => '\neon\core\form\fields\Choice',
		'choicemultiple' => '\neon\core\form\fields\ChoiceMultiple',
		'currency' => '\neon\core\form\fields\Currency',
		'date' => '\neon\core\form\fields\Date',
		'daterange' => '\neon\core\form\fields\DateRange',
		'datetime' => '\neon\core\form\fields\DateTime',
		'email' => '\neon\core\form\fields\Email',
		'file' => '\neon\core\form\fields\File',
		'filebrowser' => '\neon\core\form\fields\FileBrowser',
		'filemultiple' => '\neon\core\form\fields\FileMultiple',
		'hidden' => '\neon\core\form\fields\Hidden',
		'ide' => '\neon\core\form\fields\Ide',
		'image' => '\neon\core\form\fields\Image',
		'integer' => '\neon\core\form\fields\Integer',
		'json' => '\neon\core\form\fields\Json',
		'link' => '\neon\core\form\fields\Link',
		'markdown' => '\neon\core\form\fields\Markdown',
		'password' => '\neon\core\form\fields\Password',
		'phone' => '\neon\core\form\fields\Phone',
		'radio' => '\neon\core\form\fields\Radio',
		'real' => '\neon\core\form\fields\Real',
		'section' => '\neon\core\form\fields\Section',
		'select' => '\neon\core\form\fields\Select',
		'selectchain' => '\neon\core\form\fields\SelectChain',
		'selectdynamic' => '\neon\core\form\fields\SelectDynamic',
		'selectmultiple' => '\neon\core\form\fields\SelectMultiple',
		'selectpicker' => '\neon\core\form\fields\SelectPicker',
		'submit' => '\neon\core\form\fields\Submit',
		'switch' => '\neon\core\form\fields\SwitchButton',
		'switchbutton' => '\neon\core\form\fields\SwitchButton',
		'switchmultiplestate' => '\neon\core\form\fields\SwitchMultipleState',
		'text' => '\neon\core\form\fields\Text',
		'textarea' => '\neon\core\form\fields\Textarea',
		'time' => '\neon\core\form\fields\Time',
		'wysiwyg' => '\neon\core\form\fields\Wysiwyg',
		// TODO:  the form should not be aware of the Page Selector - this should be a registered by the CMS page
		'pageselector' => '\neon\cms\form\fields\PageSelector',
		'color' => '\neon\core\form\fields\el\Color',
		'repeater' => '\neon\core\form\FormRepeater',
		'form' => '\neon\core\form\Form',
	];

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
	 * @return fields\Field|Form|FormRepeater
	 */
	abstract public function add($field, $defaults=[], $overwrite=false);

	/**
	 * Adds a sub form box to the form
	 *
	 * @param string|IField $name
	 * @param array $options
	 * @return IField|Form
	 */
	public function addSubForm($name, $options=[])
	{
		if (is_object($name)) {
			return $this->add($name);
		} else {
			return $this->add($options, ['name' => $name, 'class' => '\neon\core\form\Form']);
		}
	}

	/**
	 * Adds a text box field to the form
	 *
	 * @param string $name
	 * @param array $options
	 * @return IField|fields\Text
	 */
	public function addFieldText($name, $options=[])
	{
		return $this->add($options, ['name' => $name, 'class' => 'text']);
	}

	/**
	 * Adds a password field to the form
	 *
	 * @param string $name
	 * @param array $options
	 * @return IField|fields\Password
	 */
	public function addFieldPassword($name, $options=[])
	{
		return $this->add($options, ['name' => $name, 'class' => 'password']);
	}

	/**
	 * Adds a field input box to the form
	 *
	 * @param string $name
	 * @return IField|fields\Text
	 */
	public function addFieldFile($name, $options=[])
	{
		return $this->add($options, ['class' => 'file', 'name' => $name]);
	}

	/**
	 * Adds a specific image input box to the form
	 * This is similar to the File input however will provide additional options
	 * for image handling such as crop / aspect ratio etc.
	 *
	 * @param string $name
	 * @return IField|fields\Text
	 */
	public function addFieldImage($name, $options=[])
	{
		return $this->add($options, ['class' => 'image', 'name' => $name]);
	}

	/**
	 * Adds a email input box field to the form
	 *
	 * @param string $name
	 * @return IField|fields\Email
	 */
	public function addFieldEmail($name, $options=[])
	{
		return $this->add($options, ['class' => 'email', 'name' => $name]);
	}

	/**
	 * Adds a submit button field to the form
	 *
	 * @param string $name
	 * @param array $options
	 * @return IField|fields\Submit
	 */
	public function addFieldSubmit($name, $options=[])
	{
		return $this->add($options, ['class' => 'submit', 'name' => $name]);
	}

	/**
	 * Adds a hidden text box field to the form
	 *
	 * @param string $name
	 * @param array $options
	 * @return IField|fields\Text
	 */
	public function addFieldHidden($name, $options=[])
	{
		return $this->add($options, ['class' => 'hidden', 'name' => $name]);
	}

	/**
	 * Add a checkbox field
	 *
	 * @param string $name
	 * @param array  $options
	 * @return IField|fields\Checkbox
	 */
	public function addFieldCheckbox($name, $options=[])
	{
		return $this->add($options, ['class' => 'checkbox', 'name' => $name]);
	}

	/**
	 * Add a checkbox list field
	 *
	 * @param string $name
	 * @param array  $options
	 * @return IField|fields\Checkbox
	 */
	public function addFieldCheckList($name, $options=[])
	{
		return $this->add($options, ['class' => 'checklist', 'name' => $name]);
	}

	/**
	 * Adds a Date field to the form
	 *
	 * @param string $name
	 * @param array  $options
	 * @return IField|fields\Date
	 */
	public function addFieldDate($name, $options=[])
	{
		return $this->add($options, ['name' => $name, 'class' => 'date']);
	}

	/**
	 * Adds a DateTime field to the form
	 *
	 * @param string $name
	 * @param array  $options
	 * @return IField|fields\DateTime
	 */
	public function addFieldDateTime($name, $options=[])
	{
		return $this->add($options, ['name' => $name, 'class' => 'datetime']);
	}

	/**
	 * Adds an integer field to the form
	 *
	 * @param string $name
	 * @param array  $options
	 * @return IField|fields\Integer
	 */
	public function addFieldInteger($name, $options=[])
	{
		return $this->add($options, ['name' => $name, 'class' => 'integer']);
	}

	/**
	 * Add a select box field
	 *
	 * @param string $name
	 * @param array  $options
	 * @return IField|fields\Select
	 */
	public function addFieldSelect($name, $options=[])
	{
		return $this->add($options, ['class' => 'select', 'name' => $name]);
	}
	
	/**
	 * Add a dynamic select box field
	 
	 * These will make calls to the database to get their select list.
	 * These require a *dataMapProvider* which is the neon app which will make 
	 * the database call. If this is missing daedalus will be assumed.
	 *
	 * Also you need to provide a *dataMapKey* which will depend on the service,
	 * for example for daedalus this is the table you want to search in
	 *
	 * @param string $name
	 * @param array  $options  see notes above
	 * @return IField|fields\SelectDynamic
	 */
	public function addFieldSelectDynamic($name, $options=[])
	{
		if (!isset($options['dataMapProvider']))
			$options['dataMapProvider'] = 'daedalus';
		if (neon()->isDevMode()) {
			if (!isset($options['dataMapKey']))
				throw new \InvalidArgumentException("You need to provide a dataMapProvider if not daedalus, and a dataMapKey to use a select dynamic");
		}
		return $this->add($options, ['class' => 'selectdynamic', 'name' => $name]);
	}

	/**
	 * Add a section field
	 *
	 * @param string $name
	 * @param array  $options
	 * @return IField|fields\Section
	 */
	public function addFieldSection($name, $options=[])
	{
		return $this->add($options, ['class' => 'section', 'name' => $name]);
	}

	/**
	 * Add a switch button field
	 *
	 * @param string $name
	 * @param array  $options
	 * @return IField|fields\SwitchButton
	 */
	public function addFieldSwitch($name, $options=[])
	{
		return $this->add($options, ['class' => 'switch', 'name' => $name]);
	}

	/**
	 * Add a text box field to a form
	 *
	 * @param string $name
	 * @param array  $options
	 * @return IField|fields\Textarea
	 */
	public function addFieldTextArea($name, $options=[])
	{
		return $this->add($options, ['class' => 'textarea', 'name' => $name]);
	}

	/**
	 * Adds a Time field to the form
	 *
	 * @param string $name
	 * @param array  $options
	 * @return IField|fields\Time
	 */
	public function addFieldTime($name, $options=[])
	{
		return $this->add($options, ['name' => $name, 'class' => 'time']);
	}

	/**
	 * Add a wysiwyg field
	 *
	 * @param string $name
	 * @param array  $options
	 * @return IField|fields\Wysiwyg
	 */
	public function addFieldWysiwyg($name, $options=[])
	{
		return $this->add($options, ['class' => 'wysiwyg', 'name' => $name]);
	}

	/**
	 * Add a link field
	 *
	 * @param string $name
	 * @param array  $options
	 * @return IField|fields\Wysiwyg
	 */
	public function addFieldLink($name, $options)
	{
		return $this->add($options, ['class' => 'link', 'name' => $name]);
	}

	/**
	 * Add a form repeater field
	 *
	 * @param string $name
	 * @param array  $options
	 * @return FormRepeater
	 */
	public function addFormRepeater($name, $options)
	{
		return $this->add($options, ['class' => 'repeater', 'name' => $name]);
	}

	/**
	 * A generic add function to add things to a form
	 *
	 * @param array|IField $field a configuration array or an IField object
	 * @param array $defaults (optional) - This is ignored if the $field parameter is an object
	 * @throws \InvalidArgumentException if the $field is not an instance of IField
	 * @throws UnknownFieldClass if the class is not found
	 * @return IField
	 */
	public function makeField($field, $defaults=[])
	{
		if (is_array($field))
			$field = $this->createFieldObjectFromConfig($field, $defaults);

		if (!($field instanceof IField || $field instanceof Form)) {
			// add this form as the parent form reference
			throw new \InvalidArgumentException('The $field parameter was not a valid \neon\core\form\fields\Field or \neon\core\form\Form object "' . print_r($field, true) . '" given');
		}
		return $field;
	}

	/**
	 * A factory function to create a form field from its array config
	 *
	 * @param array $config
	 *
	 * ~~~php
	 * [
	 *     'class' => '\neon\core\form\fields\Text'
	 *     'name' => 'last_name'
	 *     'validators' => [
	 *         ['string', ['max' => 10]]
	 *     ]
	 * ]
	 * ~~~
	 *
	 * @param array $defaults (optional) - Default config properties that will be added to the config if they are not present
	 * @throws UnknownFieldClass
	 * @return IField
	 */
	public function createFieldObjectFromConfig($config, $defaults=[])
	{
		$config = Arr::merge($defaults, $config);
		// allows class config property to be specified by an alias "switch"
		// this will then be replaced by the class defined in $this->fieldAliases
		if (isset($config['class']) && $this->fieldAliasExists($config['class'])) {
			$config['class'] = $this->getFieldClassByAlias($config['class']);
		}
		$config = Arr::merge(['class' => $this->fieldAliases['text'], 'form' => $this], $config);
		$class = Arr::remove($config, 'class');
		if (!class_exists($class)) {
			if (class_exists('\neon\core\form\fields\\'.$class)) {
				$class = '\neon\core\form\fields\\'.$class;
			} else {
				throw new UnknownFieldClass($class);
			}
		}
		$object = \Neon::createObject($class, $config);
		\Neon::configure($object, $config); // this really shouldn't be necessary but Yii::createObject is doing something odd
		$object->init();// honour runtime customisation
		return $object;
	}

	/**
	 * Whether a particular alias exists in $this->fieldAliases
	 *
	 * @param $alias
	 * @return bool
	 */
	public function fieldAliasExists($alias)
	{
		return isset($this->fieldAliases[strtolower($alias)]);
	}

	/**
	 * Return a full class path from an alias string
	 * @see $this->fieldAliases
	 *
	 * @param string $alias
	 * @return mixed
	 */
	public function getFieldClassByAlias($alias)
	{
		if (!$this->fieldAliasExists($alias))
			throw new \InvalidArgumentException("There is no alias with the key '$alias' in the Forms fieldAliases array");
		return $this->fieldAliases[strtolower($alias)];
	}

	/**
	 * Get form field definitions (config arrays) for DDS data types
	 * TODO: validate against DDS data types
	 * @return array
	 */
	public function getDefaultTypes()
	{
		$fieldPath = '\neon\core\form\fields\\';
		return [
			// !! 'textshort', 'Short Text', 'For text up to 150 characters (about 15 to 30 words)', '{\"size\":150}', 'textshort', 0)
			'textshort' => [
				'class' => $fieldPath.'Text',
				'validators' => ['string'=> ['max' => 150]]
			],
			// !! 'text', 'Text', 'For text up to about twenty thousand characters (about two to four thousand average English words)', '{\"size\":20000}', 'text', 0),
			'text' => [
				'class' => $fieldPath.'Textarea',
				'validators' => ['string'=> ['max' => 65000]]
			],
			// !! 'textlong', 'Long Text', 'For text up to about five million characters (about half to one million average English words).', '{\"size\":5000000}', 'textlong', 0),
			'textlong' => [
				'class' => $fieldPath.'Textarea',
				'validators' => ['string'=> ['max' => 5000000]]
			],
			// - perhaps we need a htmllong?
			// - perhaps we need a jsonlong?
			// 'html', 'HTML', 'For HTML up to about ten thousand characters', '{\"size\":10000}', 'text', 0),
			'html' => [
				'class' => $fieldPath.'Wysiwyg',
				'validators' => ['string'=> ['max' => 65000]]
			],
			// 'json', 'JSON', 'For JSON up to about ten thousand characters', '{\"size\":10000}', 'text', 0),
			'json' => [
				'class' => $fieldPath.'Textarea',
				// the string validator prevents using arrays as it complains - ideally we only want
				// to validate a json fields once it has been converted
				'validators' => ['string'=> ['max' => 1000000]]
			],
			'markdown' => [
				'class' => $fieldPath.'Markdown',
				// the string validator prevents using arrays as it complains - ideally we only want
				// to validate a json fields once it has been converted
				'validators' => ['string'=> ['max' => 1000000]]
			],
			// 'url', 'URL', 'URL up to 150 characters', '{\"size\":150}', 'textshort', 0)
			'url' => [
				'class' => $fieldPath.'Text',
				'validators' => ['string'=> ['max' => 150]]
			],

			'date' => [
				'class' => $fieldPath.'Date',
			],

			'datetime' => [
				'class' => $fieldPath.'DateTime',
			],

			'time' => [
				'class' => $fieldPath.'Time',
			],

			'integer' => [
				'class' => $fieldPath.'Integer',
				'validators' => ['integer' => ['min' => -2147483648, 'max' => 2147483648]]
			],

			'float' => [
				'class' => $fieldPath.'Currency',
				'validators' => 'number'
			],

			'image_ref' => [
				'class' => $fieldPath.'Image',
				'validators' => 'image'
			],

			'uuid64' => [
				'class' => $fieldPath.'Text',
				'validators' => ['string'=> ['length' => 22]]
			],

			//	!! 'binary',       'Normal Binary',      'For normal binaries such as images up to 16MB in size', '', 'binary', 0),
			//	!! 'binarylong',   'Very Large Binary',  'For storing binary data such as documents up to 4GB in size', '', 'binarylong', 0),
			//	!! 'binaryshort',  'Small Binary',       'For small binaries up to 16KB in size (e.g thumbnails)', '', 'binaryshort', 0),
			//	!! 'date',         'Date',               'For storing a date (no time associated) from the years 1000 to 9999', '', 'date', 0),
			//	!! 'datetime',     'Date and Time',      'For storing date and time from the years 1000 to 9999', '', 'datetime', 0),
			//	!! 'float',        'Float',              'For floating numbers (decimal with limited but large precision) up to about 1 x 10^±38', '', 'float', 0),
			//	!! 'floatdouble',  'Double Float',       'A double precision float up to 1 x 10^±308', '', 'floatdouble', 0),
			//	!! 'integer',      'Integer',            'For integers up to ±2,147,483,648', '', 'integer', 0),
			//	!! 'integerlong',  'Large Integer',      'For integers up to 9 quintillion (9 with 18 zeros after it).', '', 'integerlong', 0),
			//	!! 'integershort', 'Short Integer',	      'For integers up to ±8192', '{\"max\":8192}', 'integershort', 0),
			//	!! 'integertiny',  'Tiny Integer',       'For numbers up to ±128', '{\"max\":128}', 'integertiny', 0),
			//	!! 'boolean',      'Boolean',            'For true | false value', '{\"size\":1}', 'integertiny', 0),
			'boolean' => [
				'class' => $fieldPath.'SwitchButton',
				'validators' => 'boolean'
			],
			'choice' => [
				'class' => $fieldPath.'Choice',
			],
			//	'uuid',     'UUID',      'For Universally Unique Identifiers', '{\"size\":32}', 'textshort', 0),
			//	'mime',     'MIME Type', 'For storing mime types', '{\"size\":256}', 'text', 0),
			//	'document', 'Document',  'For storing documents up to 4GB in size', '', 'binarylong', 0),
			//	'image',    'Image File', 'For storing images up to 16MB in size', '', 'binary', 0);
		];
	}
}