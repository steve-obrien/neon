<?php
namespace neon\core\traits;

use neon\core\helpers\Arr;
use neon\core\interfaces\IArrayable;
use neon\core\interfaces\IProperties;
use \yii\base\ArrayableTrait;
use yii\helpers\Json;
use yii\web\JsExpression;

/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 07/01/2018
 * @package neon
 */
trait PropertiesTrait
{
	public function toArray($except=[])
	{
		$array= [];
		$properties = $this->getProperties();
		foreach($properties as $key => $value) {
			if (in_array($value, $except)) { continue; }
			list($prop, $value) = $this->resolveProperty($key, $value);
			$array[$prop] = $this->valueToArray($value);
		}
		if (!isset($array['class']))
			$array['class'] = get_called_class();
		return $array;
	}

	/**
	 * Resolves properties
	 * @param $key
	 * @param $value
	 * @return array
	 */
	public function resolveProperty($key, $value)
	{
		if (is_int($key)) $key = $value;
		$value = is_string($value) ? $this->$value : call_user_func($value, $this, $key);

		return [$key, $value];
	}

	public function valueToArray($value)
	{
		if (is_array($value)) {
			foreach($value as $key => $val) {
				$value[$key] = $this->valueToArray($val);
			}
		} else if (is_object($value) && $value instanceof IArrayable) {
			$value = $value->toArray();
		} else if ($value instanceof JsExpression) {
			//dd($value);
		}
		return $value;
	}

	public function getDefaultProperties()
	{
		$reflectionClass = new \ReflectionClass(get_class($this));
		return $reflectionClass->getDefaultProperties();
	}

	/**
	 * Implementation of JsonSerializable
	 * Serializes all the properties into property_key => property_value
	 * __NOTE:__ this returns an array as is expected by the interface. The name is a little misleading
	 * @return array
	 */
	public function jsonSerialize(): mixed
	{
		$array = $this->toArray();
		return $array;
	}

	/**
	 * @inheritdoc
	 */
	public function toJson()
	{
		profile_begin('IProperties: toJson');
		// call the serialise first. This is so that any exceptions thrown
		// will display properly. If left to json_encode, it captures the
		// exceptions and unhelpfully says it couldn't serialise but not why
		$serialise = $this->jsonSerialize();
		$json = $this->jsonEncode($serialise);
		profile_end('IProperties: toJson');
		return $json;
	}

	/**
	 * Encode JSON correctly
	 * @param $properties
	 * @return string
	 */
	public function jsonEncode($properties)
	{
		// by using JSON_FORCE_OBJECT - we make php and javascript encoding more compatible
		// php arrays will always be represented as javascript objects.
		// this makes associative php arrays passable - indexed arrays will be output like:
		// {"0":"value at index 0", "1":"item at index 2"}
		$options = JSON_HEX_QUOT 
		| JSON_HEX_APOS 
		| JSON_UNESCAPED_UNICODE 
		| JSON_UNESCAPED_SLASHES 
		| JSON_NUMERIC_CHECK 
		| JSON_FORCE_OBJECT //(potentially solved with JSON_OBJECT_AS_ARRAY in later versions keeping javascript and php array info in tact)
	 	| (neon()->isDevMode() ? JSON_PRETTY_PRINT : 0);
		return Json::encode($properties, $options);
	}

	public function getClass()
	{
		return get_class($this);
	}

}
