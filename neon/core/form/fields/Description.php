<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use neon\core\form\fields\Field;
use neon\core\grid\query\IQuery;
use neon\core\helpers\Arr;
use neon\core\helpers\Html;
use neon\core\web\View;

/**
 * Class Text
 * @package neon\core\form
 */
class Description extends DisplayText
{
	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Description',
			'group' => "Form Only",
			'icon' => 'fa fa-paragraph',
			'order' => 923
		];
	}
}