<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

/**
 * Class Text
 * @package neon\core\form
 */
class SelectMultiple extends ChoiceMultiple
{
	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Select Multiple', 'icon' => 'fa fa-list', 'group' => 'Choice', 'order' => 420
		];
	}
}