<?php
namespace neon\core\validators;

use Psr\Log\InvalidArgumentException;
use neon\core\validators\DateValidator;
use \Yii;
/**
 * Class Validator
 * @package neon\core\validators
 * @inheritdoc
 */
class Validator extends \yii\validators\Validator
{
	/**
	 * @var array list of built-in validators (name => class or configuration)
	 */
	public static $builtInValidators = [
		'boolean' => ['class'=>'yii\validators\BooleanValidator'],
		'captcha' => ['class'=>'yii\captcha\CaptchaValidator'],
		'compare' => ['class'=>'yii\validators\CompareValidator'],
		'date' => ['class'=>'neon\core\validators\DateValidator'],
		'datetime' => ['class' => 'neon\core\validators\DateValidator', 'type' => DateValidator::TYPE_DATETIME,],
		'time' => ['class' => 'yii\validators\DateValidator', 'type' => DateValidator::TYPE_TIME,],
		'default' => ['class'=>'yii\validators\DefaultValueValidator'],
		'double' => ['class'=>'neon\core\validators\NumberValidator'],
		'each' => ['class'=>'yii\validators\EachValidator'],
		'email' => ['class'=>'neon\core\validators\EmailValidator'],
		'exist' => ['class'=>'yii\validators\ExistValidator'],
		'file' => ['class'=>'yii\validators\FileValidator'],
		'filter' => ['class'=>'yii\validators\FilterValidator'],
		'image' => ['class'=>'yii\validators\ImageValidator'],
		'in' => ['class'=>'yii\validators\RangeValidator'],
		'integer' => ['class' => 'neon\core\validators\NumberValidator', 'integerOnly' => true,],
		'match' => ['class'=>'neon\core\validators\RegularExpressionValidator'],
		'number' => ['class'=>'neon\core\validators\NumberValidator'],
		'required' => ['class'=>'neon\core\validators\RequiredValidator'],
		'safe' => ['class'=>'yii\validators\SafeValidator'],
		'string' => ['class'=>'neon\core\validators\StringValidator'],
		'trim' => ['class' => 'yii\validators\FilterValidator', 'filter' => 'trim', 'skipOnArray' => true,],
		'unique' => ['class'=>'yii\validators\UniqueValidator'],
		'url' => ['class'=>'neon\core\validators\UrlValidator'],
		'ip' => ['class'=>'yii\validators\IpValidator'],
		// client side validators
		'uploading' => ['class' => 'neon\core\validators\UploadingValidator']
	];

	/**
	 * Creates a validator object.
	 * @param string|\Closure $type the validator type. This can be either:
	 *  * a built-in validator name listed in [[builtInValidators]];
	 *  * a method name of the model class;
	 *  * an anonymous function;
	 *  * a validator class name.
	 * @param \yii\base\Model $model the data model to be validated.
	 * @param array|string $attributes list of attributes to be validated. This can be either an array of
	 * the attribute names or a string of comma-separated attribute names.
	 * @param array $params initial values to be applied to the validator properties.
	 * @return Validator the validator
	 */
	public static function createValidator($type, $model, $attributes, $params = [])
	{
		$params['attributes'] = $attributes;

		if ($type instanceof \Closure || ($model->hasMethod($type) && !isset(static::$builtInValidators[$type]))) {
			// method-based validator
			$params['class'] = __NAMESPACE__ . '\InlineValidator';
			$params['method'] = $type;
		} else {
			if (isset(static::$builtInValidators[$type])) {
				$type = static::$builtInValidators[$type];
			}
			if (is_array($type)) {
				// passed in params take precedence over defaults defined in type
				// with the exception of the class property which accounts for
				// arrays defined with ['class' => 'required']
				$params = array_merge($type, $params);

				if (isset($type['class'])) {
					$params['class'] = $type['class'];
				}
			} else {
				$params['class'] = $type;
			}
		}
		return Yii::createObject($params);
	}
}