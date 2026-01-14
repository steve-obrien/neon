<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 22/11/2016 16:06
 * @package neon\firefly
 */

namespace neon\firefly\services\driveManager;

use \neon\core\helpers\Arr;
use \neon\core\web\UploadedFile;
use League\Flysystem\AdapterInterface;
use League\Flysystem\DirectoryListing;
use League\Flysystem\FileExistsException;
use League\Flysystem\FilesystemException;
use League\Flysystem\Filesystem as LeagueFilesystem;
use neon\firefly\services\driveManager\interfaces\IFileSystem;
use League\Flysystem\FilesystemAdapter; // Updated import

/**
 * This is a wrapper adapter class for the Flysystem Filesystem
 * Class FileSystem
 * A Filesystem
 *
 * @package firefly
 */
class Drive implements IFileSystem
{
	/**
	 * @var \League\Flysystem\Filesystem
	 */
	protected $_flysystem;

	/**
	 * cache the original drive name
	 * @var string
	 */
	protected $_drive;

	/**
	 * Return the drive name - that this filesystem is an implementation of
	 * @return string
	 */
	public function getName()
	{
		return $this->_drive;
	}

	/**
	 * FileSystem constructor.
	 * @param  FilesystemAdapter  $driver
	 * @param  array  $config
	 * @param  string  $drive the drive name in the app configuration for e.g. "local", "s3", "db"
	 */
	public function __construct(FilesystemAdapter $driver, $config = [], $drive)
	{
		$this->_drive = $drive;
		$config = Arr::only($config, ['visibility', 'disable_asserts']);
		$this->_flysystem = new LeagueFilesystem($driver, count($config) > 0 ? $config : null);
	}

	/**
	 * Get the Flysystem.
	 *
	 * @return \League\Flysystem\Filesystem
	 */
	public function getDriver(): \League\Flysystem\Filesystem
	{
		return $this->_flysystem;
	}

	/**
	 * Determine if a file exists.
	 *
	 * @param  string  $path
	 * @return bool
	 */
	public function exists($path)
	{
		return $this->_flysystem->has($path);
	}

	/**
	 * Get meta data for the file
	 *
	 * @param  string  $path
	 * @return array
	 */
	public function getMeta($path)
	{
		try {
			return [
				'mimeType' => $this->_flysystem->mimeType($path),
				'lastModified' => $this->_flysystem->lastModified($path),
				'size' => $this->_flysystem->fileSize($path),
			];
		} catch (FilesystemException $e) {
			// Handle the exception as needed
			return [];
		}
	}

	/**
	 * Get the contents of a file.
	 *
	 * @param  string  $path
	 * @throws FilesystemException
	 * @return string
	 */
	public function read($path)
	{
		return $this->_flysystem->read($path);
	}

	/**
	 * @inheritdoc
	 */
	public function readStream($path)
	{
		return $this->_flysystem->readStream($path);
	}

	/**
	 * @inheritdoc
	 */
	public function put($path, $contents, $visibility = null)
	{
		// unfortunately we can not test against an interface as the base PHP file object does not have one
		if (is_object($contents)) {
			return $this->putFile($path, $contents, $visibility);
		}

		// visibility can be overwritten directly otherwise it will use the visibility (public / private) as defined
		// in the config for the driver currently being used.
		$config = [];
		if ($visibility !== null) {
			$config = ['visibility' => $visibility];
		}

		if (is_resource($contents)) {
			$this->_flysystem->writeStream($path, $contents, $config);
		}

		if (is_string($contents)) {
			$this->_flysystem->write($path, $contents, $config);
		}

		return true;
	}

	/**
	 * Store the uploaded file on the drive.
	 *
	 * @param  string  $path
	 * @param  Object|\SplFileObject|UploadedFile  $file
	 * @param  string  $visibility 'public' | 'private'
	 * @throws \InvalidArgumentException if the $file object does not have a getRealPath function
	 * @return bool True on success
	 */
	public function putFile($path, $file, $visibility = null)
	{
		neon()->firefly->isFileObjectValid($file);

		$stream = fopen($file->getRealPath(), 'r+');

		$result = $this->put($path = trim($path), $stream, $visibility);

		if (is_resource($stream)) fclose($stream);

		return $result ? (bool) $path : false;
	}

	/**
	 * Get a file's visibility.
	 *
	 * @param string $path The path to the file.
	 * @throws FilesystemException
	 * @return string|false The visibility (public|private) or false on failure.
	 */
	public function getVisibility($path)
	{
		return $this->_flysystem->visibility($path);
	}

	/**
	 * Set the visibility for a file.
	 *
	 * @param string $path       The path to the file.
	 * @param string $visibility  One of 'public' or 'private'.
	 * @throws FilesystemException
	 *
	 * @return bool True on success, false on failure.
	 */
	public function setVisibility($path, $visibility)
	{
		if (!$this->exists($path)) {
			throw new FilesystemException("The file '$path' was not found'");
		}
		return $this->_flysystem->setVisibility($path, $visibility);
	}

	/**
	 * @inheritdoc
	 */
	public function prepend($path, $data, $separator = PHP_EOL)
	{
		if ($this->exists($path)) {
			return $this->put($path, $data.$separator.$this->read($path));
		}

		return $this->put($path, $data);
	}

	/**
	 * Append to a file.
	 *
	 * @param  string  $path
	 * @param  string  $data
	 * @param  string  $separator
	 * @return bool
	 */
	public function append($path, $data, $separator = PHP_EOL)
	{
		if ($this->exists($path)) {
			return $this->put($path, $this->read($path).$separator.$data);
		}

		return $this->put($path, $data);
	}

	/**
	 * Delete the file at a given path.
	 *
	 * @param  string|array  $paths
	 * @return bool
	 */
	public function delete($paths)
	{
		$paths = is_array($paths) ? $paths : func_get_args();

		$success = true;

		foreach ($paths as $path) {
			try {
				if (! $this->_flysystem->delete($path)) {
					$success = false;
				}
			} catch (FilesystemException $e) {
				$success = false;
			}
		}
		// $this->syncFileManagerDelete($from, $to);
		return $success;
	}

	/**
	 * Copy a file to a new location.
	 *
	 * @param  string  $from
	 * @param  string  $to
	 * @return bool
	 */
	public function copy($from, $to)
	{
		return $this->_flysystem->copy($from, $to);
	}

	/**
	 * Rename a file.
	 *
	 * @param string $from    Path to the existing file.
	 * @param string $to      The new path of the file.
	 * @throws FileExistsException   Thrown if $to exists.
	 * @throws FileNotFoundException Thrown if $from does not exist.
	 * @return bool True on success, false on failure.
	 */
	public function move($from, $to)
	{
		// sync with filemanager ?
		return $this->_flysystem->move($from, $to);
	}

	/**
	 * Get the file's last modification time.
	 *
	 * @param  string  $path
	 * @return int
	 */
	public function getLastModified($path)
	{
		return $this->_flysystem->lastModified($path);
	}

	/**
	 * Get a file's size.
	 *
	 * @param string $path The path to the file.
	 * @return int|false The file size or false on failure.
	 */
	public function getSize($path)
	{
		return $this->_flysystem->fileSize($path);
	}

	/**
	 * Get the URL for the file at the given path.
	 *
	 * @param  string  $path
	 * @return string
	 */
	public function getUrl($path)
	{
		// TODO: replace with file manager
	}

	/**
	 * List contents of a directory.
	 *
	 * @param string $directory The directory to list.
	 * @param bool   $recursive Whether to list recursively.
	 * @return array A list of file metadata.
	 */
	public function listContents($directory = '', $recursive = false): DirectoryListing
	{
		return $this->_flysystem->listContents($directory, $recursive);
	}

	/**
	 * List all files in a directory.
	 *
	 * @param  string|null  $directory
	 * @param  bool  $recursive
	 * @return array
	 */
	public function listFiles($directory = null, $recursive = false)
	{
		$contents = $this->_flysystem->listContents($directory, $recursive);

		return $this->_filterContentsByType($contents, 'file');
	}

	/**
	 * List all of the directories within a directory.
	 *
	 * @param  string|null  $directory
	 * @param  bool  $recursive
	 * @return array
	 */
	public function listDirectories($directory = null, $recursive = false)
	{
		$contents = $this->_flysystem->listContents($directory, $recursive);

		return $this->_filterContentsByType($contents, 'dir');
	}

	/**
	 * Create a directory.
	 *
	 * @param  string  $path
	 * @return bool
	 */
	public function createDirectory($path)
	{
		return $this->_flysystem->createDirectory($path);
	}

	/**
	 * Create a directory.
	 *
	 * @param  string  $path
	 * @return bool
	 */
	public function makeDirectory($path)
	{
		//return $this->createDirectory($path);
	}

	/**
	 * Recursively delete a directory.
	 *
	 * @param  string  $directory
	 * @return bool
	 */
	public function deleteDirectory($directory)
	{
		return $this->_flysystem->deleteDirectory($directory);
	}

	/**
	 * Filter directory contents by type.
	 *
	 * @param  array  $contents
	 * @param  string  $type
	 * @return array
	 */
	protected function _filterContentsByType($contents, $type)
	{
		return \neon\core\helpers\Collection::make($contents)
			->where('type', $type)
			->pluck('path')
			->values()
			->all();
	}

	/**
	 * Get a file's mime-type.
	 *
	 * @param string $path  The path to the file.
	 * @throws FileNotFoundException
	 * @return string|false The file mime-type or false on failure.
	 */
	public function getMimeType($path)
	{
		return $this->_flysystem->mimeType($path);
	}

	/**
	 * Create a hash checksum from a files content.
	 *
	 * @param  string  $path  - The path to the file in the context of the adapter
	 * @param  string  $algorithm  - Any algorithm supported by hash()
	 * @return string  return the hash output string on success or an empty string if the file path can not be read as a stream
	 */
	public function hash($path, $algorithm='md5')
	{
		if (!in_array($algorithm, hash_algos())) {
			throw new \InvalidArgumentException('Hash algorithm ' . $algorithm . ' is not supported');
		}
		$stream = $this->_flysystem->readStream($path);
		if ($stream === false) {
			return '';
		}
		$context = hash_init($algorithm);
		hash_update_stream($context, $stream);
		return hash_final($context);
	}

	/**
	 * Pass dynamic method calls onto Flysystem.
	 *
	 * @param  string  $method
	 * @param  array  $parameters
	 * @throws \BadMethodCallException
	 * @return mixed
	 */
	public function __call($method, array $parameters)
	{
		return call_user_func_array([$this->_flysystem, $method], $parameters);
	}
}
