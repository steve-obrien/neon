<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 02/12/2016 10:58
 * @package neon
 */

namespace neon\user\form\fields;

use \neon\core\form\fields\Link;

/**
 * Class PageSelector - provides a drop down list of web pages
 * @package neon\cms\form\fields
 */
class UserSelectorMultiple extends Link
{
	public $dataMapProvider = 'user';
	public $dataMapKey = 'users';

		/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Link Users', 'icon' => 'fa fa-link', 'group' => 'Table Links', 'order' => 430,
		];
	}
}