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
use yii\validators\UrlValidator as YiiUrlValidator;

class UrlValidator extends YiiUrlValidator implements IProperties
{
	public $pattern = '/^(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(?::\d{1,5})?(?:$|[?\/#])/i';

	use PropertiesTrait,
		ValidationTrait { ValidationTrait::toArray insteadof PropertiesTrait; }

	public $message = '{attribute} does not look like a valid url';

	/**
	 * @inheritdoc
	 */
	public function getClientOptions($model, $attribute)
	{
		$options = [
			'message' => $this->formatMessage($this->message, [
				'attribute' => $model->getAttributeLabel($attribute),
			]),
		];
		if ($this->skipOnEmpty) {
			$options['skipOnEmpty'] = true;
		}
		return $options;
	}
}