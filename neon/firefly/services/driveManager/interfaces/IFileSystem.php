<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 22/11/2016 15:18
 * @package neon/firefly
 */

namespace neon\firefly\services\driveManager\interfaces;

use \League\Flysystem\FileExistsException;
use \League\Flysystem\FileNotFoundException;

/**
 * This is mainly a port of the Flysystem interface - with a few extras - unfortunately because we tweak
 * some of the functions we can not reuse or extend the original FilesystemInterface
 * Interface FileSystem
 * @package neon\firefly\finder\interfaces
 */
interface IFileSystem
{
	/**
	 * The public visibility setting.
	 * @var string
	 */
	const VISIBILITY_PUBLIC = 'public';

	/**
	 * The private visibility setting.
	 * @var string
	 */
	const VISIBILITY_PRIVATE = 'private';

	/**
	 * Gets the configured drive name
	 * @return string
	 */
	public function getName();

	/**
	 * Read a file.
	 *
	 * @param string $path The path to the file.
	 * @throws FileNotFoundException
	 * @return string|false The file contents or false on failure.
	 */
	public function read($path);

	/**
	 * Read a file ad a resource stream
	 *
	 * @param string $path The path to the file.
	 * @throws FileNotFoundException
	 * @return resource|false The file contents or false on failure.
	 */
	public function readStream($path);

	/**
	 * List contents of a directory.
	 *
	 * @param string $directory The directory to list.
	 * @param bool   $recursive Whether to list recursively.
	 * @return array A list of file metadata including files and directories
	 */
	public function listContents($directory = '', $recursive = false);

	/**
	 * List all files in a directory.
	 *
	 * @param string $directory The directory to list.
	 * @param bool   $recursive Whether to list recursively.
	 * @return array A list of file metadata.
	 */
	public function listFiles($directory = '', $recursive = false);

	/**
	 * List all directories within a directory.
	 *
	 * @param  string|null  $directory
	 * @param  bool  $recursive
	 * @return array
	 */
	public function listDirectories($directory = null, $recursive = false);

	/**
	 * Get the file size of a given file.
	 *
	 * @param  string  $path
	 * @return int
	 */
	public function getSize($path);

	/**
	 * Get a file's mime-type.
	 *
	 * @param string $path The path to the file.
	 * @throws FileNotFoundException
	 * @return string|false The file mime-type or false on failure.
	 */
	public function getMimeType($path);

	/**
	 * Get the file's last modification time.
	 *
	 * @param  string  $path
	 * @throws FileNotFoundException
	 * @return string|false The timestamp or false on failure.
	 */
	public function getLastModified($path);

	/**
	 * Get a file's visibility.
	 *
	 * @param string $path The path to the file.
	 * @throws FileNotFoundException
	 * @return string|false The visibility (public|private) or false on failure.
	 */
	public function getVisibility($path);

	/**
	 * Set the visibility for a file.
	 *
	 * @param string $path       The path to the file.
	 * @param string $visibility One of 'public' or 'private'.
	 * @return bool True on success, false on failure.
	 */
	public function setVisibility($path, $visibility);

	/**
	 * Rename a file.
	 *
	 * @param string $from  Path to the existing file.
	 * @param string $to  The new path of the file.
	 * @throws FileExistsException   Thrown if $newPath exists.
	 * @throws FileNotFoundException Thrown if $path does not exist.
	 * @return bool True on success, false on failure.
	 */
	public function move($from, $to);

	/**
	 * Copy a file to a new location.
	 *
	 * @param  string  $from
	 * @param  string  $to
	 * @return bool
	 */
	public function copy($from, $to);

	/**
	 * Delete a file.
	 *
	 * @param string $path
	 * @throws FileNotFoundException
	 * @return bool True on success, false on failure.
	 */
	public function delete($path);

	/**
	 * Create a file or update if exists.
	 *
	 * @param string $path     The path to the file.
	 * @param string|object|resource $contents  The file contents.
	 * @param string  $visibility  One of 'public' or 'private'
	 * @throws \InvalidArgumentException if the $contents is an object and it does not have a getRealPath function
	 * @return bool  True on success, false on failure.
	 */
	public function put($path, $contents, $visibility=null);

	/**
	 * Get meta data for this file
	 *
	 * @param  string  $path
	 * @return mixed
	 */
	public function getMeta($path);

	/**
	 * Determine if a file exists.
	 *
	 * @param  string  $path
	 * @return bool
	 */
	public function exists($path);

	/**
	 * Prepend to a file.
	 *
	 * @param  string  $path
	 * @param  string  $data
	 * @param  string  $separator
	 * @return bool
	 */
	public function prepend($path, $data, $separator = PHP_EOL);

	/**
	 * Append to a file.
	 *
	 * @param  string  $path
	 * @param  string  $data
	 * @param  string  $separator
	 * @return bool
	 */
	public function append($path, $data, $separator = PHP_EOL);

	/**
	 * Create a directory.
	 *
	 * @param  string  $path
	 * @return bool
	 */
	public function createDirectory($path);

	/**
	 * Create a directory.
	 *
	 * @deprecated use createDirectory instead
	 * @param  string  $path
	 * @return bool
	 */
	public function makeDirectory($path);

	/**
	 * Recursively delete a directory.
	 *
	 * @param  string  $directory
	 * @return bool
	 */
	public function deleteDirectory($directory);

	/**
	 * Create a hash checksum from a files content.
	 *
	 * @param  string  $path  - The path to the file in the context of the adapter
	 * @param  string  $algorithm  - Any algorithm supported by hash()
	 * @return string  return the hash output string on success or an empty string if the file path can not be read as a stream
	 */
	public function hash($path, $algorithm='md5');

	/**
	 * Get the underlying Flysystem driver
	 * @return \League\Flysystem\FilesystemInterface
	 */
	public function getDriver();
}
