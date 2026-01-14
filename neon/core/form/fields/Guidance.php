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
class Guidance extends DisplayText
{
	/**
	 * @inheritdoc
	 */
	public function getFieldHtml()
	{
        return Html::tag('neon-core-form-fields-guidance', [
        	'text' => $this->getText(),
        ]);
	}

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		return array_merge(parent::getProperties(), ['showIf']);
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Guidance',
			'group' => "Form Only",
			'icon' => 'fa fa-info',
			'order' => 921
		];
	}
}