<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use neon\core\grid\query\IQuery;
use neon\core\helpers\Arr;
use \neon\core\helpers\Html;

/**
 * Class Multichoice
 * Allow a user to select multiple options from a fixed choice list
 *
 * @package neon\core\form
 */
class ChoiceMultiple extends Choice
{
	/**
	 * @inheritdoc
	 */
	public $ddsDataType = 'choice_multiple';

	protected $_value = [];

	/**
	 * @inheritDoc
	 */
	public function getValueDisplay($context='')
	{
		$items = $this->getSelectedItems();
		return implode(',', $items);
	}

	/**
	 * @inheritdoc
	 */
	public function setValueFromDb($value)
	{
		$items = [];
		foreach ($value as $item)
			$items[] = $item['key'];
		return parent::setValueFromDb($items);
	}

	/**
	 * Get the items that have been selected (as opposed to the total set of
	 * available items)
	 *
	 * @return array [ 'key' => 'value' ]
	 */
	public function getSelectedItems()
	{
		$values = $this->getValue();
		if (!is_array($values))
			$values = [$values];
		if ($this->create === false)
			return Arr::only($this->getItems(), $values);
		return $values;
	}

	/**
	 * @inheritdoc
	 */
	public function getFilterField()
	{
		return [
			'class' => 'neon\core\form\fields\SelectMultiple',
			'multiple' => true,
			'items' => $this->getItems()
		];
	}

	/**
	 * @inheritdoc
	 */
	public function processAsFilter(IQuery $query, $searchData=null)
	{
		if (isset($searchData[0])) {
			$keys = explode(',', $searchData[0]);
			foreach($keys as $key) {
				if (!empty($key) && $key !== 'null') {
					$query->where($this->getDataKey(), 'like', $key);
				}
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Multichoice (Abstract)', 'group' => 'Do Not Use', 'icon' => 'fa fa-list', 'order' => 1500,
		];
	}
}
