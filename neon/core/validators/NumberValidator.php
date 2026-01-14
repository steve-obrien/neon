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
use yii\validators\NumberValidator as YiiValidator;

class NumberValidator extends YiiValidator implements IProperties
{
	use PropertiesTrait,
		ValidationTrait {
		ValidationTrait::toArray insteadof PropertiesTrait;
	}

	public function getClientOptions($model, $attribute)
	{
		$options = parent::getClientOptions($model, $attribute);
		$options['integerOnly']=$this->integerOnly;
		return $options;
	}
}