<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2018- Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\phoebe\services\adapters\common;

use yii\base\Component;
use neon\phoebe\interfaces\IPhoebeObject;
use neon\phoebe\services\adapters\common\PhoebeBase;
use neon\phoebe\services\adapters\common\models\PhoebeObject as ObjectModel;
use neon\phoebe\services\adapters\common\models\PhoebeObjectHistory as ObjectHistoryModel;
use neon\core\helpers\Hash;

/**
 * The PhoebeBase provides default or phoebe specific implmentations for certain
 * methods. It knows what type of phoebe interface it is managing
 */
class PhoebeObjectBase extends PhoebeBase
implements
	IPhoebeObject
{

	/** ------------------------------------------- **/
	/** ---------- Phoebe Object Methods ---------- **/

	/** -------------------------------------- **/
	/** ---------- Object Lifecycle ---------- **/

	/**
	 * The base object behaviour is to replace the data.
	 * If you need to do any processing of the data - e.g. to save to other
	 * database tables then override this method and call this for saving
	 * in Phoebe.
	 *
	 * @inheritdoc
	 */
	public function editObject($changes)
	{
		// NOTE - changes are only saved into $this->data and not
		// to the phoebe object in general.

		if (empty($changes) || $changes == $this->data)
			return true;

		// Get the associated class object
		$classType = $this->getClassType();
		$class = $this->getClassModel($classType);

		if (!$class)
			throw new \RuntimeException("Class of type '$classType' not found. Can't save objects of unknown types");

		// save the history object
		if ($class->object_history) {
			$this->saveObjectHistory();
		}

		// update the version if the class is not version locked
		if (!$class->version_locked)
			$this->version = $class->version;

		// merge the changes and save the model
		$this->data = $changes;
		$this->updated = date('Y-m-d H:i:s');
		$model = $this->getModel();
		if (!$model->save())
			return $model->errors;
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function deleteObject()
	{
		return $this->modelDelete();
	}

	/**
	 * @inheritdoc
	 */
	public function undeleteObject()
	{
		return $this->modelUndelete();
	}

	/**
	 * @inheritdoc
	 */
	public function destroyObject()
	{
		$this->clearHistory();
		$this->getModel()->delete();
	}

	/**
	 * @inheritdoc
	 */
	public function getChangeLogUuid()
	{
		return null;
	}

	/** ------------------------------------------ **/
	/** ---------- Object Class Methods ---------- **/

	/**
	 * @inheritdoc
	 */
	public function getIPhoebeClass()
	{
		$model = $this->getModel();
		$classModel = $this->getClassModel($model->class_type, $model->version);
		$class = $this->createIPhoebeClass($classModel);
		$class->applyClassOverrideByUuid($this->getOverride());
		return $class;
	}

	/**
	 * @inheritdoc
	 */
	public function setOverride($overrideUuid, $force=false)
	{
		// don't override an override unless forced to do so
		if (!$force && $this->override_uuid)
			return false;

		// Get the associated class object and check the override applies to this class
		if ($this->getClassOverrideModel($overrideUuid, $this->version)) {
			$this->override_uuid = $overrideUuid;
			return $this->getModel()->save();
		}
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function getOverride()
	{
		return $this->override_uuid;
	}


	/** -------------------------------------- **/
	/** ----------- Object History ----------- **/

	/**
	 * @inheritdoc
	 */
	public function listHistory(&$paginator=[])
	{
		// check the inputs
		$paginator = $this->createPaginator($paginator);

		$query = ObjectHistoryModel::find()
			->where(['object_uuid' => $this->uuid]);

		if ($paginator['start']==0)
			$paginator['total'] = $query->count();

		// and get the results
		return $query->offset($paginator['start'])
			->limit($paginator['length'])
			->orderBy('archived DESC')
			->asArray()
			->all();
	}

	/**
	 * @inheritdoc
	 */
	public function getHistoricalObject($historicalUuid)
	{
		return ObjectHistoryModel::find()
			->where([
				'uuid' => $historicalUuid,
				'object_uuid' => $this->uuid
			])
			->asArray()
			->one();
	}

	/**
	 * @inheritdoc
	 */
	public function clearHistory()
	{
		$uuid = $this->uuid;
		if (!empty($uuid)) {
			return ObjectHistoryModel::deleteAll([
				'object_uuid' => $uuid
			]);
		}
		return 0;
	}

	/** ---------------------------------------------- **/
	/** ----------- Implementation Methods ----------- **/

	/**
	 * Gets the data that is a pseudo attribute on the model so not found
	 * with the phoebeBase __get method nor Yii's
	 * @return array
	 */
	public function getData()
	{
		return $this->getModel()->data;
	}

	/**
	 * Sets the data as it is a pseudo attribute on the model so not found
	 * with the phoebeBase __set method nor Yii's
	 * @return array
	 */
	public function setData($value)
	{
		$this->getModel()->data = $value;
	}


	/**
	 * Save the object's history
	 * @param object $object
	 * @throws \RuntimeException
	 */
	protected function saveObjectHistory()
	{
		$hmodel = new ObjectHistoryModel;
		$hmodel->uuid = Hash::uuid64();
		$hmodel->object_uuid = $this->uuid;
		$hmodel->phoebe_type = $this->phoebe_type;
		$hmodel->class_type = $this->class_type;
		$hmodel->version = $this->version;
		$hmodel->override_uuid = $this->override_uuid;
		$hmodel->serialised_data = $this->serialised_data;
		if (!$hmodel->save())
			throw new \RuntimeException("Failed to save the history of the object. Model errors were ".print_r($hmodel->errors,true));
	}

}