<?php

/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 17/11/2016 17:03
 * @package neon
 */

namespace neon\core\helpers;

use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class Str - is a string helper class to provide handy string functions
 * This essentially merges the Yii Inflector helper and the Yii StringHelper as well as
 * providing a few additional functions too.
 *
 * @package neon\core\helpers
 */
class Hash
{
	/**
	 * Generates a uuid
	 * @param  string  $separator the section separator for the uuid - normally '-'
	 * @return string
	 */
	public static function uuid($separator = '-')
	{
		$sha = sha1(uniqid('', true) . rand(1000000, 9999999));
		return substr($sha, 0, 8) . $separator . substr($sha, 9, 4) . $separator . '3' . substr($sha, 15, 3) . $separator . '8' . substr($sha, 19, 3) . $separator . substr($sha, 22, 12);
	}

	/**
	 * Generates a 22 character long uuid using 0-9A-Za-z_-
	 * @param string encoding - characters used for the encoding
	 * @return string
	 */
	public static function uuid64($encoding = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZxy')
	{
		// replace uuid64 with ulids - better db indexing and orderable by date
		return self::ulid();
	}

	/**
	 * Generate a html friendly ID
	 * @return string
	 */
	public static function id()
	{
		return self::uuid64('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghiZYX');
	}

	/**
	 * Converts a uuid into a uuid64. If the uuid is already a uuid64
	 * it is simply returned.
	 * @param string $uuid
	 * @param string encoding - characters used for the encoding
	 * @return string
	 */
	public static function uuid2uuid64($uuid, $encoding = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZxy')
	{
		if (strlen($uuid) == 22)
			return $uuid;

		// remove any hyphens
		$uuid = str_replace('-', '', $uuid);

		$uuid .= '0'; // 33 hexadecimal characters = 22 64-cimal chars.
		// to convert from base-16 to base-64 (not Base64)
		// 64 characters
		$uuid64 = '';
		$position = 0;
		while ($position < 33) {
			$snip = substr($uuid, $position, 3);
			$number = hexdec($snip);
			$uuid64 .= $encoding[(int)floor($number / 64)] . $encoding[(int)($number % 64)];
			$position += 3;
		}
		return $uuid64;
	}

	public static function ulid()
	{
		$timestamp = (int) (microtime(true) * 1000); // Current time in milliseconds
		$random = random_bytes(11); // 80 bits of randomness
		$ulid = self::base62Encode($timestamp) . self::binaryToBase62($random);
		// ensure length
		$ulid = str_pad($ulid, 22, '0', STR_PAD_RIGHT);
		$ulid = substr($ulid, 0, 22);
		return $ulid;
	}

	public static function binaryToBase62($binaryData)
	{
		// Convert binary data to hexadecimal
		$hexData = bin2hex($binaryData);

		// Convert the hexadecimal data to a decimal number
		$decimalData = '0';
		$length = strlen($hexData);
		for ($i = 0; $i < $length; $i++) {
			$decimalData = bcadd(bcmul($decimalData, '16'), hexdec($hexData[$i]));
		}

		// Encode the decimal number to base62
		return self::base62Encode($decimalData);
	}

	public static function base62Encode($input)
	{
		$alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		$base = strlen($alphabet);
		$encoded = '';

		// Ensure the input is treated as a string for BCMath functions
		$number = (string)$input;

		while (bccomp($number, '0') > 0) {
			$remainder = bcmod($number, $base);
			$encoded = $alphabet[(int)$remainder] . $encoded;
			$number = bcdiv($number, $base, 0);
		}

		return $encoded;
	}

	/**
	 * Whether the given string is in the correct format of a uuid64
	 * @return boolean
	 */
	public static function isUuid64($test)
	{
		return (is_string($test) && (preg_match('/^[a-zA-Z0-9-_]{22}$/', $test) === 1));
	}

	/**
	 * Get a token - that can be used to get a fresh instance of the object with the same configurations applied
	 * To get the object back from the token call self::getObjectFromToken
	 * @return string token
	 * @throws \ReflectionException
	 */
	public static function setObjectToToken($object, $duration = 86400)
	{
		// get public properties of child class
		$data = \Neon::getObjectVars($object);
		$data['class'] = get_class($object);
		if (method_exists($object, 'toArray'))
			$data = array_merge($data, $object->toArray(['objectToken']));
		$token = sha1(serialize($data) . env('NEON_SECRET'));
		neon()->cache->set($token, $data, $duration); // 24 hour
		return neon()->security->maskToken($token);
	}

	/**
	 * Get an object from a previously defined token
	 * @param string $token
	 * @param string $expectClass - specify additional class check
	 * @return object
	 * @throws BadRequestHttpException
	 * @throws NotFoundHttpException
	 */
	public static function getObjectFromToken($token, $expectClass = null)
	{
		$data = neon()->cache->get(neon()->security->unmaskToken($token));
		if ($data === null)
			throw new NotFoundHttpException('Does not exist or has timed out');

		if (!isset($data['class']) || !class_exists($data['class']))
			throw new BadRequestHttpException('Not a valid class');

		// instantiate the class
		$class = Arr::remove($data, 'class');
		$object = new $class($data);
		if ($expectClass !== null && !$object instanceof $expectClass)
			throw new BadRequestHttpException('Not a valid class');

		return $object;
	}
}
