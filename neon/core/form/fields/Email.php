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
class Email extends Text
{
	/**
	 * The DDS data type to store the value of the field
	 * @var string
	 */
	public $ddsDataType = 'email';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		// set up default email validator
		if (!$this->hasValidator(\neon\core\validators\EmailValidator::class))
			$this->addValidator('email');
	}

	/**
	 * @inheritdoc
	 */
	public function getFieldHtml()
	{
		$options = $this->getOptions();
		$options['id'] = $this->getId();
		$options['class'] = 'form-control';
		return Html::textInput($this->getInputName(), $this->getValue(), $options);
	}

	/**
	 * @inheritdoc
	 */
	public function fake()
	{
		return faker()->email;
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Email',
			'group' => 'Text',
			'order' => 130,
			'icon' => 'fa fa-envelope-o',
		];
	}

}
