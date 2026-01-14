<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 21/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\form\fields\el;

use neon\core\form\fields\el\assets\ElAsset;
use neon\core\form\fields\SwitchButton;
use neon\core\form\fields\Text;
use neon\core\helpers\Arr;
use neon\core\helpers\Html;

class SwitchInput extends SwitchButton
{
	/**
	 * The DDS data type to store the value of the field
	 * @var string
	 */
	public $ddsDataType = 'boolean';

	public function registerScripts($view)
	{
		ElAsset::register($view);
	}

	/**
	 * @inheritdoc
	 */
	public function getFieldHtml()
	{
		return Html::tag('el-switch', '', ['name'=>$this->getInputName()]);
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'El Switch',  'icon' => 'fa fa-toggle-on', 'group' => 'Choice', 'order'=>465,
		];
	}


}