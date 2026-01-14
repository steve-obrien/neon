<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 28/11/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @package neon\firefly
 */

namespace neon\firefly\services\fileManager\interfaces;

use neon\core\web\Response;
use neon\core\web\UploadedFile;
use neon\firefly\services\fileManager\FileNotFoundException;

interface IFileManager
{
	/**
	 * Create a file or update if exists.
	 *
	 * @param  string|resource  $contents
	 * @param  null|string $name
	 * @param  null|array $meta - Additional json encoded meta data to store against the file. Add anything you like.
	 *   For example an array of encoded image transformations detailing the original image id and the transformations
	 *   performed to create this image - other uses could be to store simple image meta data like geo location or device or rotation etc etc.
	 * @return  null|string returns the uuid of the file or null on failure
	 */
	public function save($contents, $name = null, $meta = null);

	/**
	 * Save a file passed as a UploadedFile | \SplFileInfo | object
	 *
	 * @param UploadedFile|\SplFileInfo $file
	 * @param  null|array $meta - Additional json encoded meta data to store against the file
	 * @return string|null
	 * @throws \InvalidArgumentException if the $file param is not an object that implements getRealPath and getFilename methods
	 */
	public function saveFile($file, $meta = null);

	/**
	 * Save a file into firefly from a url
	 * Make sure the source is trusted - if its an image - then run through the imageManager to recreate the image
	 *
	 * @param string $url the url to load the file from
	 * @param string $name the file name to use
	 * @param  null|array $meta - Additional json encoded meta data to store against the file
	 */
	public function saveFromUrl($url, $name, $meta = null);

	/**
	 * Returns boolean if the file with the given uuid exists
	 *
	 * @param  string  $uuid  - The unique file id
	 * @return boolean
	 */
	public function exists($uuid);

	/**
	 * Read a file.
	 *
	 * @param  string  $uuid  - The unique file id
	 * @throws  \yii\web\FileNotFoundException
	 * @return  string|false The file contents or false on failure.
	 */
	public function read($uuid);

	/**
	 * Read the file as a stream
	 *
	 * @param  string  $uuid
	 * @return  resource|false on failure
	 */
	public function readStream($uuid);

	/**
	 * Get the file meta data of a given file.
	 *
	 * @param  string  $uuid  - The unique file id
	 * @return array like:
	 *
	 * ```php
	 * "uuid" => "F9fe5-rueduefMbPoXDaL0",
	 * "id" => "F9fe5-rueduefMbPoXDaL0",
	 * "name" => "Large archive file",
	 * "file_hash" => "b3283838041a9c8280ab62b700bccd51",
	 * "path" => "2016/12/Large archive file",
	 * "drive" => "local",
	 * "mime_type" => "application/zip",
	 * "size" => 281328512,
	 * "downloaded" => null,
	 * "viewed" => null,
	 * "updated_by" => null,
	 * "updated_at" => "2016-12-07 23:38:11",
	 * "created_by" => null,
	 * "created_at" => "2016-12-07 23:38:11",
	 * "deleted_at" => null,
	 * ```
	 *
	 * Returns an empty array if no file exists for the given uuid
	 */
	public function getMeta($uuid);

	/**
	 * Get the name of the file
	 *
	 * @param  string  $uuid  - The unique file id
	 * @return string|false if no file exists
	 */
	public function getFileName($uuid);

	/**
	 * Set the file name of a file manager file
	 *
	 * @param  string  $uuid  - The unique file id
	 * @param  string  $name  - The new file name
	 * @return boolean - true on success false on failure
	 */
	public function setFileName($uuid, $name);

	/**
	 * Get the URL for the file at the given path.
	 *
	 * @param  string  $uuid  - The unique file id
	 * @return string
	 */
	public function getUrl($uuid);

	/**
	 * Get the URL for an image at the given path.
	 *
	 * @param  string  $uuid  - The unique file id
	 * @param  array $params - various parameters that modify the
	 *   result. This includes:
	 *   'w' => width
	 *   'h' => height
	 * @return string
	 */
	public function getImage($uuid, $params=[]);

	/**
	 * Get a file's visibility.
	 *
	 * @param  string  $uuid  - The unique file id
	 * @throws FileNotFoundException
	 * @return string|false The visibility (public|private) or false on failure.
	 */
	public function getVisibility($uuid);

	/**
	 * Set the visibility for a file.
	 *
	 * @param  string  $uuid  - The unique file id
	 * @param  string $visibility One of 'public' or 'private'.
	 * @return bool True on success, false on failure.
	 */
	public function setVisibility($uuid, $visibility);

	/**
	 * Create a copy of the file and return its new uuid
	 *
	 * @param  string  $uuid  - The unique file id
	 * @return $uuid|null returns a new uuid to the copied file on success null on failure
	 */
	public function copy($uuid);

	/**
	 * Delete a file - soft deletes the file
	 *
	 * @param  string  $uuid  - The unique file id
	 * @throws \yii\web\FileNotFoundException
	 * @return int number of items deleted
	 */
	public function delete($uuid);

	/**
	 * Destroy the file
	 *
	 * @param  string  $uuid  - The unique file id
	 * @return  int db rows deleted
	 */
	public function destroy($uuid);

	/**
	 * Restore a previously deleted file
	 *
	 * @param  string  $uuid  - The unique file id
	 * @return boolean  true on success, false on failure
	 */
	public function restore($uuid);

	/**
	 * Prepend to a file.
	 *
	 * @param  string  $uuid  - The unique file id
	 * @param  string $contents
	 * @param  string $separator
	 * @return bool
	 */
	public function prepend($uuid, $contents, $separator = PHP_EOL);

	/**
	 * Append to a file.
	 *
	 * @param  string  $uuid  - The unique file id
	 * @param  string  $contents
	 * @param  string  $separator
	 * @return bool
	 */
	public function append($uuid, $contents, $separator = PHP_EOL);

	/**
	 * Get a hash checksum for the contents of the file
	 *
	 * @param  string  $uuid  - The unique file id
	 * @param  bool  $refresh - whether to force creating the has again
	 * - otherwise it will just return the value from the database
	 * @return string|false - string the md5 checksum of the file contents, false on failure
	 */
	public function getFileHash($uuid, $refresh=false);

	/**
	 * Get the file size in bytes
	 *
	 * @param  string  $uuid  - The unique file id
	 * @return integer|false  The number of bytes representing the file size or false on failure
	 */
	public function getSize($uuid);

	/**
	 * Set custom meta information on the file
	 *
	 * @param string $uuid
	 * @param array $meta - encoded as json
	 * @return bool
	 */
	public function setMeta($uuid, $meta);

	/**
	 * This function returns a response object - calling send on the object will
	 * actually action the browser send command.
	 *
	 * @param  string  $uuid  - The unique file id
	 * @param  bool  $download  If true makes the browser download the file via OS download save dialog.
	 * If false will display the file in the browser
	 * @return Response
	 */
	public function sendFile($uuid, $download=false);

	/**
	 * Find a file manager file data by its drive and path location
	 *
	 * @param string $drive the configured drive name
	 * @param string $path the file path of the drives file system
	 * @return array|null
	 */
	public function findFile($drive, $path);

	/**
	 * Get the IFileSystem driver that knows how to find this file
	 *
	 * @param string  $uuid
	 * @param \neon\firefly\services\fileManager\models\FileManager  $file
	 * @throws FileNotFoundException
	 * @return \neon\firefly\services\driveManager\interfaces\IFileSystem
	 * @inheritdoc
	 */
	public function getDriveFor($uuid, &$file);

}
