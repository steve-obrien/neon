<?php

namespace neon\firefly\services\mediaManager\interfaces;

use Intervention\Image\Exception\NotFoundException;
use neon\firefly\services\mediaManager\DirectoryExistsException;
use neon\firefly\services\mediaManager\InvalidDirectoryException;
use neon\firefly\services\mediaManager\models\Media;
use yii\web\BadRequestHttpException;

interface IMediaManager
{
	/**
	 * Create a new directory
	 *
	 * @param string $directory
	 * @return array list of associative arrays containing directory details for each directory created
	 * For example running the following in dev shell `./neo dev/shell`
	 * ```php
	 * >>> neon()->firefly->mediaManager->createDirectory('/test/testy'); // where neither /test or /test/testy exists
	 * => [
	 *       [
	 *          "path" => "/test",
	 *          "type" => "dir",
	 *          "parent_id" => "f1xYdL9Ff8q0ZvXYMYqI-g",
	 *          "id" => "OgOE03dqcwGfsafFO_SV10",
	 *       ],
	 *       [
	 *          "path" => "/test/testy",
	 *          "type" => "dir",
	 *          "parent_id" => "OgOE03dqcwGfsafFO_SV10",
	 *          "id" => "zPVXp6Xxe1S76Cdngzrkmg",
	 *       ],
	 *    ]
	 *
	 * >>> neon()->firefly->mediaManager->createDirectory('/test/testy/test'); // now /test/ and /test/testy exist
	 * => [
	 *       [
	 *          "path" => "/test/testy/test",
	 *          "type" => "dir",
	 *          "parent_id" => "zPVXp6Xxe1S76Cdngzrkmg",
	 *          "id" => "732y6x5Ffv20E78N1_p3P0",
	 *       ],
	 *    ]
	 * @throws InvalidDirectoryException
	 * @throws DirectoryExistsException
	 */
	public function createDirectory($directory);

	/**
	 * @param string $id - the item uuid
	 * @param string $newName the new name of the file or directory
	 * @throws NotFoundException if the id is not in the media manager
	 * @throws BadRequestHttpException if the $newName is not formatted correctly e.g. contains a forward slash
	 * @return boolean - true if successful
	 */
	public function rename($id, $newName);

	/**
	 * Whether a directory exists - note soft deleted directories will not be found
	 * @param string $directory
	 * @return Boolean
	 */
	public function exists($directory);

	/**
	 * List contents of a directory.
	 *
	 * @param string $directory The directory to list.
	 * @param boolean $withDeleted Defaults to false
	 * @return array A list of file metadata including files and directories or an empty array
	 */
	public function listContents($directory = '', $withDeleted = false);

	/**
	 * Add a file to the media manager
	 *
	 * @param string $id - the file managers file uuid
	 * @param string $path - the path the file should be stored in
	 * @throws \neon\firefly\services\mediaManager\InvalidDirectoryException if the $path specified can not be found
	 * @return array
	 * neon()->firefly->mediaManager->addFile('J9BcD7aGdmmdSzDjQVEDMM')
	 * => [
	 *       "id" => "J9BcD7aGdmmdSzDjQVEDMM",
	 *       "path" => "/",
	 *       "type" => "file",
	 *       "parent_id" => "f1xYdL9Ff8q0ZvXYMYqI-g",
	 *       "file" => [
	 *         "uuid" => "J9BcD7aGdmmdSzDjQVEDMM",
	 *         "name" => "argon-design-system-v1.0.1.zip",
	 *         "file_hash" => null,
	 *         "path" => "2018/08/argon-design-system-v1.0.1.J9BcD7aGdmmdSzDjQVEDMM.zip",
	 *         "drive" => "media",
	 *         "mime_type" => "application/zip",
	 *         "size" => 2931890,
	 *         "downloaded" => 0,
	 *         "viewed" => 0,
	 *         "updated_by" => 7,
	 *         "updated_at" => "2018-08-28 17:22:32",
	 *         "created_by" => 7,
	 *         "created_at" => "2018-08-28 17:22:32",
	 *         "deleted_at" => null,
	 *         "meta" => null,
	 *         "id" => "J9BcD7aGdmmdSzDjQVEDMM",
	 *        ],
	 *     ]
	 */
	public function addFile($id, $path='/');

	/**
	 * Get the information about the root node.
	 * Typically interested in the root nodes id.
	 * @return Media
	 */
	public function getRoot();

	/**
	 * Get the root path allowed to access.
	 * @return string
	 */
	public function getRootPath();

	/**
	 * Set the root path accessible. Use this to restrict the root for the
	 * duration of the call similar to chroot. Useful if you need different
	 * people or sections to see protected sections of the full media.
	 * @param string $path
	 */
	public function setRootPath($path);

	/**
	 * Get meta information about a directory by its path
	 * @param string $path
	 * @return array gives empty array if not found
	 * ```php
	 *  [
	 *     "id" => "OgOE03dqcwGfsafFO_SV10",
	 *     "parent_id" => "f1xYdL9Ff8q0ZvXYMYqI-g",
	 *     "path" => "/test",
	 *     "type" => "dir",
	 *     "deleted" => 0,
	 *  ]
	 * ```
	 */
	public function getDirectoryMeta($path);

	/**
	 * Get meta of an item by its uuid
	 *
	 * @param string $id
	 * @return array
	 * ```php
	 *  [
	 *     "id" => "OgOE03dqcwGfsafFO_SV10",
	 *     "parent_id" => "f1xYdL9Ff8q0ZvXYMYqI-g",
	 *     "path" => "/test",
	 *     "type" => "dir",
	 *     "deleted" => 0,
	 *  ]
	 * ```
	 */
	public function getMeta($id);

	/**
	 * Soft delete a media item (directory or file)
	 * @param string $id - the record uuid
	 * @return boolean whether successfully deleted
	 */
	public function delete($id);

	/**
	 * Remove the record from the media manager db
	 * @param string $id - the item uuid
	 * @return int number of rows deleted
	 */
	public function destroy($id);

	/**
	 * Empty the bin - destroys all currently deleted directories and files
	 * will also remove the associated physical file from its storage
	 * @return array of id => destroyed success / fail
	 */
	public function destroyDeleted();

	/**
	 * Move items into a directory
	 *
	 * @param string[] $items - An array of item id's to move
	 * @param string $intoId - An item id to move the items into
	 * @return boolean - true if successful
	 */
	public function move($items, $intoId);

	/**
	 * Returns the correct path.
	 * Given that the root path can be set to any directory.
	 * Will replace the root '/' with the correct mediaManager root path if set
	 * @param string $path
	 * @return string path
	 */
	public function getRealPath($path);
}
