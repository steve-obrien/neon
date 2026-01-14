<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\test;

use neon\core\helpers\Str;
use neon\core\helpers\Hash;

/**
 * Class NeonUnitTest
 *
 * A base class to extend from basic unit tests - essentially providing common helper functions
 *
 * @author Steve O'Brien - Newicon Ltd
 * @package neon\tests\framework
 */
class Unit extends \Codeception\Test\Unit
{
	protected function _before()
	{
	}

	protected function _after()
	{
	}

	/**
	 * create a random string of given size.
	 * @param  integer  $size  size of the string required
	 * @return  string
	 */
	protected static function randomString($size=20)
	{
		$source = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
		$sourceLen = strlen($source);
		$string = '';
		for ($i=0; $i<$size; $i++)
			$string .= substr($source, rand(0, $sourceLen-1), 1);
		return $string;
	}

	public function assertArraySubset(array $subset, array $array, string $message = ''): void
	{
		foreach ($subset as $key => $value) {
			$this->assertArrayHasKey($key, $array, $message);

			if (is_array($value)) {
				$this->assertArraySubset($value, $array[$key], $message);
			} else {
				$this->assertEquals($value, $array[$key], $message);
			}
		}
	}

	/**
	 * Typical usage:
	 * ```php
	 * $this->debugShell(get_defined_vars());
	 * ```
	 * @param  array  $vars - use get_defined_vars()
	 * @param  null  $bind - the context for $this
	 */
	protected function debugShell(array $vars = array(), $bind = null)
	{
		\Psy\Shell::debug($vars);
	}

	/**
	 * Returns the number of bytes for integers like `128K` | `128M` | `2G` | `1T` | `1P`
	 *
	 * @param $string
	 * @return mixed
	 */
	protected function toBytes($string)
	{
		return Str::toBytes($string);
	}

	/**
	 * Return a formatted string representing the number of bytes like '128M'
	 *
	 * @param  int  $size  in bytes
	 * @param  int  $precision
	 * @return string
	 */
	protected function formatBytes($size, $precision = 2)
	{
		return Str::formatBytes($size, $precision);
	}

	/**
	 * Return time in database format
	 * @param int $time  the time if not now
	 * @return datetime in form yyyy-mm-dd hh-mm-ss
	 */
	protected function datetime($time=null, $format='Y-m-d H:i:s')
	{
		return date($format, ($time===null ? time() : $time));
	}

	/**
	 * Return a new uuid64
	 */
	protected function uuid64()
	{
		return Hash::uuid64();
	}

}
