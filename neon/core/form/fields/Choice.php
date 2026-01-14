<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 18/10/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\form\fields;

use neon\core\helpers\Arr;
use neon\core\helpers\Html;

/**
 * Class Choice
 * Allows one item to be saved from a selection of choices
 * @author Steve O'Brien - Newicon Ltd
 * @package neon\core\form\fields
 */
class Choice extends Field
{
	/**
	 * @inheritdoc
	 */
	public $ddsDataType = 'choice';

	/**
	 * Whether the select box allows the user to clear the current selection
	 * It's useful to turn this off when a selection must be made, a default is set, and null is not permitted
	 * For example: select the start day of your work week day, where the default is Monday - clearing this selection
	 * makes no sense as you would leave it as monday
	 * @var bool
	 */
	public $allowClear = true;

	/**
	 * Whether a user can create new options based on what they type in the box
	 * @var bool
	 */
	public $create = false;

	/**
	 * @inheritdoc
	 */
	public function getFieldHtml()
	{
		return Html::tag('neon-select', '', [
			'v-bind' => $this->toJson(),
			'name' => $this->getInputName()
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function getFilterField()
	{
		$field = parent::getFilterField();
		// if the select option has the creation ability - we want this off when used in filters.
		$field['create'] = false;
		// without this a select box always has a value meaning the grid will always filter by it
		$field['allowClear'] = true;
		return $field;
	}

	/**
	 * @var array - store the choice items
	 * [ 'key' => 'The display value'] ]
	 */
	private $_items = [];

	/**
	 * Set the choice items
	 * This is key => value where the value is what is displayed in the choice and the key is the value stored against the field
	 * *Note* this also support groups where an item is an array - the key will be used as the group label
	 * *Note* this also supports an array of associative arrays in the format ['key' => 'the key', 'value' => 'The Value']
	 * @param array $items
	 * @return $this
	 */
	public function setItems($items)
	{
		$test =  current((array) $items);
		if (is_array($test) && isset($test['key']) && isset($test['value'])) {
			$this->_items = Arr::map($items, 'key', 'value');
		} else {
			$this->_items = $items;
		}
		return $this;
	}

	/**
	 * The array of choices for e.g. `[ 'key' => 'The display value' ]`
	 * @return array
	 */
	public function getItems()
	{
		return $this->_items;
	}

	/**
	 * Clear the items
	 * @return array  the previously set items
	 */
	public function clearItems()
	{
		$items = $this->_items;
		$this->_items = [];
		return $items;
	}

	/**
	 * Get the items suitable for display in standard html elements such as checkbox
	 * lists and radio sets.
	 *
	 * This maps items to key=>value pairs and adds some extra html around the
	 * displayed text to enable enhanced css handling. Use neonField_labelText to
	 * access this in CSS
	 */
	public function getItemsDisplay()
	{
		$items = $this->getItems();
		foreach ($items as $key => $value)
			$items[$key] = '<span class="neonField_labelText">'.$value.'</span>';
		return $items;
	}

	/**
	 * The value from the database is the uuid of the linked field
	 * @param array $value in format: ['key' => 'key', 'value' => 'Human Readable String', 'type' => 'choice']
	 * @inheritDoc
	 */
	public function setValueFromDb($value)
	{
		// A choice field received an array of information from Daedalus
		if (is_array($value) && isset($value['key'])) {
			parent::setValueFromDb($value['key']);
		} else {
			// if it is not array assume this is the key of the field
			parent::setValueFromDb($value);
		}
		return $this;
	}

	/**
	 * @return array
	 */
	public function getProperties()
	{
		return array_merge(parent::getProperties(), ['items', 'allowClear', 'create']);
	}

	/**
	 * Get a displayable representation of the fields value
	 * Assumes a single value for the choice
	 * @return string
	 */
	public function getValueDisplay($context='')
	{
		$items = $this->getItems();
		return isset($items[$this->getValue()]) ? $items[$this->getValue()] : '';
	}

	/**
	 * Check if the items (array) is in ('key' => 'value')
	 * if not then get it mapped via yii Arr::map helper
	 *
	 * @return array
	 * @deprecated
	 */
	public function getItemsMapped()
	{
		$items = $this->getItems();
		if (Arr::isIndexed($items, true)) {
			$items = Arr::map($items, 'key', 'value');
		}
		return $items;
	}

	/**
	 * @return array
	 */
	protected function extractChoices()
	{
		return $this->getItems();
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Choice (Abstract)', 'group' => 'Do Not Use', 'icon' => 'fa fa-list', 'order' => 1500,
		];
	}

	/**
	 * @inheritdoc
	 */
	public function getValue()
	{
		$value = parent::getValue();
		return is_array($value) ? array_filter($value) : $value;
	}
}