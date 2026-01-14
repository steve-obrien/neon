<?php

namespace neon\firefly;

use neon\core\BaseApp;
use neon\firefly\services\driveManager\interfaces\IFile;
use neon\firefly\services\fileManager\interfaces\IFileManager;
use neon\firefly\services\driveManager\interfaces\IFileSystemFactory;
use neon\firefly\services\fileManager\models\FileManager;

/**
 * The neon firefly app class
 *
 * @property  \neon\firefly\services\FileManager  $fileManager
 * @property  \neon\firefly\services\driveManager\interfaces\IFileSystemFactory  $driveManager
 * @property  \neon\firefly\services\mediaManager\interfaces\IMediaManager  $mediaManager
 * @property  \neon\firefly\services\imageManager $imageManager
 */
class App extends BaseApp implements IFileManager, IFileSystemFactory
{
	/**
	 * @var array of AccessRule configuration object definitions
	 * @see \yii\filters\AccessRule configurations
	 *
	 * ```php
	 * return [
	 *     [
	 *         'allow' => true,
	 *         'roles' => ['@'],
	 *         'permission' => ['somePermissionRule']
	 *     ],
	 * ];
	 */
	public $fileAccessRules = null;
	/**
	 * The name of the cache component to use accessible from neon
	 * defaults to 'cache'
	 * This prevents accisently using neon()->firefly->cache instead of getCache
	 * using cache would return the core "cache" component
	 * @var string
	 */
	public $cache = '';

	/**
	 * Get the configured neon cache component - defaults to 'cache'
	 * Note: Do not shorten to neon()->firefly->cache - this will return the core cache component
	 * as neon()->cache is valid and BaseApp inherits of the base serviceLocator where this is registered
	 * @return null|\yii\caching\Cache
	 */
	public function getCache()
	{
		return neon()->get($this->cache);
	}

	/**
	 * @inheritdoc
	 */
	public function configure()
	{
		if (!$this->has('mediaManager')) {
			$this->set('mediaManager', [
				'class' => '\neon\firefly\services\MediaManager'
			]);
		}
		// set path to IMediaManager interface
		$this->set('IMediaManager', [
			'class' => '\neon\firefly\services\MediaManager'
		]);
		if (!$this->has('imageManager')) {
			$this->set('imageManager', [
				'class' => '\neon\firefly\services\ImageManager'
			]);
		}
		if (!$this->has('fileManager')) {
			$this->set('fileManager', [
				'class' => '\neon\firefly\services\FileManager'
			]);
		}
		// set path to IFileManager interface
		$this->set('IFileManager', [
			'class' => '\neon\firefly\services\FileManager'
		]);
		if (!$this->has('driveManager')) {
			$this->set('driveManager', [
				'class' => '\neon\firefly\services\DriveManager'
			]);
		}
	}

	public function setup()
	{
		// This is nice but breaks passing in asset urls.
		// f/http://* causes chaos. Understandably.
		// neon()->urlManager->addRules([
		// 	'f/<id>' => 'firefly/file/get',
		// 	'f/<id>' => 'firefly/file/img',
		// ]);

		// neon()->urlManager->addRules([
		// 	'ff/get' => 'firefly/file/get',
		// 	'ff/img' => 'firefly/file/img',
		// ]);
	}

	/**
	 * request an IMediaManager interface
	 * @return \neon\firefly\services\mediaManager\interfaces\IMediaManager
	 */
	public function getIMediaManager() {
		return $this->get('IMediaManager');
	}

	/**
	 * request an IFileManager interface
	 * @return \neon\firefly\services\fileManager\interfaces\IFileManager
	 */
	public function getIFileManager() {
		return $this->get('IFileManager');
	}

	public $menu = false;

	/**
	 * @inheritdoc
	 */
	public function getDefaultMenu()
	{
		if ($this->menu === false) {
			return [
				'label' => 'Media',
				'order' => 1200,
				'url' => ['/firefly/media/index'],
				'visible' => neon()->user->hasRole('neon-administrator'),
				'active' => is_route('/firefly/*'),
			];
		}
		return $this->menu;
	}

	/**
	 * Pass dynamic methods call onto FileManager.
	 * This function would remove the need for all functions below.
	 * However I do like being explicit.
	 * For example this will not deal with any possible clashes between the IFileManager interface and the
	 * IApp interface that this class shares...
	 * On the bench... We could also fix the intelisense using the @ method properties in the header.
	 * But it is yet another place to keep in sync.  By explicitly adding the functions we can use @ inheritdoc.
	 * Both options are negligible performance wise so this is a maintainability thingy.
	 *
	 * The other annoying thing about this is PHP will not believe this class implements the interface if we are not explicit.
	 * This is annoying because its useful for injection and reflection code to know this object implements the
	 * IFileManager, IFileSystemFactory interfaces
	 *
	 * @param  string  $method
	 * @param  array  $parameters
	 * @throws \BadMethodCallException
	 * @return mixed
	 *
	 * public function __call($method, array $parameters)
	 * {
	 *	return call_user_func_array([$this->fileManager, $method], $parameters);
	 * }
	 */

	/**
	 * @inheritdoc
	 */
	public function save($contents, $name = null, $meta = null)
	{
		return $this->fileManager->save($contents, $name, $meta);
	}

	/**
	 * @inheritdoc
	 */
	public function saveFile($file, $meta = null)
	{
		return $this->fileManager->saveFile($file);
	}

	/**
	 * @inheritdoc
	 */
	public function saveFromUrl($url, $name, $meta = null)
	{
		return $this->fileManager->saveFromUrl($url, $name, $meta);
	}

	/**
	 * @inheritdoc
	 */
	public function exists($uuid)
	{
		return $this->fileManager->exists($uuid);
	}

	/**
	 * @inheritdoc
	 */
	public function getMeta($uuid)
	{
		return $this->fileManager->getMeta($uuid);
	}

	/**
	 * @inheritdoc
	 */
	public function getFileName($uuid)
	{
		return $this->fileManager->getFileName($uuid);
	}

	/**
	 * @inheritdoc
	 */
	public function setFileName($uuid, $name)
	{
		return $this->fileManager->setFileName($uuid, $name);
	}

	/**
	 * @inheritdoc
	 */
	public function getUrl($uuid)
	{
		return $this->fileManager->getUrl($uuid);
	}

	/**
	 * @inheritdoc
	 */
	public function getImage($uuid, $params=[])
	{
		return $this->fileManager->getImage($uuid, $params);
	}

	/**
	 * @inheritdoc
	 */
	public function getVisibility($uuid)
	{
		return $this->fileManager->getVisibility($uuid);
	}

	/**
	 * @inheritdoc
	 */
	public function setVisibility($uuid, $visibility)
	{
		return $this->fileManager->setVisibility($uuid, $visibility);
	}

	/**
	 * @inheritdoc
	 */
	public function copy($uuid)
	{
		return $this->fileManager->copy($uuid);
	}

	/**
	 * @inheritdoc
	 */
	public function delete($uuid)
	{
		return $this->fileManager->delete($uuid);
	}

	/**
	 * @inheritdoc
	 */
	public function restore($uuid)
	{
		return $this->fileManager->restore($uuid);
	}

	/**
	 * @inheritdoc
	 */
	public function destroy($uuid)
	{
		return $this->fileManager->destroy($uuid);
	}

	/**
	 * @inheritdoc
	 */
	public function prepend($uuid, $contents, $separator = PHP_EOL)
	{
		return $this->fileManager->prepend($uuid, $contents, $separator);
	}

	/**
	 * @inheritdoc
	 */
	public function append($uuid, $contents, $separator = PHP_EOL)
	{
		return $this->fileManager->append($uuid, $contents, $separator);
	}

	/**
	 * @inheritdoc
	 */
	public function read($uuid)
	{
		return $this->fileManager->read($uuid);
	}

	/**
	 * @inheritdoc
	 */
	public function readStream($uuid)
	{
		return $this->fileManager->readStream($uuid);
	}

	/**
	 * @inheritdoc
	 */
	public function getFileHash($uuid, $refresh = false)
	{
		return $this->fileManager->getFileHash($uuid, $refresh);
	}

	/**
	 * @param  string  $name
	 * @return \neon\firefly\services\driveManager\interfaces\IFileSystem
	 */
	public function drive($name=null)
	{
		return $this->driveManager->drive($name);
	}

	/**
	 * @inheritdoc
	 */
	public function sendFile($uuid, $download=false)
	{
		return $this->fileManager->sendFile($uuid, $download);
	}

	/**
	 * Configure a response object
	 * @deprecated
	 * @param  string  $uuid
	 * @return \neon\core\web\Response
	 */
	public function response($uuid)
	{
		return $this->fileManager->sendFile($uuid);
	}

	/**
	 * @inheritdoc
	 */
	public function getSize($uuid)
	{
		return $this->fileManager->getSize($uuid);
	}

	/**
	 * @inheritdoc
	 */
	public function setMeta($uuid, $meta)
	{
		return $this->fileManager->setMeta($uuid, $meta);
	}

	/**
	 * @inheritDoc
	 */
	public function findFile($drive, $path)
	{
		return $this->fileManager->findFile($drive, $path);
	}

	/**
	 * @inheritDoc
	 */
	public function getDriveFor($uuid, &$file)
	{
		return $this->fileManager->getDriveFor($uuid, $file);
	}

	/**
	 * Check the object is valid
	 * The $file param must be an object with getRealPath and getFilename functions
	 *
	 * @param mixed $file
	 * @param boolean $throwExceptions - whether to throw exceptions on validation failure
	 * @return boolean
	 */
	public function isFileObjectValid($file, $throwExceptions=true)
	{
		if ($file instanceof IFile)
			return true;
		// not all objects have the privilege of implementing the IFile interface like \SplFileObject
		// Firefly only cares about these two functions 'getRealPath' and 'getFilename'
		$objectIsValid = is_object($file)
			&& method_exists($file, 'getRealPath')
			&& method_exists($file, 'getFilename');
		// the $file param must be an object with getRealPath and getFilename functions
		if (! $objectIsValid && $throwExceptions) {
			throw new \InvalidArgumentException('$file param must be an object with a getRealPath and getFilename method');
		}
		return $objectIsValid;
	}
}
