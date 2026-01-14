<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 23/11/2016 23:44
 * @package neon
 */

namespace neon\firefly\services\fileManager\models;

use \neon\core\db\ActiveRecord;
use \neon\core\helpers\Hash;
use neon\firefly\services\fileManager\FileNotFoundException;

/**
 * This is the model class for table "firefly_file_manager".
 *
 * @property string $uuid
 * @property string $name
 * @property string $file_hash
 * @property string $path
 * @property string $drive
 * @property string $mime_type
 * @property integer $size
 * @property integer $downloaded
 * @property integer $visibility
 * @property integer $viewed
 * @property integer $updated_by
 * @property string $updated_at
 * @property integer $created_by
 * @property string $created_at
 * @property string $deleted_at
 * @property mixed $meta
 */
class FileManager extends ActiveRecord
{
	public static $blame = true;

	public static $timestamps = true;

	public static $softDelete = true;

	const UUID = 'uuid';

	/**
	 * @param  string  $drive   the drive name e.g. 'local' | 's3'
	 * @param  string  $path
	 */
	public static function findFileByPath($drive, $path)
	{
		static::find()->where(['drive' => $drive, 'path' => $path])->one();
	}

	/**
	 * Start up the model lets create a uuid;
	 */
	public function init()
	{
		$this->uuid = self::uuid();
		parent::init();
	}

	/**
	 * Get a uuid
	 */
	public static function uuid()
	{
		return Hash::uuid64();
	}

	/**
	 * @return string table name
	 */
	public static function tableName()
	{
		return 'firefly_file_manager';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['uuid'], 'required'],
			[['size', 'downloaded', 'viewed', 'updated_by', 'created_by'], 'integer'],
			[['meta', 'alt', 'caption', 'updated_at', 'created_at', 'deleted_at', 'created_by', 'updated_by'], 'safe'],
			[['uuid'], 'string', 'max' => 22],
			[['name'], 'string', 'max' => 100],
			[['file_hash'], 'string', 'max' => 32],
			[['path'], 'string', 'max' => 500],
			[['drive'], 'string', 'max' => 50],
			[['mime_type'], 'string', 'max' => 255],
		];
	}

	/**
	 * Get and cache a file record by uuid
	 * We do not use the firefly image cache here - as this is a data only cache not image processing cache
	 *
	 * @param string $uuid
	 * @return FileManager|null
	 */
	public static function get($uuid, $throwNotFoundExcaption=true): self
	{
		$model = neon()->cache->getOrSet($uuid, static function() use ($uuid) {
			return static::findOne($uuid);
		});
		if ($model === null && $throwNotFoundExcaption)
			throw new FileNotFoundException("No file with id '$uuid' exists.");
		return $model;
	}

	/**
	 * @inheritDoc
	 */
	public function afterSave($insert, $changedAttributes)
	{
		neon()->cache->delete($this->uuid);
		parent::afterSave($insert, $changedAttributes);
	}

	/**
	 * @inheritDoc
	 * Also removes the physical file the record is tracking
	 */
	public function destroy()
	{
		neon()->cache->delete($this->uuid);
		// remove the file from disk
		$driver = neon()->firefly->driveManager->drive($this->drive);
		$driver->delete($this->path);
		return parent::destroy();
	}

	/**
	 * Automatically decode json
	 * @param string $value
	 * @return mixed
	 */
	public function __get_meta($value)
	{
		return json_decode($value);
	}

	/**
	 * Automatically encode meta field as json when set
	 * @param array $value - data to be converted to json string
	 */
	public function __set_meta($value)
	{
		$this->setAttribute('meta', json_encode($value));
	}

	/**
	 * We will likely refactor the uuid column to simply be `id`
	 * So lets add the id for now as well as uuid
	 * @return array
	 */
	public function fields()
	{
		$fields = parent::fields();
		$fields['id'] = $fields['uuid'];
		//$fields['meta'] = $this->meta;

		return $fields;
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'uuid' => 'Uuid',
			'name' => 'Name',
			'file_hash' => 'File Hash',
			'path' => 'Path',
			'drive' => 'Drive',
			'mime_type' => 'Mime Type',
			'size' => 'File Size (Bytes)',
			'downloaded' => 'Downloaded',
			'viewed' => 'Viewed',
			'updated_by' => 'Updated By',
			'updated_at' => 'Updated At',
			'created_by' => 'Created By',
			'created_at' => 'Created At',
			'deleted_at' => 'Deleted At',
			'meta' => 'Meta',
			'alt' => 'Alt',
			'caption' => 'Caption',
		];
	}
}
