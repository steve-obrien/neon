<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 23/11/2016 23:44
 * @package neon/firefly
 */

namespace neon\firefly\services\driveManager\models;

use League\Flysystem\Util;
use \neon\core\db\ActiveRecord;

/**
 * Class DbFile
 * @package neon\firefly
 *
 * This model effectively attempts to mirror the expected output of the Flysystem
 * https://flysystem.thephpleague.com/creating-an-adapter/
 *
 * @property string  $path        The path specified also used as the primary key
 * @property mixed   $contents    The file contents
 * @property string  $visibility  Enum of 'public' | 'private'
 * @property string  $updated_at  Timestamp last modified time in mysql Datetime format
 * @property string  $mime_type   The file mime type
 * @property int     $size        The size of the file contents
 * @property string  $type        Enum of 'file' | 'dir'
 */
class DbFile extends ActiveRecord
{
	const TYPE_FILE = 'file';
	const TYPE_DIR = 'dir';
	const VISIBILITY_PUBLIC = 'public';
	const VISIBILITY_PRIVATE = 'private';

	public static $timestamps = true;

	/**
	 * @return string table name
	 */
	public static function tableName()
	{
		return '{{%firefly_files}}';
	}

	/**
	 * A per session file cache indexed by `path`
	 * note none of these file objects will contain the `contents`
	 * @var array
	 */
	static $fileCache = [];

	/**
	 * Find a file row by path
	 *
	 * @param string $path
	 * @param bool $includeContents whether to include the `contents` column - this could be large!
	 * @return DbFile|null
	 */
	public static function findByPath($path, $includeContents = false)
	{
		// if we have already loaded the file - and we do not require the contents
		// then load from the cache
		if (!$includeContents && isset(self::$fileCache[$path])) {
			return self::$fileCache[$path];
		}
		$select = ['path', 'updated_at', 'mime_type', 'size', 'type', 'visibility'];
		if ($includeContents) {
			$select[] = 'contents';
		}
		$file = DbFile::find()
			->select($select)
			->where(['path' => $path])
			->one();
		// only cache files that do not have `contents`
		// - otherwise we could eat up all the memory with a few large files
		if (!$includeContents) {
			self::$fileCache[$path] = $file;
		}
		return $file;
	}

	/**
	 * @inheritdoc
	 */
	public function delete()
	{
		$deleted = parent::delete();
		if ($deleted !== false) {
			unset(self::$fileCache[$this->path]);
		}
		return $deleted;
	}

	/**
	 * Set the visibility property
	 * @param string|boolean $value can be a string of 'public' | 'private'
	 */
	public function __set_visibility($value)
	{
		if (is_string($value)) {
			if ($value != self::VISIBILITY_PRIVATE && $value != self::VISIBILITY_PUBLIC) {
				throw new \InvalidArgumentException('The visibility must be set to either "' . self::VISIBILITY_PRIVATE . '" or "' . self::VISIBILITY_PUBLIC . '"');
			}
			$this->setAttribute('visibility', $value);
		} // If the value is a boolean then a value of true means public a value of false means private
		else if (is_bool($value)) {
			$this->setAttribute('visibility', $value ? self::VISIBILITY_PUBLIC : self::VISIBILITY_PRIVATE);
		} // We have no idea what you passed in :-S
		else {
			throw new \InvalidArgumentException('Incorrect value being set on visibility, expecting either a boolean or a string, you gave ' . print_r($value, true));
		}
	}

	/**
	 * Convert the mysql Datetime into a timestamp
	 * @param $updatedAt
	 * @return int;
	 */
	public static function convertToTimestamp($updatedAt)
	{
		// the timestamp may not yet be set by the database
		// so return a current timestamp
		$dateTime = empty($updatedAt)
			? (new \DateTime('now'))
			: (new \DateTime($updatedAt, new \DateTimeZone("Europe/London")));
		return $dateTime->getTimestamp();
	}

	/**
	 * Convert a row into a format the file system understand.
	 * @param array|DbFile $row
	 * @return array
	 */
	public static function convertToFileSystemFormat($row)
	{
		return [
			'path' => $row['path'],
			'mimetype' => $row['mime_type'],
			'dirname' => Util::dirname($row['path']),
			'timestamp' => self::convertToTimestamp($row['updated_at']),
			'visibility' => $row['visibility'],
			'size' => $row['size'],
			'type' => $row['type'],
			'contents' => isset($row['contents']) ? $row['contents'] : ''
		];
	}

	/**
	 * Enable $this->timestamp
	 * @return int timestamp
	 */
	public function getTimestamp()
	{
		return self::convertToTimestamp($this['updated_at']);
	}

	/**
	 * @inheritdoc
	 * @see self::convertToFileSystemFormat
	 * Return the file in an array expected by the file system
	 * https://flysystem.thephpleague.com/creating-an-adapter/
	 */
	public function toArray(array $fields = [], array $expand = [], $recursive = true)
	{
		return self::convertToFileSystemFormat($this);
	}
}