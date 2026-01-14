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
class Json extends Ide
{
	/**
	 * @inheritdoc
	 */
	public $ddsDataType = 'json';

	/**
	 * @inheritdoc
	 */
	public function getFilterField()
	{
		return ['class' => 'neon\\core\\form\\fields\\Text'];
	}

	/**
	 * @inheritdoc
	 */
	public function getValueDisplay($context='')
	{
		if ($context === 'csv') {
			return $this->getValue();
		}
		return neon()->formatter->asJson($this->getValue());
	}

	/**
	 * @inheritdoc
	 */
	public function getValue()
	{
		// return sanitised data only
		return $this->getUnsafeValue();
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Json IDE', 'icon' => 'fa fa-code', 'group' => 'Formatted Text', 'order' => 240
		];
	}
}
