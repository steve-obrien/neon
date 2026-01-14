<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use neon\core\form\fields\el\assets\ElAsset;
use neon\core\form\fields\Field;
use neon\core\helpers\Html;

/**
 * Class Text
 * @package neon\core\form
 */
class Submit extends Field
{
	/**
	 * Label to apply to the button when the form is processing the request. e.g. submitting the form
	 * @var string
	 */
	public $submittingLabel = 'Submitting...';

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		return array_merge(parent::getProperties(), ['submittingLabel']);
	}

	/**
	 * @inheritdoc
	 */
	public function registerScripts($view)
	{
		ElAsset::register($view);
	}

	/**
	 * @inheritdoc
	 */
	public function setValueFromDb($value)
	{
		parent::setValueFromDb(false);
	}

	/**
	 * @inheritdoc
	 */
	public function setValue($value)
	{
		if ($value === 'true' || $value === true)
			$this->getForm()->submittedButton=$this->getName();
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Submit Button', 'group' => 'Form Only', 'icon' => 'fa fa-calendar-o', 'order' => 910,
		];
	}
}