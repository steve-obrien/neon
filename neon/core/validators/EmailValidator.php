<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 25/01/2018
 * @package neon
 */

namespace neon\core\validators;

use neon\core\interfaces\IProperties;
use neon\core\traits\PropertiesTrait;
use yii\validators\EmailValidator as YiiEmailValidator;

class EmailValidator extends YiiEmailValidator implements IProperties
{
	use PropertiesTrait,
		ValidationTrait { ValidationTrait::toArray insteadof PropertiesTrait; }

	public $message = '{attribute} does not look like a valid email address';
	/**
	 * @inheritdoc
	 */
	public function getClientOptions($model, $attribute)
	{
		$options = [
			'allowName' => $this->allowName,
			'message' => $this->formatMessage($this->message, [
				'attribute' => $model->getAttributeLabel($attribute),
			]),
			'enableIDN' => (bool)$this->enableIDN,
		];
		if ($this->skipOnEmpty) {
			$options['skipOnEmpty'] = 1;
		}

		return $options;
	}
}