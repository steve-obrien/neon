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
 * Class Text
 * @package neon\core\form
 */
class CheckList extends ChoiceMultiple
{
	/**
	 * @inheritDoc
	 */
	public function getValueDisplay($context='')
	{
		$items = $this->getSelectedItems();
		return Html::displayAsTags($items);
	}

	/**
	 * @inheritdoc
	 */
	public function getValue()
	{
		$value = parent::getValue();
		return $value ? $value : [];
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Checklist', 'group' => 'Choice', 'icon' => 'fa fa-list', 'order' => 441,
		];
	}
}