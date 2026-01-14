<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2018 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;
use neon\core\helpers\Arr;

/**
 * Class Text
 * @package neon\core\form
 */
class SelectPicker extends Link
{
	/**
	 * The label to be displayed describing the action to be taken on the list of selected items
	 * @var string
	 */
	public $selectedItemsListLabel = '';

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		return array_merge(parent::getProperties(), ['selectedItemsListLabel']);
	}

	/**
	 * @inheritdoc
	 */
	public function getFilterField()
	{
		return [
			'class' => SelectMultiple::class,
			'multiple' => true,
			'items' => $this->getItems()
		];
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Select Picker', 'icon' => 'fa fa-hand-pointer-o', 'group' => 'Table Links', 'order' => 570
		];
	}
}