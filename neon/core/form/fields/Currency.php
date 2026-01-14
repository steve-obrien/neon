<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use neon\core\form\fields\Real;

/**
 * Class Text
 * @package neon\core\form
 */
class Currency extends Real
{
	/**
	 * @inheritdoc
	 */
	public $decimal = 2;

	/**
	 * @inheritdoc
	 */
	public function fake()
	{
		return faker()->randomFloat($this->decimal);
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Currency', 'group' => 'Number', 'icon' => 'fa fa-money', 'order'=>540,
		];
	}
}