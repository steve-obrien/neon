<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2015 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\db;

use neon\core\helpers\Hash;
use neon\core\interfaces\IArrayable;
use yii\base\ModelEvent;
use neon\core\db\ActiveQuery;


/**
 * ActiveRecord is the base class for ActiveRecords in Neon.
 */
class ActiveRecord extends \yii\db\ActiveRecord
{
	/**
	 * Whether we have the blamable columns `created_by` & `updated_by`
	 * @var bool
	 */
	public static $blame = false;

	/**
	 * Whether the model should be timestamped
	 * @var bool
	 */
	public static $timestamps = false;

	/**
	 * Whether this model implements soft delete
	 * @var bool
	 */
	public static $softDelete = false;

	/**
	 * To use a uuid 64 as primary key for this model instead of a auto increment id
	 * @var bool
	 */
	public static $idIsUuid64 = false;

	/**
	 * The column name for the uuid field
	 * Most of the time this can just be the id field as there is no point having both an id and a uuid
	 */
	const UUID = 'id';

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	const CREATED_AT = 'created_at';

	/**
	 * The name of the "created by" column.
	 *
	 * @var string
	 */
	const CREATED_BY = 'created_by';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var string
	 */
	const UPDATED_AT = 'updated_at';

	/**
	 * The name of the "updated by" column.
	 *
	 * @var string
	 */
	const UPDATED_BY = 'updated_by';

	/**
	 * The name of the "deleted" column.
	 *
	 * @var string
	 */
	const DELETED = 'deleted';

	/**
	 * @inheritdoc
	 */
	public function beforeSave($insert)
	{
		// update timestamps if required
		$this->_updateTimestamps();
		// Blame
		$this->_updateBlame();
		// Uuid
		if (static::$idIsUuid64 && $this->{static::UUID} == null) {
			$this->{static::UUID} = static::uuid64();
		}
		return parent::beforeSave($insert);
	}

	/******************************************************************************
	 * Implement timestamps behaviour
	 ******************************************************************************/

	/**
	 * Update the model's timestamps.
	 *
	 * @return bool  Whether successfully saved.
	 */
	public function touch()
	{
		return $this->_updateTimestamps() ? $this->save() : false;
	}

	/**
	 * Update the creation and update timestamps.
	 *
	 * @return boolean
	 */
	protected function _updateTimestamps()
	{
		if (static::$timestamps) {
			$this->fieldsMustExist([static::CREATED_AT, static::UPDATED_AT], 'The timestamp feature requires a "created_at" and "updated_at" field');
			$timestamp = date('Y-m-d H:i:s');
			if ($this->isClean(static::UPDATED_AT)) {
				$this->{static::UPDATED_AT} = $timestamp;
			}
			if ($this->isNewRecord && $this->isClean(static::CREATED_AT)) {
				$this->{static::CREATED_AT} = $timestamp;
			}
			return true;
		}
		return false;
	}

	/**
	 * Function checks the table has been set up correctly when applying particular inbuilt behaviours
	 *
	 * @param array $fields - e.g ['created_by', 'deleted']
	 * @param string $message - optional message to display in the exception if the fields don't exist
	 * @throws \InvalidArgumentException if fields don;t exist on the table
	 */
	public function fieldsMustExist($fields, $message='')
	{
		$tableName = $this->tableName();
		foreach($fields as $field) {
			if (!$this->hasAttribute($field))
				throw new \InvalidArgumentException("No '$field' exists on the '$tableName' table. $message");
		}
	}

	/**
	 * Update the blame attributes
	 *
	 * @return bool
	 */
	protected function _updateBlame()
	{
		if (static::$blame) {
			$this->fieldsMustExist([static::CREATED_BY, static::UPDATED_BY], 'The blame feature requires a "created_by" and "updated_by" field');
			if ($this->isNewRecord && $this->isClean(static::CREATED_BY)) {
				$this->{static::CREATED_BY} = neon()->isConsoleApp() ? null : neon()->user->id;
			}
			if ($this->isClean(static::UPDATED_BY)) {
				$this->{static::UPDATED_BY} = neon()->isConsoleApp() ? null : neon()->user->id;
			}
			return true;
		}
		return false;
	}

	/**
	 * Remove the record from the database - simply calls standard delete if softDelete is not supported
	 *
	 * @return int|false returns false if the record was not deleted
	 */
	public function destroy()
	{
		if (static::$softDelete) {
			static::$softDelete = false;
			$result = $this->delete();
			static::$softDelete = true;
			return $result;
		}
		return $this->delete();
	}

	/**
	 * Handles the 'beforeDelete' event,
	 * applying soft delete and preventing actual deleting.
	 *
	 * @return bool
	 */
	public function beforeDelete()
	{
		if (static::$softDelete) {
			$this->softDelete();
			// prevent normal delete operation
			return false;
		}

		// normal operation
		return parent::beforeDelete();
	}

	/**
	 * @inheritdoc
	 */
	public function delete()
	{
		if (static::$softDelete) {
			// prevent normal delete operation
			return $this->softDelete();
		}

		// normal operation
		return parent::delete();
	}

	/**
	 * Perform a soft delete
	 *
	 * @return boolean - whether the model successfully updated the deleted column
	 */
	public function softDelete()
	{
		$this->fieldsMustExist([static::DELETED], 'The soft delete feature requires a "deleted" field');
		$this->{static::DELETED} = 1;
		return $this->save();
	}

	/**
	 * Restore a previously soft deleted model
	 *
	 * @return boolean whether the model was successfully restored
	 */
	public function restore()
	{
		if (static::$softDelete) {
			$this->{static::DELETED} = 0;
			return $this->save();
		}
		return false;
	}

	/******************************************************************************
	 * Special model getters and setters -
	 * http://neon.dev/dev/docs/index?app=Core&doc=models#model-getters-and-setters
	 ******************************************************************************/

	/**
	 * Make getters for magic properties take precedence
	 * over the attributes - this allows you to format them before they are presented back
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		if ($this->hasAttributeGetter($name, $getter)) {
			// read property, e.g. getName()
			return $this->$getter(parent::__get($name));
		}
		// implement default behaviour
		return parent::__get($name);
	}

	/**
	 * Make setter methods of magic database attributes take precedence
	 * Use `$this->setAttribute($name, $value)` to set the database attribute property
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		// Make attribute setter methods of magic database attributes take precedence
		if ($this->hasAttributeSetter($name, $setter)) {
			return $this->$setter($value);
		}
		// implement default behaviour
		parent::__set($name, $value);
	}

	/**
	 * Whether this model has an attribute getter defined for the attribute specified by `$name`
	 * @param string $name the attribute name
	 * @param string $getter byRef string name of the getter function
	 * @return bool
	 */
	public function hasAttributeGetter($name, &$getter)
	{
		$getter = '__get_' . $name;
		return method_exists($this, $getter);
	}

	/**
	 * Whether a specific attribute setter method exists for this attribute (specified by name)
	 *
	 * @param string $name
	 * @param string $setter ByRef the setter function
	 * @return bool returns true if the attribute setter method exists
	 */
	public function hasAttributeSetter($name, &$setter)
	{
		$setter = '__set_' . $name;
		return method_exists($this, $setter);
	}

	/******************************************************************************
	 * Helpers
	 ******************************************************************************/

	/**
	 * Determine if the model or given attribute(s) have been modified.
	 *
	 * Valid Examples:
	 *
	 * ```php
	 * $this->isDirty('name', 'title'); // if the name and title attribute have been changed
	 * $this->isDirty(['name', 'title']); // if the name and title attribute have been changed
	 * $this->isDirty() // if any attribute has been changed
	 * ```
	 *
	 * @param  array|string|null  $attributes
	 * @return bool
	 */
	public function isDirty($attributes = null)
	{
		if (is_null($attributes)) {
			return count($this->getDirtyAttributes()) > 0;
		}
		// is a string - or lots of string arguments
		// like $this->isDirty('name', 'title');
		if (! is_array($attributes)) {
			$attributes = func_get_args();
		}

		foreach($attributes as $attribute) {
			return $this->isAttributeChanged($attribute);
		}

		return false;
	}

	/**
	 * Determine if the model or given attribute(s) have remained the same.
	 *
	 * @param  array|string|null  $attributes
	 * @return bool
	 */
	public function isClean($attributes = null)
	{
		return ! $this->isDirty(...func_get_args());
	}

	/**
	 * This is a placeholder. To potentially be overriden
	 * The previous hub system would look up the user credientials here.
	 * And look at the subdomain which mapped to the correct database to use.
	 * @return \neon\core\db\Connection Connection the database connection used by this AR class.
	 */
	public static function getDb()
	{
		return parent::getDb();
	}

	/**
	 * Check if the database table exists for this model
	 * @param array $schema
	 * @return boolean true if it exists
	 */
	public static function tableExists(&$schema)
	{
		$schema = static::getDb()->getSchema()->getTableSchema(static::tableName(), true);
		return ($schema !== null);
	}

	/**
	 * Generate a new UUID64
	 * @return string uuid64
	 */
	public static function uuid64()
	{
		return Hash::uuid64();
	}

	/**
	 * The Query class to create when calling a ::find() method
	 * @var string
	 */
	public static $queryClass = ActiveQuery::class;

	public static function findOrFail($id)
	{
		$record = self::findOne($id);
		if ($record===null) abort('No found');
		return $record;
	}

	/**
	 * @inheritdoc
	 */
	public static function find()
	{
		$query = \Neon::createObject(static::$queryClass, [get_called_class()]);
		if (static::$softDelete) {
			$query->andWhere([static::tableName() .'.'. static::DELETED => 0]);
		}
		return $query;
	}

	/**
	 * Return a query object that will include deleted objects too
	 * (simply returns a fresh query object without the initial where condition)
	 * @return ActiveQuery
	 */
	public static function findWithDeleted()
	{
		$query = new ActiveQuery(get_called_class());
		return $query;
	}

	/**
	 * Alias of find
	 * @param boolean $withDeleted
	 * @return ActiveQuery
	 */
	public static function query($withDeleted=false)
	{
		return $withDeleted ? static::findWithDeleted() : static::find();
	}
}
