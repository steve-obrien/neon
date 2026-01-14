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
class Integer extends Real
{
	/**
	 * The DDS data type to store the value of the field
	 * @var string
	 */
	public $ddsDataType = 'integer';

	/**
	 * @var Integer does not have decimals so set to zero
	 */
	public $decimal = 0;

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Integer', 'icon' => 'fa fa-italic', 'group' => 'Number', 'order' => 700
		];
	}
}