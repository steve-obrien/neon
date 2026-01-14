<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

/**
 * Class SwitchMultipleState
 * @package neon\core\form
 */
class SwitchMultipleState extends Radio
{
	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Switch Multiple State', 'icon' => 'fa fa-toggle-on', 'group' => 'Choice', 'order' => 451
		];
	}
}