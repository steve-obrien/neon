<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use neon\core\form\fields\Field;
use neon\core\helpers\Html;

/**
 * Class Text
 * @package neon\core\form
 */
class Markdown extends Ide
{
	/**
	 * @inheritdoc
	 */
	public $ddsDataType = 'markdown';

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Markdown IDE', 'icon' => 'fa fa-font', 'group' => 'Formatted Text', 'order' => 230
		];
	}
}