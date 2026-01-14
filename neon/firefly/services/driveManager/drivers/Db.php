<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 23/11/2016 18:39
 * @package neon
 */

namespace neon\firefly\services\driveManager\drivers;

use neon\firefly\services\fileManager\FileExceedsMemoryLimitException;
use \yii\base\Component;
use League\Flysystem\Util;
use League\Flysystem\Config;
use League\Flysystem\AdapterInterface;
use \neon\firefly\services\driveManager\models\DbFile;
use \League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use \neon\firefly\services\driveManager\interfaces\IDriverConfig;

/**
 * A Flysystem adapter for storing files in a database!
 * Class Db Adapter interface
 * @package neon\firefly\finder\drivers
 */
class Db extends Component implements IDriverConfig, AdapterInterface
{
	public $visibility;

	/**
	 * Write a new file.
	 *
	 * @param string $path
	 * @param string $contents
	 * @param Config $config Config object
	 *
	 * @return array|false false on failure file meta data on success
	 */
	public function write($path, $contents, Config $config)
	{
		$file = DbFile::findByPath($path);
		// if no model found then we create a new file
		if ($file === null) {
			$file = new DbFile(['path' => $path]);
		}
		$file->type = DbFile::TYPE_FILE;
		$file->contents = $contents;
		$file->size = mb_strlen($contents);

		if ($visibility = $config->get('visibility')) {
			$file->visibility = $visibility;
		}
		$file->mime_type = Util::guessMimeType($path, $file->contents);
		// model errors may be available via $file->getErrors() if save failed
		return $file->save() ? $file->toArray() : false;
	}

	/**
	 * Write a new file using a stream.
	 *
	 * @param string $path
	 * @param resource $resource
	 * @param Config $config Config object
	 *
	 * @return array|false false on failure file meta data on success
	 */
	public function writeStream($path, $resource, Config $config)
	{
		return $this->write($path, stream_get_contents($resource), $config);
	}

	/**
	 * Update a file.
	 *
	 * @param string $path
	 * @param string $contents
	 * @param Config $config Config object
	 *
	 * @return array|false false on failure file meta data on success
	 */
	public function update($path, $contents, Config $config)
	{
		return $this->write($path, $contents, $config);
	}

	/**
	 * Update a file using a stream.
	 *
	 * @param string $path
	 * @param resource $resource
	 * @param Config $config Config object
	 *
	 * @return array|false false on failure file meta data on success
	 */
	public function updateStream($path, $resource, Config $config)
	{
		return $this->writeStream($path, $resource, $config);
	}

	/**
	 * Rename a file.
	 *
	 * @param string $path
	 * @param string $newPath
	 *
	 * @return bool
	 */
	public function rename($path, $newPath)
	{
		$file = DbFile::findByPath($path);
		unset(DbFile::$fileCache[$path]);
		if (!$file) {
			return false;
		}
		$newFile = DbFile::findByPath($newPath);
		// the $newFile should not exist!
		if ($newFile !== null) {
			// the file already exists!
			return false;
		}
		$file->path = $newPath;
		return $file->save();
	}

	/**
	 * Copy a file.
	 *
	 * @param string $path
	 * @param string $newPath
	 * @throws FileNotFoundException if the $path does not exist
	 * @throws FileExistsException if the $newpath exists
	 * @return bool
	 */
	public function copy($path, $newPath)
	{
		$this->_checkFileSizeLimit($path);
		$file = DbFile::findByPath($path);
		// the file to copy does not exist!
		if ($file === null) {
			// could throw a FileDoesNotExistsException
			throw new FileNotFoundException("The file '$path' does not exist");
		}
		// make sure the newPath does not already exist!

		$newFile = DbFile::findByPath($newPath);
		if ($newFile) {
			// The newpath already exists!
			throw new FileExistsException("The file '$newPath' already exists");
		}
		$newFile = clone $file;
		$newFile->path = $newPath;
		return $newFile->save();
	}

	/**
	 * Delete a file.
	 *
	 * @param string $path
	 * @return bool
	 */
	public function delete($path)
	{
		$file = DbFile::findByPath($path);
		if ($file) {
			return $file->delete();
		}
		return false;
	}

	/**
	 * Delete a directory.
	 *
	 * @param string $dirname
	 *
	 * @return bool
	 */
	public function deleteDir($dirname)
	{
		$files = DbFile::find()
			->select(['path'])
			->where(['like', 'path', $dirname])
			->all();
		$success = true;
		foreach ($files as $file) {
			if ( ! $this->delete($file['path'])) {
				$success = false;
			}
		}
		return $success;
	}

	/**
	 * Create a directory.
	 *
	 * @param  string  $dirname directory name
	 * @param  Config  $config
	 * @throws FileExistsException
	 * @return array|false
	 */
	public function createDir($dirname, Config $config)
	{
		$file = DbFile::findOne($dirname);
		if ($file === null) {
			$file = new DbFile(['path' => $dirname]);
		}
		else if ($file->type == DbFile::TYPE_FILE) {
			throw new FileExistsException('Can not create a directory as a file already exists.');
		}
		if ($visibility = $config->get('visibility')) {
			$file->visibility = $visibility;
		}
		$file->type = DbFile::TYPE_DIR;
		return $file->save();
	}

	/**
	 * Set the visibility for a file.
	 *
	 * @param string $path
	 * @param string $visibility
	 *
	 * @return array|false file meta data
	 */
	public function setVisibility($path, $visibility)
	{
		$file = DbFile::findByPath($path);
		if (!$file) {
			return false;
		}
		$file->visibility = $visibility;
		return $file->save() ? $file->toArray() : false;
	}

	/**
	 * Check whether a file exists.
	 *
	 * @param string $path
	 *
	 * @return array|bool|null
	 */
	public function has($path)
	{
		return DbFile::findByPath($path) ? true : false;
	}

	/**
	 * Read a file.
	 *
	 * @param string $path
	 *
	 * @return array|false
	 */
	public function read($path)
	{
		$this->_checkFileSizeLimit($path);
		$file = DbFile::findByPath($path, true);
		return $file ? $file->toArray() : false;
	}

	/**
	 * Read a file as a stream.
	 *
	 * @param string $path
	 *
	 * @return array|false
	 */
	public function readStream($path)
	{
		$this->_checkFileSizeLimit($path, 'This file exceeds the PHP memory limit. The database driver only emulates streams.');
		$stream = fopen('php://temp', 'w+');
		$result = $this->read($path);
		if (!$result) {
			fclose($stream);
			return false;
		}
		fwrite($stream, $result['contents']);
		rewind($stream);
		return compact('stream');
	}

	/**
	 * List contents of a directory.
	 *
	 * @param string $directory
	 * @param bool $recursive
	 *
	 * @return array
	 */
	public function listContents($directory = '', $recursive = false)
	{
		$query = DbFile::find()
			->select('path, size, type, mime_type, updated_at, visibility');
		if ((bool) strlen($directory)) {
			$directory = $this->sanitizeDirectoryName($directory);
			$query->where(['like', 'path', $directory]);
		}
		$result = $query->asArray()->limit(1000)->all();
		$result = array_map(function($r){
			return DbFile::convertToFileSystemFormat($r);
		}, $result);
		return Util::emulateDirectories($result);
	}

	/**
	 * sanitizes location path (adds trailing slash and adds backslashes before mysql wildcards)
	 *
	 * @param string $path
	 * @return string
	 */
	protected function sanitizeDirectoryName($path)
	{
		$path = rtrim($path, '/');
		$sanitizedPath = addcslashes($path, '_%');
		return $sanitizedPath . '/';
	}

	/**
	 * Get all the meta data of a file or directory.
	 *
	 * @param string $path
	 *
	 * @return array|false
	 */
	public function getMetadata($path)
	{
		$file = DbFile::findByPath($path);
		return $file ? $file->toArray() : false;
	}

	/**
	 * Get all the meta data of a file or directory.
	 *
	 * @param string $path
	 *
	 * @return array|false
	 */
	public function getSize($path)
	{
		$file = DbFile::findByPath($path);
		return $file ? $file->toArray() : false;
	}

	/**
	 * Get the mimetype of a file.
	 * Note the name is getMimetype - due to the Adapter Interface
	 * in the rest of the app this is exposed by getMimeType - camel cased.
	 *
	 * @param string $path
	 *
	 * @return array|false
	 */
	public function getMimetype($path)
	{
		$file = DbFile::findByPath($path);
		return $file ? $file->toArray() : false;
	}

	/**
	 * Get the timestamp of a file.
	 *
	 * @param string $path
	 *
	 * @return array|false
	 */
	public function getTimestamp($path)
	{
		$file = DbFile::findByPath($path);
		return $file ? $file->toArray() : false;
	}

	/**
	 * Get the visibility of a file.
	 *
	 * @param string $path
	 *
	 * @return array|false
	 */
	public function getVisibility($path)
	{
		$file = DbFile::findByPath($path);
		return $file ? $file->toArray() : false;
	}

	/**
	 * @return $this
	 */
	public function getAdapter()
	{
		return $this;
	}

	/**
	 * Checks the file does not exceed PHP memory limit - and throws an exception.
	 *
	 * @param  string  $path
	 * @throws \neon\firefly\services\fileManager\FileExceedsMemoryLimitException
	 */
	protected function _checkFileSizeLimit($path, $message='')
	{
		if (function_exists('ini_get')) {
			$bytes = to_bytes(ini_get('memory_limit'));
			$size = $this->getSize($path);
			if ($size['size'] >= $bytes) {
				throw new FileExceedsMemoryLimitException($message);
			}
		}
	}
}