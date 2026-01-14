<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2008 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\helpers;

use \neon\core\helpers\Str;

/**
 * Class FileHelper
 * @author Steve O'Brien - Newicon Ltd
 * @package neon\core\helpers
 */
class File extends \yii\helpers\FileHelper
{
	/**
	 * Return the number in bytes representing the max upload size.
	 * Considers both the PHP post max size and the PHP upload max size.
	 * Requires the ini_get function
	 *
	 * @return int
	 */
	public static function maxUploadSize()
	{
		$postMax =  Str::toBytes(ini_get('post_max_size'));
		$uploadMax = Str::toBytes(ini_get('upload_max_filesize'));
		return min($postMax, $uploadMax);
	}

	/**
	 * Return a nicely formatted string representing the max upload size
	 *
	 * @return string
	 */
	public static function maxUploadSizeString()
	{
		return Str::formatBytes(self::maxUploadSize());
	}

	/**
	 * Create a file at a give path or alias path
	 * This function is similar to file_put_contents however it will ensure the nested directory path exists
	 *
	 * @param string $path
	 * @param string $content
	 * @throws \yii\base\Exception if the directory could not be created (i.e. php error due to parallel changes)
	 * @return bool|int - the number of bytes written to the file, or false on failure
	 */
	public static function createFile($path, $content)
	{
		static::createDirectory(dirname($path));
		return file_put_contents($path, $content);
	}
}