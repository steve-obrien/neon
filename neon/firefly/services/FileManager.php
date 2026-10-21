<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 26/11/2016 12:24
 * @package neon
 */

namespace neon\firefly\services;

use neon\core\db\Query;
use neon\core\helpers\File;
use neon\core\helpers\Url;
use neon\daedalus\services\ddsManager\models\DdsMember;
use neon\firefly\services\fileManager\FileNotFoundException;
use \neon\firefly\services\fileManager\interfaces\IFileManager;
use \neon\firefly\services\fileManager\models\FileManager as Model;
use neon\firefly\services\fileManager\FileExceedsMemoryLimitException;
use yii\base\InvalidConfigException;
use yii\web\HttpException;

/**
 * The file manager intends to abstract things like paths and drives from the application
 * instead giving the application a uuid that refers to a file.  Meanwhile this file can be moved,
 * and changed about without breaking.
 * Class FileManager
 * @package neon\firefly
 */
class FileManager implements IFileManager
{

	/**
	 * The default drive to use to store files
	 * @var  string  the Drive name
	 */
	public $defaultDrive = null;

	/**
	 * @inheritdoc
	 */
	public function save($contents, $name = null, $meta = null)
	{
		if (FireflySecurityPolicy::safeFilenamesOnly() && $name!==null) {
			$name = File::getSafeUploadFilename($name);
		}
		$driver = $this->getDrive();
		$fm = new Model();
		$path = $this->_generatePath($fm, $name);
		$success = $driver->put($path, $contents);
		if (!$success) {
			return null;
		}
		if ($success) {
			$fm->attributes = [
				'name' => $name,
				'path' => $path,
				'drive' => $driver->getName(),
				'mime_type' => $driver->getMimeType($path),
				'size' => $driver->getSize($path),
				'meta' => $meta
			];
			$success = $fm->save();
		}
		return $success ? $fm->uuid : null;
	}

	/**
	 * @inheritdoc
	 */
	public function saveFile($file, $meta = null)
	{
		neon()->firefly->isFileObjectValid($file);
		$realPath = $file->getRealPath();
		if (FireflySecurityPolicy::safeFileRealpathsOnly()) {
			$realPath = $this->getSafeRealpath($realPath);
		}
		FireflySecurityPolicy::assertDetectedMimetype($realPath);

		$stream = fopen($realPath, 'rb');
		$uuid = $this->save($stream, $file->getFilename(), $meta);
		if (is_resource($stream)) fclose($stream);
		return $uuid;
	}

	/**
	 * @inheritdoc
	 */
	public function saveFromUrl($url, $name, $meta = null)
	{
		$stream = fopen($url, 'r');
		if ($stream === false) {
			throw new \RuntimeException("Could not open URL/file source");
		}
		FireflySecurityPolicy::assertDetectedMimetypeFromStream($stream);
		return $this->save($stream, $name, $meta);
	}

	/**
	 * Save a file from a url into firefly.
	 * Make sure the source is trusted - if its an image then you can run through imageManager->process
	 * @param string $url
	 * @param string $name
	 * @return string|null
	 */
	public function saveFileFromUrl($url, $name)
	{
		$stream = fopen($url, 'r');
		if ($stream === false) {
			throw new \RuntimeException("Could not open URL/file source");
		}
		FireflySecurityPolicy::assertDetectedMimetypeFromStream($stream);
		return $this->save($stream, $name);
	}

	/**
	 * Look up all dds fields that reference this item
	 * @param $uuid
	 *
	 */
	public function findReferences($uuid)
	{
		// find all image and file reference datatypes
		// does not seem to be an interface option to lookup all members of a type across dds classes
		// find all members with type:
		$all = DdsMember::find()->where(['data_type_ref' => 'image_ref'])
			->orWhere(['data_type_ref' => 'file_ref'])
			->orWhere(['data_type_ref' => 'file_ref_multi'])
			->all();
			
		if (empty($all))
			return [];

		$q = new Query();
		foreach ($all as $i => $field) {
			if ($i === 0) {
				$q->select(['_uuid'],"'$field[class_type]' as 'class',")
					->from('ddt_' . $field['class_type'])
					->where([$field['member_ref'] => $uuid]);
			} else {
				$q->union((new Query())->select(['_uuid'],"'$field[class_type]' as 'class',")
					->from('ddt_' . $field['class_type'])
					->where([$field['member_ref'] => $uuid]));
			}
		}
		return $q->all();
	}

	/**
	 * @inheritdoc
	 */
	public function getMeta($uuid)
	{
		try {
			$model = $this->_get($uuid);
		} catch (FileNotFoundException $e) {
			return [];
		}
		return $model->toArray();
	}

	/**
	 * @inheritdoc
	 */
	public function exists($uuid)
	{
		try {
			$this->_get($uuid);
		} catch (FileNotFoundException $e) {
			return false;
		}
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function read($uuid)
	{
		try {
			$drive = $this->getDriveFor($uuid, $file);
		} catch (FileNotFoundException $e) {
			return false;
		}
		$this->_checkFileSizeLimit($uuid);
		return $drive->read($file['path']);
	}

	/**
	 * @inheritdoc
	 */
	public function readStream($uuid)
	{
		try {
			$drive = $this->getDriveFor($uuid, $file);
		} catch (FileNotFoundException $e) {
			return false;
		}
		return $drive->readStream($file['path']);
	}

	/**
	 * @inheritdoc
	 */
	public function getUrl($uuid)
	{
		return  Url::to(['/firefly/file/get', 'id' => $uuid], true);
	}

	/**
	 * @inheritdoc
	 */
	public function getImage($uuid, $params=[])
	{
		$p = array_merge(['/firefly/file/img', 'id'=>$uuid], $params);
		return Url::to($p, true);
	}

	/**
	 * @inheritdoc
	 */
	public function getVisibility($uuid)
	{
		try {
			$drive = $this->getDriveFor($uuid, $file);
		} catch (FileNotFoundException $e) {
			return false;
		}
		return $drive->getVisibility($file['path']);
	}

	/**
	 * @inheritdoc
	 */
	public function setVisibility($uuid, $visibility)
	{
		try {
			$drive = $this->getDriveFor($uuid, $file);
		} catch (FileNotFoundException $e) {
			return false;
		}
		return $drive->setVisibility($file['path'], $visibility);
	}

	/**
	 * @inheritdoc
	 */
	public function copy($uuid)
	{
		$model = $this->_get($uuid);
		$content = neon()->firefly->readStream($uuid);
		$newUuid = neon()->firefly->save($content, $model->name, $model->meta);
		return $newUuid;
	}

	/**
	 * @inheritdoc
	 */
	public function delete($uuid)
	{
		try {
			$f = $this->_get($uuid);
			return $f->delete();
		} catch(FileNotFoundException $e) {
			return false;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function destroy($uuid)
	{
		$file = Model::findWithDeleted()->where(['uuid' => $uuid])->one();
		if ($file === null) return false;
		return $file->destroy();
	}

	/**
	 * @inheritdoc
	 */
	public function restore($uuid)
	{
		$f = $this->_get($uuid);
		return $f->restore();
	}

	/**
	 * @inheritdoc
	 */
	public function prepend($uuid, $contents, $separator = PHP_EOL)
	{
		$drive = $this->getDriveFor($uuid, $file);
		return $drive->prepend($file['path'], $contents, $separator);
	}

	/**
	 * @inheritdoc
	 */
	public function append($uuid, $contents, $separator = PHP_EOL)
	{
		$drive = $this->getDriveFor($uuid, $file);
		return $drive->append($file['path'], $contents, $separator);
	}

	/**
	 * @inheritdoc
	 */
	public function getDrive()
	{
		if (empty($this->defaultDrive)) {
			throw new InvalidConfigException('You must define a default drive for the File Manager - Set "defaultDrive" configuration option to be one of the Drive Managers configured drives - see the Drive Manager\'s "drives" config option');
		}
		return neon()->firefly->drive($this->defaultDrive);
	}

	/**
	 * @inheritdoc
	 */
	public function getDriveFor($uuid, &$file)
	{
		$file = $this->_get($uuid);
		return neon()->firefly->drive($file->drive);
	}

	/**
	 * @inheritdoc
	 */
	public function sendFile($uuid, $download=false)
	{
		$meta = $this->getMeta($uuid);
		$drive = $this->getDriveFor($uuid, $file);
		$read = $this->readStream($uuid);
		$meta['mime_type'] = $drive->getMimeType($meta['path']);
		// log the download stat
		if ($download === true) {
			$file->downloaded = $file->downloaded + 1;
			$file->save();
		}
		return neon()->response->sendStreamAsFile($read, $meta['name'], [
			'mimeType' => $meta['mime_type'],
			'inline' => !$download,
			'fileSize' => $meta['size']
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function getFileHash($uuid, $refresh = false)
	{
		$data = $this->getMeta($uuid);
		$hash = $data['file_hash'] ? $data['file_hash'] : null;
		if ($refresh || $hash === null) {
			$driver = $this->getDriveFor($uuid, $file);
			$hash = $driver->hash($file['path']);
			$model = $this->_get($uuid);
			if (!$hash) return false;
			$model->file_hash = $hash;
			$model->save();
		}
		return $hash;
	}

	/**
	 * @inheritdoc
	 */
	public function getFileName($uuid)
	{
		$meta = $this->getMeta($uuid);
		return isset($meta['name']) ? $meta['name'] : false ;
	}

	/**
	 * @inheritdoc
	 */
	public function setFileName($uuid, $name)
	{
		try {
			$drive = $this->getDriveFor($uuid, $file);
		} catch (FileNotFoundException $e) {
			return false;
		}
		if (FireflySecurityPolicy::safeFilenamesOnly()) {
			$name = File::getSafeUploadFilename($name);
		}
		$file->name = $name;
		return $file->save();
	}

	/**
	 * @inheritdoc
	 */
	public function getSize($uuid)
	{
		$meta = $this->getMeta($uuid);
		return isset($meta['size']) ? $meta['size'] : false ;
	}

	/**
	 * @inheritdoc
	 */
	public function setMeta($uuid, $meta)
	{
		try {
			$model = $this->_get($uuid);
			$model->meta = $meta;
			return $model->save();
		} catch (FileNotFoundException $e) {
			return false;
		}
	}

	/**
	 * Update file fields
	 * 
	 * @param string $uuid - the uuid of the file
	 * @param array $updates - and array of updates keys matching field names
	 * @return array the updated model
	 */
	public function update($uuid, $updates)
	{
		$model = $this->_get($uuid);
		$model->attributes = $updates;
		if (!$model->save())
			throw new HttpException(400, $model->getErrors());
		return $model->toArray();
	}

	/**
	 * Find a file manager file model by its path and drive location
	 *
	 * @param string $drive
	 * @param string $path
	 * @return array|null
	 */
	public function findFile($drive, $path)
	{
		$model = Model::find()->where(['drive' => $drive, 'path' => $path])->one();
		if ($model === null)
			return null;
		return $model->toArray();
	}

	/**
	 * Checks the file does not exceed PHP memory limit - and throws an exception.
	 *
	 * @param $uuid
	 * @throws \neon\firefly\services\fileManager\FileExceedsMemoryLimitException
	 */
	protected function _checkFileSizeLimit($uuid)
	{
		if (function_exists('ini_get')) {
			$bytes = to_bytes(ini_get('memory_limit'));
			$size = $this->getSize($uuid);
			if ($size >= $bytes) {
				throw new FileExceedsMemoryLimitException();
			}
		}
	}

	/**
	 * Get the row from the database by uuid.
	 * This function is a stub as can add a simple session cache.
	 *
	 * @param  string $uuid
	 * @return  Model
	 */
	protected function _get($uuid)
	{
		return Model::get($uuid);
	}

	/**
	 * Auto generate a sensible path
	 * By default this will generate path based on the year/month/[fileName]_[uuid].extension
	 * where [fileName] is the origin file name supplied
	 * and [uuid] is the filemanager uuid key for this file
	 * e.g. 2016/11
	 *
	 * @param  string  $name  - The actual file name
	 * @return  string  - The full file past on the drive
	 */
	protected function _generatePath($fm, &$name=null)
	{
		$info = pathinfo($name);
		$fileName = $info['filename'];
		$extension = isset($info['extension']) ? '.' . $info['extension'] : '';
		if (empty($fileName)) {
			$fileName = 'nameless';
			$name = 'nameless';
		}
		return date('Y') . '/' . date('m') . '/' . $fileName . '.' . $fm->uuid . $extension;
	}

	/**
	 * tries to ensure a realpath is actually a realpath and is safe & available
	 * @param $realPath
	 * @return string
	 */
	private function getSafeRealpath($realPath): string
	{
		if (!is_string($realPath) || $realPath === '') {
			throw new \InvalidArgumentException('Invalid file path');
		}
		if (str_contains($realPath, "\0")) {
			throw new \InvalidArgumentException('Invalid file path');
		}
		if (preg_match('#^[a-zA-Z][a-zA-Z0-9+\-.]*://#', $realPath) === 1) {
			throw new \InvalidArgumentException('Stream wrappers are not allowed');
		}
		$realPath = realpath($realPath);
		if ($realPath === false || !is_file($realPath) || !is_readable($realPath)) {
			throw new \InvalidArgumentException('Invalid file path');
		}
		return $realPath;
	}

}
