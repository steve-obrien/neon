<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 05/01/2018 15:24
 * @package neon
 */

namespace neon\core\helpers;

use \yii\helpers\BaseFormatConverter;
use yii\i18n\Formatter;

/**
 * Class Converter for useful and often used conversions of data formats
 * @package neon\core\helpers
 */
class Converter extends BaseFormatConverter
{
	/**
	 * Convert a date string from one format to another
	 *
	 * @param string $date - a date string in the format specified by $fromFormat
	 * @param string $fromFormat - php datetime format
	 * @param string $toFormat - php datetime format
	 * @param string $type - one of 'date' | 'datetime' | 'time'
	 * @return null|string date in format defined by $toFormat
	 */
	public static function convertDate($date, $fromFormat, $toFormat, $type='date')
	{
		$date = \DateTime::createFromFormat(self::convertDateFormatToPhp($fromFormat), $date);
		return is_bool($date) ? null : $date->format(self::convertDateFormatToPhp($toFormat, $type));
	}

	/**
	 * Converts a string date format into the the correct format for JQuery UI
	 * [jQuery UI date format]: http://api.jqueryui.com/datepicker/#utility-formatDate
	 *
	 * If the $format param begins with php: then the php format is assumed
	 * if not specified assumed the ICU format
	 * [ICU format]: http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax
	 * [php date() function format]: http://php.net/manual/en/function.date.php
	 *
	 * @param string $format - the date format either `php:d/m/Y` or ICU format
	 * @return string
	 */
	public static function convertDateFormatToJui($format)
	{
		if (strncmp($format, 'php:', 4) === 0)
			return self::convertDatePhpToJui(substr($format, 4));
		if (strncmp($format, 'icu:', 4) === 0)
			return self::convertDateIcuToJui(substr($format, 4));
		return self::convertDateIcuToJui($format);
	}

	/**
	 * Converts date format strings like `icu:dd/MM/yyyy` and `php:d/m/Y` into a php date string format
	 * if no format style prefix ['php:', 'icu:'] is given then assumes the passed in string is already a
	 * valid icu date string.
	 *
	 * @param string $format
	 * @param string $type - one of 'date' | 'datetime' | 'time'
	 * @return string - string representing a php date format
	 */
	public static function convertDateFormatToPhp($format, $type='date')
	{
		if (strncmp($format, 'php:', 4) === 0)
			return substr($format, 4);
		if (strncmp($format, 'icu:', 4) === 0)
			return self::convertDateIcuToPhp(substr($format, 4), $type);
		// assume icu format by default - this is the default used by neon()->formatter->dateFormat
		return self::convertDateIcuToPhp($format, $type);
	}
}