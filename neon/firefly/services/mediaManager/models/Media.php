<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 23/11/2016 23:44
 * @package neon
 */

namespace neon\firefly\services\mediaManager\models;

use \neon\core\db\ActiveRecord;
use neon\core\helpers\Str;
use neon\firefly\services\fileManager\models\FileManager;
use neon\firefly\services\mediaManager\DirectoryExistsException;

/**
 * This is the model class for table "firefly_media".
 *
 * @property  string  $id
 * @property  string  $parent_id
 * @property  string  $path
 * @property  string  $type
 */
class Media extends ActiveRecord
{
	/**
	 * Turn on soft delete
	 * @var bool
	 */
	public static $softDelete = true;

	/**
	 * Auto generate ids when saving
	 * @var bool
	 */
	public static $idIsUuid64 = true;

	const TYPE_DIR = 'dir';
	const TYPE_FILE = 'file';

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%firefly_media}}';
	}

	/**
	 * Find media directory object by its path
	 *
	 * @param string $path
	 * @param boolean $withDeleted
	 * @return Media|null|\yii\db\ActiveRecord
	 */
	public static function findDirectoryByPath($path, $withDeleted=false)
	{
		$path = static::getPath($path);
		return static::query($withDeleted)
			->where(['path' => $path])
			->andWhere(['or', ['type' => 'dir'], ['type' => 'root']])
			->one();
	}

	/**
	 * Finds a file by its path
	 *
	 * @param string $path
	 * @return array|null|\yii\db\ActiveRecord
	 */
	public static function findFileByPath($path)
	{
		$path = static::getPath($path);
		return static::find()
			->where(['path' => $path])
			->andWhere(['type' => 'file'])
			->one();
	}

	/**
	 * Will return the parent of the given path for example
	 * a path of '/root/parent/thing' will return the '/root/parent' directory node
	 * or null if it does not exist
	 *
	 * @param string $path
	 * @param string $parentPath - the parent path used to find the parent node
	 * @return Media|null|\yii\db\ActiveRecord
	 */
	public static function findParentByPath($path, &$parentPath='')
	{
		$info = pathinfo($path);
		$parentPath = $info['dirname'];
		return Media::findDirectoryByPath($parentPath);
	}

	/**
	 * Search for files and directories using a text string
	 * @return []
	 */
	public static function search(string $q)
	{
		$mediaFields = ['id', 'parent_id', 'path', 'type', 'deleted'];
		$fileFields = ['uuid','name','file_hash','drive','mime_type','size','downloaded','viewed','updated_by','updated_at', 'created_by', 'created_at', 'meta', 'alt', 'caption'];
		// Append media fields with aliases to the SQL
		$mediaSelect = '';
		foreach ($mediaFields as $field)
			$mediaSelect .= ", media.$field as media_$field";

		$sql = "SELECT f.* $mediaSelect
		FROM firefly_media as media
		LEFT JOIN firefly_file_manager as f on f.uuid = media.id
		WHERE (MATCH(f.name, f.path, f.mime_type, f.alt, f.caption)
			AGAINST(:q1 IN BOOLEAN MODE))
		-- This enables partial matchs
		OR (LOWER(f.name) LIKE :q2)
		OR (f.mime_type LIKE :q2)
		-- OR (LOWER(f.path) LIKE LOWER(:q2))
		OR (LOWER(f.alt) LIKE :q2)
		OR (LOWER(f.caption) LIKE :q2)
		OR (LOWER(media.path) LIKE :q2 AND `type` = 'dir')
		AND media.deleted = 0
		limit 100
		";

		$items = neon()->db
			->createCommand($sql, [':q1' => strtolower($q), ':q2' => strtolower("%$q%")])
			->queryAll();

		return collect($items)->map(function($item) use ($mediaFields, $fileFields) {
			$mappedItem = [];
			// Map each media field
			foreach ($mediaFields as $field)
				$mappedItem[$field] = $item["media_$field"];
			// Nest other fields from 'file_manager' in file
			$mappedItem['file'] = [];
			foreach ($fileFields as $key)
				$mappedItem['file'][$key] =  $item[$key];
			return $mappedItem;
		})->toArray();
	}

	/**
	 * Create a new directory by its path and return its array representation
	 * This function assumes the parent node exists and throws an exception if the parent
	 * can not be found.
	 *
	 * @param string $path
	 * @return array
	 * @throws \Exception if the parent does not exist or model validation fails
	 */
	public static function createDirectory($path)
	{
		$parent = static::findParentByPath($path, $parentPath);
		if ($parent === null)
			throw new \Exception("Directory does not exist with path '$parentPath'");
		$node = new static(['path' => $path, 'type' => 'dir', 'parent_id' => $parent->id]);
		$node->save();
		if ($node->hasErrors()) {
			throw new \Exception('Model validation errors: ' . print_r($node->getErrors(), true));
		}
		return $node->toArray();
	}

	/**
	 * Get an array of Media object that are children
	 *
	 * @return array|Media[]
	 */
	public function getChildren($withDeleted=false)
	{
		return $this->getChildrenQuery($withDeleted)
			->all();
	}

	/**
	 * Create a relation for use in queries (->with('file'))
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getFile()
	{
		return $this->hasOne(FileManager::class, ['uuid' => 'id']);
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['path'], 'validatePath'],
		];
	}

	/**
	 * Add validation errors if the path is wrong
	 *
	 * @param $attribute
	 * @param $params
	 */
	public function validatePath($attribute, $params)
	{
		$path = $this->$attribute;
		// must be absolute and start with /
		if ($path[0] !== '/')
			$this->addError('path', "Must be absolute path and start with a '/' provided: '$path'");
		// prevent folders with no names being created
		if (strpos($path, '//') !== false)
			$this->addError('path', "Path can not contain a double forward slash");
	}

	/**
	 * Whether this record represents a directory
	 *
	 * @return bool
	 */
	public function isDir()
	{
		return $this->type === 'dir' || $this->type === 'root';
	}

	/**
	 * Whether this record represents a file
	 *
	 * @return bool
	 */
	public function isFile()
	{
		return $this->type === 'file';
	}

	/**
	 * Get the root node
	 *
	 * @return Media
	 */
	public static function getRootNode()
	{
		return static::findDirectoryByPath('/');
	}

	/**
	 * Add a child node to this node
	 *
	 * @param Media|array $node
	 * @return boolean
	 */
	public function addChild($node)
	{
		$node->parent_id = $this->id;
		return $node->save();
	}

	/**
	 * Move this node into the specified node
	 *
	 * @param Media $intoNode - The node to move this node into
	 * @return array - id => ['success': true|false, [error] => 'error message']
	 * @throws \Exception
	 */
	public function  moveInto(Media $intoNode)
	{
		// We must ensure we are not moving the item into itself!
		if ($intoNode->id === $this->id)
			return [
				'success' => false,
				'error' => 'Can not move an item inside itself'
			];
		// update the parentlatest version
		$this->parent_id = $intoNode->id;
		$success = false;

		// updating files is nice and easy
		if ($this->isFile()) {
			$this->path = $intoNode->path;
			$success = $this->save();
			return ['success' => $success];
		}

		// update directories - and their child paths
		if ($this->isDir()) {
			//$this->updateChildPaths();
			$originalPath = $this->path;
			$newPath = str_replace('//', '/', $intoNode->path .'/'.basename($this->path));
			$this->path = $newPath;
			$save = $this->save();
			if ($save) {
				Media::replacePaths("$originalPath/", "$newPath/", "$originalPath/%");
				return ['success' => $save];
			} else {
				return ['success' => $save, 'error' => $this->getErrors()];
			}
		}
		return $success;
	}

	/**
	 * Recursively updates all children
	 * This is a very expensive operation queries within recursive calls
	 * It will perform 2 queries for each child * the number of parents.
	 */
	public function updateChildPaths()
	{
		$this->updatePath();
		foreach($this->getChildren(true) as $child)
			$child->updateChildPaths();
	}

	/**
	 * update the items path
	 */
	public function updatePath()
	{
		$this->path = $this->rebuildPath();
		$this->save();
	}

	/**
	 * Return the path string for this item
	 * @return string
	 */
	public function rebuildPath()
	{
		$path = collect($this->getAncestors())->prepend($this)
			->map(function($item){ return ($item->type === MEDIA::TYPE_DIR) ? basename($item->path) : ''; })
			->reverse()
			->implode('/');
		// allow for root node
		$path = ($path==='') ? '/' : $path;
		// prevent trailing slash on files
		return rtrim($path, '/');
	}

	/**
	 * @return array
	 */
	public function getAncestors()
	{
		$item = $this;
		$parents = [];
		do {
			$item = $item->getParent();
			if ($item) $parents[] = $item;
		} while ($item !== null);
		return $parents;
	}

	/**
	 * @return array|bool|\yii\db\ActiveRecord|null
	 */
	public function getParent()
	{
		return neon()->cacheArray->getOrSet($this->parent_id, function() {
			return Media::findWithDeleted()->where(['id' => $this->parent_id])->one();
		});
	}

	/**
	 * Replace a path with a new path across all nodes
	 *
	 * @param string $findPath - the path string to find
	 * @param string $replacePath - the path string to replace the found path with
	 * @param string $wherePath - The mysql where path
	 * @return int - Number of affected rows
	 * @throws \Exception if query fails
	 */
	public static function replacePaths($findPath, $replacePath, $wherePath)
	{
		$tableName = static::tableName();
		$sql = 'UPDATE ' . $tableName . ' SET path = REPLACE(path, :findPath, :replacePath) WHERE path LIKE :wherePath';
		return Media::getDb()->createCommand($sql)
			->bindValues([
				':findPath' => $findPath,
				':replacePath' => $replacePath,
				':wherePath' => $wherePath
			])
			->execute();
	}

	/**
	 * Get a query object configured to filter by children
	 *
	 * @return \neon\core\db\ActiveQuery
	 */
	public function getChildrenQuery($withDeleted=false)
	{
		return static::query($withDeleted)
			->where(['parent_id' => $this->id]);
	}

	/**
	 * @inheritdoc
	 * Overwrite the path with a chroot - defined root path
	 * this prevents the full path being sent to the client if a root is defined
	 */
	public function toArray(array $fields = [], array $expand = [], $recursive = true)
	{
		$array = parent::toArray($fields, $expand, $recursive);
		$array['path'] = static::formatPath($array['path']);
		return $array;
	}

	public function afterSave($insert, $changedAttributes)
	{
		neon()->cacheArray->delete($this->id);
	}

	/**
	 * Replaces root paths
	 * @param string $path
	 * @return string formatted path e.g. /a/b/c/d where root is defined as /a/b/ will give /c/d
	 */
	public static function formatPath($path)
	{
		return Str::iReplaceFirst(neon()->firefly->mediaManager->getRootPath(), '/', $path);
	}

	/**
	 * Lots of systems will ask for root access '/' as a convenient way to access the top folder of the a directory
	 * or for example when not specifying any particular folder hierarchy
	 * however we MUST always only serve the root as defined by neon()->firefly->mediaManager->getRootPath()
	 * as '/' may be mapped to a different root.
	 * @param string $path
	 * @return string
	 */
	public static function getPath($path)
	{
		if ($path === '/' && neon()->firefly->mediaManager->getRootPath() !== '/')
			$path = neon()->firefly->mediaManager->getRootPath();
		return $path;
	}

	/**
	 * @inheritdoc
	 */
	public function delete()
	{
		// need to delete associated fileManager managed file
		if ($this->type === static::TYPE_FILE) {
			neon()->firefly->delete($this->id);
		}
		return parent::delete();
	}

	/**
	 * @inheritdoc
	 */
	public function destroy()
	{
		// also delete the associated fileManager manager file
		if ($this->type === static::TYPE_FILE) {
			neon()->firefly->destroy($this->id);
		}
		return parent::destroy();
	}
}
