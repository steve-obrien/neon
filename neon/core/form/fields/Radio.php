<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use \neon\core\form\fields\Field;
use neon\core\helpers\Arr;
use \neon\core\helpers\Html;

/**
 * Class Text
 * @package neon\core\form
 */
class Radio extends Choice
{
	/**
	 * @inheritdoc
	 */
	public function getFilterField()
	{
		$items = $this->getItems();
		return [
			'class' => 'neon\core\form\fields\Select',
			'items' => $items
		];
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Radio list', 'icon' => 'fa fa-list-ul', 'group' => 'Choice', 'order' => 430
		];
	}
}