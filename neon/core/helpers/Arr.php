<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 15/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\helpers;

use neon\core\interfaces\IArrayable;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use \yii\helpers\ArrayHelper as YiiArrayHelper;
use \ArrayAccess;

class Arr extends YiiArrayHelper
{
	/**
	 * Determine whether the given value is array accessible.
	 *
	 * @param  mixed $value
	 * @return bool
	 */
	public static function accessible($value)
	{
		return is_array($value) || $value instanceof ArrayAccess;
	}

	/**
	 * Specify defaults for an array - if the keys don't exist in $array it 
	 * creates them with the corresponding value in the defaults array.
	 * The defaults array supports nesting by specifying dot notation keys
	 * 
	 * For eg:
	 * ```php
	 * $arr = [
	 *     'msg' => 'My message',
	 *     'other' => 'stuff',
	 * ];
	 * Arr::defaults($arr, [
	 *     'msg' => 'Default hi message',
	 *     'on' => false,
	 *     'some.nested' => 'default'
	 * ])
	 * // =>
	 * [
	 *     'msg' => 'My message',
	 *     'other' => 'stuff',
	 *     'on' => false,
	 *     'some' => [
	 *         'nested' => 'default'
	 *     ]
	 * ]
	 * ```
	 * @param array $array - the array to ensure the defaults exist in
	 */
	public static function defaults($array, $defaults)
	{
		foreach($defaults as $key => $default)
			Arr::set($array, $key, Arr::get($array, $key, $default));
		return $array;
	}

	/**
	 * Flatten a multi-dimensional associative array with dots.
	 *
	 * @param  array $array
	 * @param  string $prepend
	 * @return array
	 */
	public static function dot($array, $prepend = '')
	{
		$results = [];

		foreach ($array as $key => $value) {
			if (is_array($value) && !empty($value)) {
				$results = array_merge($results, static::dot($value, $prepend . $key . '.'));
			} else {
				$results[$prepend . $key] = $value;
			}
		}

		return $results;
	}

	/**
	 * Get all of the given array except for a specified array of items.
	 *
	 * @param  array $array
	 * @param  array|string $keys
	 * @return array
	 */
	public static function except($array, $keys)
	{
		Arr::forget($array, $keys);

		return $array;
	}

	/**
	 * Determine if the given key exists in the provided array.
	 *
	 * @param  \ArrayAccess|array $array
	 * @param  string|int $key
	 * @return bool
	 */
	public static function exists($array, $key)
	{
		if ($array instanceof ArrayAccess) {
			return $array->offsetExists($key);
		}

		return array_key_exists($key, $array);
	}

	/**
	 * Return the first element in an array passing a given truth test.
	 *
	 * ```php
	 * $array = [100, 200, 300];
	 * $value = Arr::first($array, function ($value, $key) {
	 *     return $value >= 150;
	 * });
	 * // returns: 200
	 * ```
	 *
	 * @param  array $array
	 * @param  callable|null $callback Where the first paramter
	 *         is an array items value and second parameter its key.
	 * @param  mixed $default
	 * @return mixed
	 */
	public static function first($array, callable $callback = null, $default = null)
	{
		if (is_null($callback)) {
			if (empty($array)) {
				return value($default);
			}

			foreach ($array as $item) {
				return $item;
			}
		}

		foreach ($array as $key => $value) {
			if (call_user_func($callback, $value, $key)) {
				return $value;
			}
		}

		return value($default);
	}

	/**
	 * Return the last element in an array passing a given truth test.
	 *
	 * @param  array $array
	 * @param  callable|null $callback
	 * @param  mixed $default
	 * @return mixed
	 */
	public static function last($array, callable $callback = null, $default = null)
	{
		if (is_null($callback)) {
			return empty($array) ? value($default) : end($array);
		}

		return static::first(array_reverse($array, true), $callback, $default);
	}

	/**
	 * Remove one or many array items from a given array using "dot" notation.
	 *
	 * @param  array $array
	 * @param  array|string $keys
	 * @return void
	 */
	public static function forget(&$array, $keys)
	{
		$original = &$array;

		$keys = (array)$keys;

		if (count($keys) === 0) {
			return;
		}

		foreach ($keys as $key) {
			// if the exact key exists in the top-level, remove it
			if (static::exists($array, $key)) {
				unset($array[$key]);

				continue;
			}

			$parts = explode('.', $key);

			// clean up before each pass
			$array = &$original;

			while (count($parts) > 1) {
				$part = array_shift($parts);

				if (isset($array[$part]) && is_array($array[$part])) {
					$array = &$array[$part];
				} else {
					continue 2;
				}
			}

			unset($array[array_shift($parts)]);
		}
	}

	/**
	 * Push an item onto the beginning of an array.
	 *
	 * @param  array $array
	 * @param  mixed $value
	 * @param  mixed $key
	 * @return array
	 */
	public static function prepend($array, $value, $key = null)
	{
		if (is_null($key)) {
			array_unshift($array, $value);
		} else {
			$array = [$key => $value] + $array;
		}

		return $array;
	}

	/**
	 * Get a value from the array, and remove it.
	 *
	 * @param  array $array
	 * @param  string $key can use dot notation
	 * @param  mixed $default
	 * @return mixed
	 */
	public static function pull(&$array, $key, $default = null)
	{
		$value = static::get($array, $key, $default);

		static::forget($array, $key);

		return $value;
	}

	/**
	 * Get an item from an array using "dot" or array or string notation.
	 *
	 * @param  \ArrayAccess|array $array
	 * @param  string|\Closure|array $key
	 * @param  mixed $default
	 * @return mixed
	 */
	public static function get($array, $key, $default = null)
	{
		return self::getValue($array, $key, value($default));
	}

	/**
	 * Get property from an array - throws an exception if it does not exist
	 *
	 * @param \ArrayAccess|array $array
	 * @param string $key - key in the array
	 * @param string message - the error message to use if the key does not exist
	 * @throws \InvalidArgumentException if the property $key does not exist in the array
	 * @return mixed
	 */
	public static function getRequired($array, $key, $message=null)
	{
		if (!array_key_exists($key, $array)) {
			throw new \InvalidArgumentException($message ?: "You must define a '$key' property.");
		}
		return static::get($array, $key);
	}

	/**
	 * Get an item from an array or object using "dot" notation.
	 * It also works with Collection objects and allows for limited wildcard useage in dot notation
	 * for e.g. 'my.deep.nested.*';
	 *
	 * @param  mixed $target
	 * @param  string|array $key
	 * @param  mixed $default
	 * @return mixed
	 */
	public static function getData($target, $key, $default = null)
	{
		if (is_null($key)) {
			return $target;
		}

		$key = is_array($key) ? $key : explode('.', $key);

		while (!is_null($segment = array_shift($key))) {
			if ($segment === '*') {
				if ($target instanceof Collection) {
					$target = $target->all();
				} elseif (!is_array($target)) {
					return value($default);
				}

				$result = Arr::pluck($target, $key);

				return in_array('*', $key) ? Arr::collapse($result) : $result;
			}

			if (Arr::accessible($target) && Arr::exists($target, $segment)) {
				$target = $target[$segment];
			} elseif (is_object($target) && isset($target->{$segment})) {
				$target = $target->{$segment};
			} else {
				return value($default);
			}
		}
		return $target;
	}

	/**
	 * Set an array item to a given value using "dot" notation.
	 *
	 * If no key is given to the method, the entire array will be replaced.
	 *
	 * @param  array $array
	 * @param  string $key
	 * @param  mixed $value
	 * @return array
	 */
	public static function set(&$array, $key, $value)
	{
		if (is_null($key)) {
			return $array = $value;
		}

		$keys = explode('.', $key);

		while (count($keys) > 1) {
			$key = array_shift($keys);

			// If the key doesn't exist at this depth, we will just create an empty array
			// to hold the next value, allowing us to create the arrays to hold final
			// values at the correct depth. Then we'll keep digging into the array.
			if (!isset($array[$key]) || !is_array($array[$key])) {
				$array[$key] = [];
			}

			$array = &$array[$key];
		}

		$array[array_shift($keys)] = $value;

		return $array;
	}

	/**
	 * Filter the array using the given callback.
	 *
	 * ```php
	 * $array = [100, '200', 300, '400', 500];
	 *
	 * $array = Arr::where($array, function ($value, $key) {
	 *     return is_string($value);
	 * });
	 *
	 * // [1 => 200, 3 => 400]
	 * ```
	 *
	 * @param  array $array
	 * @param  callable $callback the callable format should be function ($value, $key) {}
	 *
	 * @return array
	 */
	public static function where($array, callable $callback)
	{
		return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
	}

	/**
	 * Group an associative array by a field or using a callback.
	 *
	 * @param  callable|string $groupBy
	 * @param  bool $preserveKeys
	 * @return array
	 */
	public static function groupBy($array, $groupBy, $preserveKeys = false)
	{
		return (new Collection($array))->groupBy($groupBy, $preserveKeys)->toArray();
	}

	/**
	 * Determines if an array is associative.
	 *
	 * An array is "associative" if it doesn't have sequential numerical keys beginning with zero.
	 *
	 * @param  array $array
	 * @return bool
	 */
	public static function isAssoc(array $array)
	{
		$keys = array_keys($array);

		return array_keys($keys) !== $keys;
	}

	/**
	 * Get a subset of the items from the given array.
	 *
	 * @param  array $array
	 * @param  array|string $keys
	 * @return array
	 */
	public static function only($array, $keys)
	{
		return array_intersect_key($array, array_flip((array)$keys));
	}

	/**
	 * Pluck an array of values from an array.
	 * Inspired by Laravels Pluck method
	 *
	 * example usage:
	 *
	 * ```php
	 * $array = [
	 *     ['user' => ['id' => 1, 'name' => 'Steve']],
	 *     ['user' => ['id' => 2, 'name' => 'Bob']]
	 * ]
	 *
	 * Arr::pluck($array, 'user.name')
	 *
	 * // Returns: ['Steve', 'Bob'];
	 * ```
	 *
	 * You may also specify how you wish the resulting list to be keyed:
	 *
	 * ```php
	 * $array = Arr::pluck($array, 'user.name', 'user.id');
	 *
	 * // Returns: [1 => 'Steve', 2 => 'Bob'];
	 * ```
	 *
	 * @param  array $array
	 * @param  string|array $value
	 * @param  string|array|null $key
	 * @return array
	 */
	public static function pluck($array, $value, $key = null)
	{
		$results = [];

		$value = is_string($value) ? explode('.', $value) : $value;
		$key = is_null($key) || is_array($key) ? $key : explode('.', $key);

		foreach ($array as $item) {
			$itemValue = Arr::getData($item, $value);

			// If the key is "null", we will just append the value to the array and keep
			// looping. Otherwise we will key the array using the value of the key we
			// received from the developer. Then we'll return the final array form.
			if (is_null($key)) {
				$results[] = $itemValue;
			} else {
				$itemKey = Arr::getData($item, $key);

				$results[$itemKey] = $itemValue;
			}
		}

		return $results;
	}

	/**
	 * If the given value is not an array, wrap it in one.
	 *
	 * @param  mixed $value
	 * @return array
	 */
	public static function wrap($value)
	{
		return !is_array($value) ? [$value] : $value;
	}

	/**
	 * Convert the array into a query string.
	 *
	 * @param  array  $array
	 * @return string
	 */
	public static function query($array)
	{
		return http_build_query($array, '', '&', PHP_QUERY_RFC3986);
	}

	/**
	 * Collapse an array of arrays into a single array.
	 *
	 * @param  array $array
	 * @return array
	 */
	public static function collapse($array)
	{
		$results = [];

		foreach ($array as $values) {
			if ($values instanceof Collection) {
				$values = $values->all();
			} elseif (!is_array($values)) {
				continue;
			}

			$results = array_merge($results, $values);
		}

		return $results;
	}

	/**
	 * Replace keys in an array with a different name
	 * @param array $array
	 * @param $replace ['findKey' => 'replacekey']
	 * @return array
	 */
	public static function replaceKeys($array, $replace)
	{
		foreach ($replace as $find => $rep) {
			if (isset($array[$find])) {
				$array[$rep] = $array[$find];
				unset($array[$find]);
			}
		}
		return $array;
	}

	/**
	 * similar to Arr::only(...) but with the ability to remap the extracted keys
	 *
	 * if $fields is non-associative the specified fields will be extracted (identical to Arr::only)
	 * if $fields is associative then the keys will be extracted and remapped using the values
	 *
	 * @param array $data
	 * @param array $keys
	 * @param bool $strict if this is true then an exception is throw if any keys are not found
	 * @return array
	 * @throws \Exception
	 */
	public static function extractKeys(array $data, array $keys, bool $strict=false) : array
	{
		$isAssoc = (array_keys($keys) !== range(0, count($keys) - 1));

		// this array contains what needs to be extracted as the keys (not the values)
		// to prepare for array_intersect_key
		$extractKeys = $isAssoc
			? $keys
			: array_flip($keys);

		$results = array_intersect_key($data, $extractKeys);
		if ($strict && count($results)<count($extractKeys)) {
			// it's strange there's no php method to do the following
			$missingKeys = [];
			foreach (array_keys($extractKeys) as $checkKey) {
				if (!array_key_exists($checkKey,$data)) {
					$missingKeys[] = $checkKey;
				}
			}
			throw new \Exception("The following fields were specified which were not found in the data : ".implode(", ",$missingKeys));
		}

		if (!$isAssoc) return $results;

		$newResults = [];
		foreach ($keys as $oldKey=>$newKey) {
			$newResults[$newKey] = array_key_exists($oldKey,$results) ? $results[$oldKey] : null;
		}
		return $newResults;
	}

	/**
	 * checks all elements in an array are of the specified class
	 * @param array $arr
	 * @param string $class
	 * @return bool
	 */
	public static function arrayOfClasses(array $arr, string $class): bool
	{
		foreach($arr as $elem) {
			if (!is_object($elem) || get_class($elem)!==$class){
				return false;
			}
		}
		return true;
	}

}
