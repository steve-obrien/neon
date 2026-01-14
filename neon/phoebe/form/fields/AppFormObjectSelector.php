<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 02/12/2016 10:58
 * @package neon
 */

namespace neon\phoebe\form\fields;

use \neon\core\form\fields\SelectDynamic;

/**
 * Class AppFormSelector - provides a drop down list of available AppForms
 * @package neon\cms\form\fields
 */
class AppFormObjectSelector extends SelectDynamic
{
	public $dataMapProvider = 'phoebe';
	public $dataMapFilters = ['phoebe_type'=>'applicationForm'];
	public $dataMapKey = 'phoebe_object';

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Form Object', 'icon' => 'fa fa-link', 'group' => 'Phoebe', 'order' => 1020
		];
	}
}