<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 20/11/2016 18:43
 * @package neon
 */

namespace neon\core\web;

use yii\web\UploadedFile as YiiUploadedFile;
// leaky abstraction here - solution to move this class into firefly
use neon\firefly\services\driveManager\interfaces\IFile;

/**
 * A wrapper for the $_FILES[]
 * Class UploadedFile
 * This should probably be moved into firefly - uploader
 * @package neon\core\web
 */
class UploadedFile extends YiiUploadedFile implements IFile
{

	/**
	 * Store the uploaded file on a firefly disk.
	 *
	 * @return string|false
	 */
	public function save()
	{
		$uuid = neon()->firefly->saveFile($this);
		return $uuid;
	}

	/**
	 * Get the name of the file
	 * This mirrors the \SplFileInfo php object interface
	 * @return string
	 */
	public function getFilename()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getRealPath()
	{
		return $this->tempName;
	}

	/**
	 * Get a filename for the file that is the MD5 hash of the contents.
	 * @deprecated the fileManager handles hashing for us - this is crapy
	 * @param  string  $path
	 * @return string
	 */
	public function hashName($path = null)
	{
		if ($path) {
			$path = rtrim($path, '/').'/';
		}
		return $path.md5_file($this->tempName).'.'.$this->getExtension();
	}
}