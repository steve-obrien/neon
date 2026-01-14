<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2018 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use neon\core\form\fields\DisplayText;
use neon\core\helpers\Html;


/**
 * Class Text
 * @package neon\core\form
 */
class Heading extends DisplayText
{
	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Heading',
			'group' => "Form Only",
			'icon' => 'fa fa-header',
			'order' => 922
		];
	}
}