<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 25/01/2018
 * @package neon
 */

namespace neon\core\validators;

use neon\core\helpers\Html;
use neon\core\interfaces\IProperties;
use neon\core\traits\PropertiesTrait;
use yii\validators\RegularExpressionValidator as YiiRegularExpressionValidator;

class RegularExpressionValidator extends YiiRegularExpressionValidator implements IProperties
{
	use PropertiesTrait,
		ValidationTrait {
		ValidationTrait::toArray insteadof PropertiesTrait;
	}
	/**
	 * {@inheritdoc}
	 */
	public function getClientOptions($model, $attribute)
	{
		$pattern = Html::escapeJsRegularExpression($this->pattern);

		$options = [
			'pattern' => $pattern,
			'not' => $this->not,
			'message' => $this->formatMessage($this->message, [
				'attribute' => $model->getAttributeLabel($attribute),
			]),
		];
		if ($this->skipOnEmpty) {
			$options['skipOnEmpty'] = 1;
		}

		return $options;
	}
}
