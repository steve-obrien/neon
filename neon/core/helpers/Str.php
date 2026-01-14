<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 17/11/2016 17:03
 * @package neon
 */

namespace neon\core\helpers;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use \yii\helpers\Inflector;

/**
 * Class Str - is a string helper class to provide handy string functions
 * This essentially merges the Yii Inflector helper and the Yii StringHelper as well as
 * providing a few additional functions too.
 *
 * @package neon\core\helpers
 */
class Str extends \yii\helpers\StringHelper
{
	/**
	 * Converts a word to its plural form.
	 * Note that this is for English only!
	 * For example, 'apple' will become 'apples', and 'child' will become 'children'.
	 *
	 * @param string $word the word to be pluralized
	 * @return string the pluralized word
	 */
	public static function pluralize($word)
	{
		return Inflector::pluralize($word);
	}

	/**
	 * Returns the singular of the $word
	 *
	 * @param string $word the english word to singularize
	 * @return string Singular noun.
	 */
	public static function singularize($word)
	{
		return Inflector::pluralize($word);
	}

	/**
	 * Converts an underscored or CamelCase word into a English
	 * sentence.
	 *
	 * @param string $words
	 * @param boolean $ucAll whether to set all words to uppercase
	 * @return string
	 */
	public static function titleize($words, $ucAll = false)
	{
		return Inflector::titleize($words, $ucAll);
	}

	/**
	 * Returns given word as CamelCased
	 * Converts a word like "send_email" to "SendEmail". It
	 * will remove non alphanumeric character from the word, so
	 * "who's online" will be converted to "WhoSOnline"
	 *
	 * @see variablize()
	 * @param string $word the word to CamelCase
	 * @return string
	 */
	public static function camelize($word)
	{
		return Inflector::camelize($word);
	}

	/**
	 * Converts a CamelCase name into space-separated words.
	 * For example, 'PostTag' will be converted to 'Post Tag'.
	 *
	 * @param string $name the string to be converted
	 * @param boolean $ucwords whether to capitalize the first letter in each word
	 * @return string the resulting words
	 */
	public static function camel2words($name, $ucwords = true)
	{
		return Inflector::camel2words($name, $ucwords);
	}

	/**
	 * Converts a CamelCase name into an ID in lowercase.
	 * Words in the ID may be concatenated using the specified character (defaults to '-').
	 * For example, 'PostTag' will be converted to 'post-tag'.
	 *
	 * @param string $name the string to be converted
	 * @param string $separator the character used to concatenate the words in the ID
	 * @param boolean|string $strict whether to insert a separator between two consecutive uppercase chars, defaults to false
	 * @return string the resulting ID
	 */
	public static function camel2Dash($name, $separator = '-', $strict = false)
	{
		return Inflector::camel2id($name, $separator, $strict);
	}

	/**
	 * Converts a dash string into a CamelCase name.
	 * Words in the dashed string separated by `$separator` (defaults to '-') will be concatenated into a CamelCase name.
	 * For example, 'post-tag' is converted to 'PostTag'.
	 *
	 * @param string $id the ID to be converted
	 * @param string $separator the character used to separate the words in the ID
	 * @return string the resulting CamelCase name
	 */
	public static function dash2camel($id, $separator = '-')
	{
		return Inflector::id2camel($id, $separator);
	}

	/**
	 * Converts a underscored word like `my_word` into its camel cased equivelant like `MyWord`
	 *
	 * @param $word
	 * @return string
	 */
	public static function underscore2Camel($word)
	{
		return Inflector::camelize($word);
	}

	/**
	 * Convert camel cased words to underscore for example
	 *
	 * @alias self::underscore
	 * @param $words
	 * @return string
	 */
	public static function camel2underscore($words)
	{
		return Inflector::underscore($words);
	}

	/**
	 * Converts any "CamelCased" into an "underscored_word".
	 *
	 * @param string $words the word(s) to underscore
	 * @return string
	 */
	public static function underscore($words)
	{
		return Inflector::underscore($words);
	}

	/**
	 * Returns a human-readable string from $word
	 *
	 * @param string $word the string to humanize
	 * @param boolean $ucAll whether to set all words to uppercase or not
	 * @return string
	 */
	public static function humanize($word, $ucAll = false)
	{
		return Inflector::humanize($word, $ucAll);
	}

	/**
	 * Same as camelize but first char is in lowercase.
	 * Converts a word like "send_email" to "sendEmail". It
	 * will remove non alphanumeric character from the word, so
	 * "who's online" will be converted to "whoSOnline"
	 *
	 * @param string $word to lowerCamelCase
	 * @return string
	 */
	public static function variablize($word)
	{
		return Inflector::variablize($word);
	}

	/**
	 * Converts a class name to its table name (pluralized)
	 * naming conventions. For example, converts "Person" to "people"
	 *
	 * @param string $className the class name for getting related table_name
	 * @return string
	 */
	public static function tableize($className)
	{
		return static::pluralize(static::underscore($className));
	}

	/**
	 * Convert a string to snake case.
	 *
	 * @param  string  $value
	 * @param  string  $delimiter
	 * @return string
	 */
	public static function snake($value, $delimiter = '_')
	{
		$key = $value;

		if (isset(static::$snakeCache[$key][$delimiter])) {
			return static::$snakeCache[$key][$delimiter];
		}

		if (! ctype_lower($value)) {
			$value = preg_replace('/\s+/u', '', $value);

			$value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));
		}

		return static::$snakeCache[$key][$delimiter] = $value;
	}

	/**
	 * Convert the given string to lower-case.
	 *
	 * @param  string  $value
	 * @return string
	 */
	public static function lower($value)
	{
		return mb_strtolower($value, 'UTF-8');
	}

	/**
	 * Returns a string with all spaces converted to given replacement,
	 * non word characters removed and the rest of characters transliterated.
	 *
	 * If intl extension isn't available uses fallback that converts latin characters only
	 * and removes the rest. You may customize characters map via $transliteration property
	 * of the helper.
	 *
	 * @param string $string An arbitrary string to convert
	 * @param string $replacement The replacement to use for spaces
	 * @param boolean $lowercase whether to return the string in lowercase or not. Defaults to `true`.
	 * @return string The converted string.
	 */
	public static function slug($string, $replacement = '-', $lowercase = true)
	{
		return Inflector::slug($string, $replacement, $lowercase);
	}

	/**
	 * Returns transliterated version of a string.
	 *
	 * If intl extension isn't available uses fallback that converts latin characters only
	 * and removes the rest. You may customize characters map via $transliteration property
	 * of the helper.
	 *
	 * @param string $string input string
	 * @param string|\Transliterator $transliterator either a [[Transliterator]] or a string
	 * from which a [[Transliterator]] can be built.
	 * @return string
	 * @since 2.0.7 this method is public.
	 */
	public static function transliterate($string, $transliterator = null)
	{
		return Inflector::transliterate($string, $transliterator);
	}

	/**
	 * Converts a table name to its class name. For example, converts "people" to "Person"
	 *
	 * @param string $tableName
	 * @return string
	 */
	public static function classify($tableName)
	{
		return static::camelize(static::singularize($tableName));
	}

	/**
	 * Converts number to its ordinal English form. For example, converts 13 to 13th, 2 to 2nd ...
	 *
	 * @param integer $number the number to get its ordinal value
	 * @return string
	 */
	public static function ordinalize($number)
	{
		return Inflector::ordinalize($number);
	}

	/**
	 * Converts a list of words into a sentence.
	 *
	 * Special treatment is done for the last few words. For example,
	 *
	 * ```php
	 * $words = ['Spain', 'France'];
	 * echo Inflector::sentence($words);
	 * // output: Spain and France
	 *
	 * $words = ['Spain', 'France', 'Italy'];
	 * echo Inflector::sentence($words);
	 * // output: Spain, France and Italy
	 *
	 * $words = ['Spain', 'France', 'Italy'];
	 * echo Inflector::sentence($words, ' & ');
	 * // output: Spain, France & Italy
	 * ```
	 *
	 * @param array $words the words to be converted into an string
	 * @param string $twoWordsConnector the string connecting words when there are only two
	 * @param string $lastWordConnector the string connecting the last two words. If this is null, it will
	 * take the value of `$twoWordsConnector`.
	 * @param string $connector the string connecting words other than those connected by
	 * $lastWordConnector and $twoWordsConnector
	 * @return string the generated sentence
	 * @since 2.0.1
	 */
	public static function sentence(array $words, $twoWordsConnector = ' and ', $lastWordConnector = null, $connector = ', ')
	{
		return Inflector::sentence($words, $twoWordsConnector, $lastWordConnector, $connector);
	}

	/**
	 * Determine if a given string contains a given substring.
	 *
	 * @param  string  $haystack
	 * @param  string|array  $needles
	 * @return bool
	 */
	public static function contains($haystack, $needles)
	{
		foreach ((array) $needles as $needle) {
			if ($needle != '' && mb_strpos($haystack, $needle) !== false) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Replace the first occurrence of a given value in the string.
	 * This is case sensitive
	 *
	 * @param  string  $search
	 * @param  string  $replace
	 * @param  string  $subject
	 * @return string
	 */
	public static function replaceFirst($search, $replace, $subject)
	{
		$position = strpos($subject, $search);
		return $position === false ? $subject :
			substr_replace($subject, $replace, $position, strlen($search));
	}

	/**
	 * Replace the last occurrence of a given value in the string.
	 *
	 * @param  string  $search
	 * @param  string  $replace
	 * @param  string  $subject
	 * @return string
	 */
	public static function replaceLast($search, $replace, $subject)
	{
		$position = strrpos($subject, $search);
		if ($position !== false) {
			return substr_replace($subject, $replace, $position, strlen($search));
		}
		return $subject;
	}

	/**
	 * Replace the first occurrence of a given value in the string.
	 * This is case insensitive
	 *
	 * @param  string  $search
	 * @param  string  $replace
	 * @param  string  $subject
	 * @return string
	 */
	public static function iReplaceFirst($search, $replace, $subject)
	{
		$position = stripos($subject, $search);
		return $position === false ? $subject :
			substr_replace($subject, $replace, $position, strlen($search));
	}

	/**
	 * Limit the number of words in a string.
	 *
	 * @param  string  $value
	 * @param  int     $words
	 * @param  string  $end
	 * @return string
	 */
	public static function words($value, $words = 100, $end = '...')
	{
		preg_match('/^\s*+(?:\S++\s*+){1,'.$words.'}/u', $value, $matches);

		if (! isset($matches[0]) || static::length($value) === static::length($matches[0])) {
			return $value;
		}

		return rtrim($matches[0]).$end;
	}

	/**
	 * Return the length of the given string.
	 *
	 * @param  string  $value
	 * @return int
	 */
	public static function length($value)
	{
		return mb_strlen($value);
	}

	/**
	 * Returns the number of bytes as integer from strings like `128K` | `128M` | `2G` | `1T` | `1P`
	 *
	 * @param  string  $string  A bytes string like 128M
	 * @return int
	 */
	public static function toBytes($string)
	{
		sscanf ($string, '%u%c', $number, $suffix);
		if (isset ($suffix)) {
			$number = $number * pow (1024, strpos (' KMGTP', strtoupper($suffix)));
		}
		return $number;
	}

	/**
	 * Output a formatted size like '128M' from bytes.
	 *
	 * @param  int  $size
	 * @param  int  $precision
	 * @return string
	 */
	public static function formatBytes($size, $precision = 2)
	{
		if ($size <= 0) {
			return "0 bytes";  // or handle the error as per your requirements
		}
		$base = log($size, 1024);
		$suffixes = array('', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB');

		return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
	}

	/**
	 * Generate a more truly "random" alpha-numeric string.
	 *
	 * @param  int  $length
	 * @throws \Exception - if random_bytes it was not possible to gather sufficient entropy
	 * @return string
	 */
	public static function random($length = 16)
	{
		return neon()->security->generateRandomString($length);
	}

	/**
	 * Get a random string with a random length between the min and max values specified
	 *
	 * @param int $min
	 * @param int $max
	 * @throws \Exception - if random_bytes it was not possible to gather sufficient entropy
	 * @return string
	 */
	public static function randomBetween($min, $max)
	{
		$str = self::random($max);
		return substr($str, 0, mt_rand($min, $max));
	}

	/**
	 * Begin a string with a single instance of a given value.
	 *
	 * @param  string  $value
	 * @param  string  $prefix
	 * @return string
	 */
	public static function start($value, $prefix)
	{
		$quoted = preg_quote($prefix, '/');

		return $prefix.preg_replace('/^(?:'.$quoted.')+/u', '', $value);
	}

	/**
	 * Cap a string with a single instance of a given value.
	 *
	 * @param  string  $value
	 * @param  string  $cap
	 * @return string
	 */
	public static function finish($value, $cap)
	{
		$quoted = preg_quote($cap, '/');

		return preg_replace('/(?:'.$quoted.')+$/u', '', $value).$cap;
	}

	/**
	 * Determine if a given string matches a given pattern.
	 *
	 * @param  string|array  $pattern
	 * @param  string  $value
	 * @return bool
	 */
	public static function is($pattern, $value)
	{
		$patterns = Arr::wrap($pattern);

		if (empty($patterns)) {
			return false;
		}

		foreach ($patterns as $pattern) {
			// If the given value is an exact match we can return true.
			// Otherwise, we will translate asterisks and do an
			// actual pattern match against the two strings to see if they match.
			if ($pattern == $value) {
				return true;
			}

			$pattern = preg_quote($pattern, '#');

			// Asterisks are translated into zero-or-more regular expression wildcards
			// to make it convenient to check if the strings starts with the given
			// pattern such as "library/*", making any string check convenient.
			$pattern = str_replace('\*', '.*', $pattern);
			
			if (preg_match('#^'.$pattern.'\z#u', $value) === 1) {
				return true;
			}
		}

		return false;
	}

	/**
	 * checks for numeric value optionally with whitelisted unit(s)
	 * @param $value
	 * @param array|string $allowedUnits
	 * @return bool
	 */
	public static function validateNumericWithUnits($value, $allowedUnits=[])
	{
		if (!$allowedUnits) return is_numeric($value);
		if (is_string($allowedUnits)) $allowedUnits=[$allowedUnits];
		foreach ($allowedUnits as $unit) {
			$checkNumber = substr($value,0, -strlen($unit));
			$checkUnit = substr($value, -strlen($unit));
			if ($checkUnit==$unit && is_numeric($checkNumber)) {
				return true;
			}
		}
		return false;
	}
}
