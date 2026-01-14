<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 02/12/2016 10:58
 * @package neon
 */

namespace neon\cms\form\fields;

use \neon\core\form\fields\SelectDynamic;

/**
 * Class PageSelector - provides a drop down list of web pages
 * @package neon\cms\form\fields
 */
class PageSelector extends SelectDynamic
{
	public $dataMapProvider = 'cms';
	public $dataMapKey = 'things';

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Link Page', 'icon' => 'fa fa-link', 'group' => 'Table Links', 'order'=>440,
		];
	}
}