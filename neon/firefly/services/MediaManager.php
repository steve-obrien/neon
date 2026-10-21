<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 26/11/2016 12:24
 * @package neon
 */

namespace neon\firefly\services;

use neon\core\helpers\Arr;
use neon\core\helpers\Str;
use neon\firefly\services\mediaManager\models\Media;
use neon\firefly\services\mediaManager\interfaces\IMediaManager;
use neon\firefly\services\mediaManager\DirectoryExistsException;
use neon\firefly\services\mediaManager\InvalidDirectoryException;
use \neon\firefly\services\fileManager\models\FileManager;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\base\Component;
use yii\db\Command;

/**
 * The file manager intends to abstract things like paths and drives from the application
 * instead giving the application a uuid that refers to a file.  Meanwhile this file can be moved,
 * and changed about without breaking.
 * Class FileManager
 * @package neon\firefly
 */
class MediaManager extends Component implements IMediaManager
{
	/**
	 * @inheritDoc
	 */
	public function createDirectory($directory)
	{
		$this->validateAbsoluteDirectoryPath($directory);
		$directory = rtrim($directory, '/');
		$directory = $this->getRealPath($directory);

		$paths = $this->getAllDirectoriesInPath($directory);
		array_map(function($path) use (&$created) {
			$node = Media::findDirectoryByPath($path);
			if ($node === null) {
				$created[] = Media::createDirectory($path);
			}
		}, $paths);

		if (empty($created)) {
			if ($directory == '')
				throw new DirectoryExistsException("The root directory already exists");
			throw new DirectoryExistsException("The directory '$directory' already exists");
		}
		return $created;
	}

	/**
	 * Expands all directories in a path
	 * For example:
	 *
	 * ```php
	 * // A $path given of '/my/wonderful/path'
	 * // would return:
	 * [
	 *      '/my',
	 *      '/my/wonderful',
	 *      '/my/wonderful/path',
	 * ]
	 * ```
	 * @param string $path
	 * @return array
	 */
	public function getAllDirectoriesInPath($path)
	{
		$bits = explode('/', $path);
		$bitsCurrent = [];
		$parts = [];
		foreach($bits as $i => $bit) {
			$bitsCurrent[] = $bit;
			if ($i == 0) continue;
			$parts[] = implode('/', $bitsCurrent);
		}
		return $parts;
	}

	/**
	 * Will throw InvalidArgumentException if the directory path is not correct
	 * @param string $directory
	 * @throws InvalidDirectoryException
	 */
	public function validateAbsoluteDirectoryPath($directory)
	{
		if (!is_string($directory))
			throw new InvalidDirectoryException("Invalid directory parameter '$directory'. The directory must be absolute string path and start with '/'.");
		// must be absolute and start with /
		if ($directory[0] !== '/')
			throw new InvalidDirectoryException("Invalid directory parameter '$directory'. The directory must be absolute path and start with '/'.");
		// prevent folders with no names being created
		if (strpos($directory, '//') !== false)
			throw new InvalidDirectoryException("Invalid directory parameter '$directory'. You can not have empty path sections containing '//' each directory needs a name.");
	}

	/**
	 * @inheritDoc
	 */
	public function exists($directory)
	{
		$item = Media::findDirectoryByPath($directory);
		return empty($item) ? false : true;
	}

	/**
	 * This method is private as we do not want to expose filters - for example we may change how soft deleted records
	 * are managed and exposing filters would end up with a trash bin feature passing lots of ['deleted' => 1] filters
	 * @param string $directory - the directory to list
	 * @param bool $withDeleted - whether to include deleted items
	 * @param array $filters - array of additional search filters passed to Query where clause
	 * @return array|\yii\db\ActiveRecord[]
	 */
	private function _listQuery($directory = '', $withDeleted=false, $filters=[])
	{
		$directory = $this->getRealPath($directory);
		$item = Media::findDirectoryByPath($directory, $withDeleted);

		if (empty($item)) return [];

		$m = Media::tableName();
		$f = FileManager::tableName();
		$query = $item->getChildrenQuery($withDeleted)
			->select("$m.*")
			->orderBy("type ASC, $m.path, $f.name ASC")
			->leftJoin(FileManager::tableName(), "id = $f.uuid")
			->asArray();
		if (!empty($filters))
			$query->where($filters);
		$items = $query->all();

		$ids = collect($items)->pluck('id');
		$files = FileManager::find()->where(['uuid' => $ids])->asArray()->all();
		$filesIndex = collect($files)->indexBy('uuid');

		return collect($items)->map(function($item) use ($filesIndex) {
			$file = $filesIndex->get($item['id']);
			$item['concatName'] = $item['path'] .'/'. ($file ? $file['name'] : '');
			$item['file'] = $file;
			$item['path'] = Media::formatPath($item['path']);
			return $item;
		})->toArray();
	}

	/**
	 * @inheritDoc
	 */
	public function listContents($directory = '', $withDeleted=false)
	{
		return $this->_listQuery($directory, $withDeleted);
	}

	/**
	 * @inheritDoc
	 */
	public function listContentsDeleted($directory = '')
	{
		$directory = $this->getRealPath($directory);
		// get children
		$query = Media::findWithDeleted()->alias('media')
			->select('`media`.*,  concat(media.path, firefly_file_manager.name) as `concatName`')
			->joinWith('file')
			->where(['deleted' => 1])
			->orderBy('type, concatName ASC')
			->asArray();
		$items = $query->all();


		foreach($items as $key => $item) {
			$items[$key]['path'] = Media::formatPath($item['path']);
		}
		return $items;
	}

	/**
	 * @inheritDoc
	 */
	public function addFile($id, $path='/')
	{
		// Find the file
		$fileMeta = neon()->firefly->getMeta($id);

		if (FireflySecurityPolicy::safePathsOnly()) {
			$path = $this->getSafeLogicalPath($path);
		}
		try {
			$this->createDirectory($path);
		} catch (DirectoryExistsException $e) { /* This error is ok */ }

		$path = $this->getRealPath($path);
		$result = Media::findDirectoryByPath($path);

		$media = new Media([
			'id' => $id,
			'path' => $path,
			'type' => 'file'
		]);
		$result->addChild($media);
		return array_merge($media->toArray(), ['file' => $fileMeta]);
	}

	/**
	 * @inheritDoc
	 */
	public function rename($id, $name)
	{
		$node = Media::findOne($id);
		if ($node === null) {
			throw new NotFoundHttpException("No item with id '$id' could be found'");
		}

		// The name must not contain /
		if (strpos($name, '/')) {
			throw new BadRequestHttpException('A name cannot contain a "/" character');
		}

		if ($node->isDir())
			$this->_updateDirectoryName($node, $name);
		else
			$this->_updateFileName($node, $name);
		return true;
	}

	/**
	 * Rename a file node
	 *
	 * @param Media $node
	 * @param string $name
	 */
	private function _updateFileName($node, $name)
	{
		// this is within the control of the FileManager
		neon()->firefly->fileManager->setFileName($node->id, $name);
	}

	/**
	 * Rename a directory node
	 *
	 * @param Media $node
	 * @param string $name
	 * @throws DirectoryExistsException
	 */
	private function _updateDirectoryName($node, $name)
	{
		$originalPath = $node->path;

		$bits = explode('/', $node->path);
		$bits[count($bits) - 1] = $name;
		$newPath = implode('/', $bits);
		// make sure the parent exists


		// make sure it does not already exist
		if (! empty(Media::findDirectoryByPath($newPath))) {
			throw new DirectoryExistsException("The directory '$newPath' already exists");
		}
		$node->path = $newPath;
		$node->save();

		// update child paths
		Media::replacePaths($originalPath, $newPath, "$originalPath/%");
	}

	/**
	 * @inheritDoc
	 */
	public function getRoot()
	{
		return Media::getRootNode();
	}

	private $_rootPath = '/';

	/**
	 * @inheritDoc
	 */
	public function getRootPath()
	{
		return $this->_rootPath;
	}

	/**
	 * @inheritDoc
	 */
	public function setRootPath($path)
	{
		$this->_rootPath = rtrim($path, '/') . '/';
	}

	/**
	 * @inheritDoc
	 */
	public function getDirectoryMeta($path)
	{
		$dir = Media::findDirectoryByPath($path);
		if ($dir === null) return [];
		return $dir->toArray();
	}

	/**
	 * @inheritDoc
	 */
	public function getMeta($id)
	{
		$item = Media::find()->where(['id' => $id])->asArray()->one();
		if ($item === null) return [];
		if ($item['type'] === 'file') {
			$item['file'] = neon()->firefly->fileManager->getMeta($id);
		}
		return $item;
	}

	/**
	 * @inheritDoc
	 */
	public function delete($id)
	{
		$item = Media::findOne($id);
		if ($item === null)
			throw new NotFoundHttpException("No item with id '$id' could be found'");
		$result = $item->delete();
		if ($result === false) {
			return $item->getErrors();
		}
		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function destroy($id)
	{
		$item = Media::findWithDeleted()->where(['id' => $id])->one();
		if ($item === null)
			throw new NotFoundHttpException("No item with id '$id' could be found'");
		return $item->destroy();
	}

	/**
	 * This should be a job that can batch deleting records
	 * Empty the bin
	 * @return array of id => destroyed success / fail
	 */
	public function destroyDeleted()
	{
		/** @var Media[] $items */
		$items = Media::findWithDeleted()
			->select(['id', 'type'])
			->where(['deleted' => 1])
			->all();
		$destroyed = [];
		foreach($items as $item) {
			$destroyed[$item->id] = $item->destroy();
		}
		return $destroyed;
	}

	/**
	 * @inheritDoc
	 */
	public function move($items, $intoId)
	{
		// get the "into" folder
		$into = Media::findOne($intoId);

		// throw error if it doesn't exist
		if ($into === null)
			throw new NotFoundHttpException("No item with id '$intoId' could be found'");
		// throw error if its not a directory
		if (!$into->isDir())
			throw new BadRequestHttpException("This is not a directory - you can only move items into directories");

		// find all items to move
		$items = Media::findAll(['id' => $items]);
		// update each item
		$success = [];
		foreach($items as $item) {
			$success[$item->id] = $item->moveInto($into);
		}
		return $success;
	}

	/**
	 * Lots of systems will ask for root access '/' as a convenient way to access the top folder of the a directory
	 * or for example when not specifying any particular folder hierarchy
	 * however we MUST always only serve the root as defined by neon()->firefly->mediaManager->getRootPath()
	 * as '/' may be mapped to a different root.
	 * @param string $path
	 * @return string
	 */
	public function getRealPath($path)
	{
		$path = $this->getRootPath() . ltrim($path, '/');
		return strlen($path) === 1 ? '/' : rtrim($path, '/');
	}

	/**
	 * attempt to get a safe path or fail!
	 * @param string $path
	 * @return string
	 */
	public function getSafeLogicalPath(string $path)
	{
		$path = str_replace('\\', '/', (string) $path);
		$path = trim($path);

		if ($path === '' || $path === '/') {
			return '/';
		}

		if (str_contains($path, "\0") || preg_match('/[\x00-\x1F\x7F]/', $path) === 1) {
			throw new \InvalidArgumentException('Invalid Firefly path cannot contain unsafe chars');
		}

		$rawParts = explode('/', $path);
		$parts = [];

		foreach ($rawParts as $part) {
			if ($part === '') {
				continue;
			}

			if ($part === '.' || $part === '..') {
				throw new \InvalidArgumentException("Invalid Firefly path cannot contain '.' or '..'");
			}

			if (preg_match('/^[A-Za-z0-9._ -]+$/', $part) !== 1) {
				throw new \InvalidArgumentException('Invalid Firefly path contains unsupported characters');
			}

			$parts[] = $part;
		}

		if (empty($parts)) {
			return '/';
		}

		return '/' . implode('/', $parts);
	}
}
