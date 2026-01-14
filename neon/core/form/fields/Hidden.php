<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2018 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use neon\core\form\fields\Field;
use neon\core\helpers\Html;

/**
 * Class Hidden
 * @package neon\core\form
 */
class Hidden extends Field
{
	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Hidden', 'group' => 'Form Only', 'icon' => 'fa fa-eye-slash', 'order' => 1210,
		];
	}
}