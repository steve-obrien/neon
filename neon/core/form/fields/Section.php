<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use neon\core\form\fields\Field;

/**
 * Class Text
 * @package neon\core\form
 */
class Section extends Field
{
	/**
	 * Sections do not have errors
	 * @return string
	 */
	public function getErrorHtml()
	{
		return '';
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Section',
			'group' => "Form Only",
			'icon' => 'fa fa-square-o',
			'order' => 925
		];
	}
}