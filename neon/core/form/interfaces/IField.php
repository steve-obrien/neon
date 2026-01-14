<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */
namespace neon\core\form\interfaces;

use neon\core\form\exceptions\InvalidNameFormat;
use neon\core\grid\query\IQuery;
use neon\core\interfaces\IComponent;
use neon\core\interfaces\IProperties;

 /*
 | ---------------------------------------------
 | A field represents a system data type
 | ---------------------------------------------
 | A refactor would make everything central to Type objects - which is what the form Field Objects have become)
 |
 | These functions define how data flows form the user (typically via a user http request) through to the database.
 | And from the database back to the user to be displayed.
 | Often data must be decorated, validated, sanitised and transformed.
 | Link fields, for exmaple, store data in the database as a string that represents a uuid that is the foreign key of another table.
 | To achieve this mapping a Type plus a configuration (the table the link field represents) is important.
 | Or perhaps a location field.  In the database, a text field representing json stores a lattitude and longitude, the front end a map component is presented.
 | Whilst the databse technically allows any text, the type validates the data to ensure only the correct shcema is stored.
 |
 |      +---------------------+ Http  <-----------------------------+
 |      |                                                           |
 |      |                   +----------+                            |
 |      +--setValue-------->|          |---->| Input Field |--------+
 |                          |          |
 |                          |          |
 |                          |          |---->| Display | (context)
 |                          |          |
 |                          |          |
 |                          |   Field  |---->| Process Filter | 
 |                          |          |
 |                          |          |
 |                          |          |---->| Filter Type |
 |                          |          |
 |                          |          |
 |      +-----setFromDb---->|          |--getData()-->| Daedalus |--+
 |      |                   +----------+                            |
 |      |                                                           |
 |      +-----------------------------------------------------------+
 |
 *
 * Interface IField
 * @package neon\core\form\interfaces
 */
interface IField extends
	IValidate,
	IProperties,
	IComponent
{
	/**
	 * This function represents the formatted data of the fields value
	 * the getValue represents the field value in the context of the input control and may have different formatting to
	 * the data wished to be returned by the form.
	 * This function is called when the parent forms getData function is called.
	 * Use this function to convert a value (set with $this->setValue) to its database representation
	 * @see $this->load() which represents the data input
	 * @return mixed
	 */
	public function getData();

	/**
	 * Register the scripts for this widget - this is called just before rendering the output during the run command
	 *
	 * @param \neon\core\web\View $view the view object to add the scripts to.
	 * Note passing this in allows you to attach the scripts to different views, this is useful if we want to collect the
	 * javascript for a widget in isolation, we can create a new view that we can pass in to registerScripts function
	 *
	 * @return void
	 */
	public function registerScripts($view);

	/**
	 * Used when outputting form data via the getData method. Sets the key in the output array for this fields data
	 * If not specified will return the fields name as the key for the data
	 *
	 * @see \neon\core\form\Form::getData()
	 * @return mixed
	 */
	public function getDataKey();

	/**
	 * Set the data key that is used when returning the data
	 *
	 * @param string $key
	 * @return $this method can be chained
	 */
	public function setDataKey($key);

	/**
	 * The name of the field
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Get the field input name.
	 * Fields prefix their parent form names to field inputs.
	 *
	 * @return string
	 */
	public function getInputName();

	/**
	 * The field name also used to uniquely identify the field [A-Za-z0-9_-] characters only
	 *
	 * @param string $name
	 * @throws InvalidNameFormat - if a name with unsupported characters is used
	 * @return $this method can be chained
	 */
	public function setName($name);

	/**
	 * Set the parent form this field belongs to
	 *
	 * @param \neon\core\form\Form $form
	 * @return $this method can be chained
	 */
	public function setForm($form);

	/**
	 * @return \neon\core\form\Form|null
	 */
	public function getForm();

	/**
	 * Whether this form is a sub form
	 * @return bool
	 */
	public function isSubForm();

	/**
	 * Whether this form represents the root form node
	 * @return bool
	 */
	public function isRootForm();

	/**
	 * Whether this field/form has a parent form
	 * @return bool
	 */
	public function hasForm();

	/**
	 * Whether this is a form - defaults to false
	 * @return bool
	 */
	public function isForm();

	/**
	 * Set the value for the field, this is typically the user input
	 * There is a post processing stage when asking the form for its data @see $this->getData which can be used
	 * to convert the value to its database representation
	 *
	 * Note as these represent form fields this function should cope with null values.
	 *
	 * @param string|array|null $value
	 * @return $this method can be chained
	 */
	public function setValue($value);

	/**
	 * Is called when the parent form calls $form->loadFromDb()
	 * This function should convert a database representation of its value (as given by $this->getData()) to its
	 * field value representation suitable for the field and user display.
	 *
	 * E.g. a mysql formatted date and time could be converted here from mysql format into the user format expressed
	 * in the input field.
	 *
	 * The default function simply calls $this->setValue.
	 * Override this function - perform necessary database to user value conversions and then call setValue function
	 * with the appropriate data
	 *
	 * Note this should be able to cope with null values
	 *
	 * @param mixed $value
	 * @return $this method can be chained
	 */
	public function setValueFromDb($value);

	/**
	 * Get the field value
	 * @return mixed
	 */
	public function getValue();

	/**
	 * Return a displayable representation of the fields value
	 *
	 * @param string $context - specifies a rendering context for e.g. 'grid', 'summary', 'csv' etc
	 * @return string
	 */
	public function getValueDisplay($context='');

	/**
	 * Get the hint message for this field
	 *
	 * @return string
	 */
	public function getHint();

	/**
	 * Set the hint for this field
	 *
	 * @param string $hint
	 * @return $this
	 */
	public function setHint($hint);

	/**
	 * Set the label for the field
	 *
	 * @param string $label
	 * @return $this method can be chained
	 */
	public function setLabel($label);

	/**
	 * Return the label for this field
	 *
	 * @return $this method can be chained
	 */
	public function getLabel();

	/**
	 * Set the class label for the field
	 *   - a user friendly name of the type of class this is
	 *
	 * @param string $label
	 * @return $this method can be chained
	 */
	public function setClassLabel($label);

	/**
	 * Return the class label for this field
	 *
	 * @return $this method can be chained
	 * @deprecated - to be removed
	 */
	public function getClassLabel();

	/**
	 * Whether this field represents a valid input field and will appear in data output
	 * Headings and section fields will not be inputs and therefore not clutter up data output
	 * @return bool
	 */
	public function getIsInput();

// region: Filter form

	/**
	 * Get the filter field that represents a search input field for this type
	 * This can be a field config array, and object or boolean false for no filter
	 *
	 * @return array|IField|false
	 */
	public function getFilterField();

	/**
	 * Apply appropriate filtering rules to the IQuery object
	 *
	 * @param IQuery $query
	 * @param mixed $searchData - The request data if null should populate from the form fields internal $this->getValue()
	 * @return void
	 */
	public function processAsFilter(IQuery $query, $searchData=null);

// endregion

	/**
	 * Gets an id by concatenating the name of this field and all parents by the specified $delimiter
	 * For example ```$this->getIdPath('-')``` will give a typical html element id separated by a dash
	 * @param string $delimiter - the string delimiter to separate concatenated field names by
	 * @return mixed
	 */
	public function getIdPath($delimiter='.');

	/**
	 * Generate fake data for the field
	 * @return mixed
	 */
	public function fake();
}