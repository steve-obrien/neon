<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2018- Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\phoebe\services\adapters\common;

use neon\core\form\fields\Date;
use neon\core\form\fields\Integer;
use neon\core\form\fields\Json;
use neon\core\form\fields\Text;
use yii\base\Component;
use neon\phoebe\interfaces\IPhoebeClass;
use neon\phoebe\services\adapters\common\PhoebeBase;
use neon\phoebe\services\adapters\common\models\PhoebeClass as ClassModel;
use neon\phoebe\services\adapters\common\models\PhoebeClassOverrides as OverridesModel;
use neon\phoebe\services\adapters\common\models\PhoebeObject as ObjectModel;
use neon\phoebe\services\adapters\common\models\PhoebeObjectHistory as ObjectHistoryModel;
use neon\core\helpers\Hash;

/**
 * The PhoebeClassBase provides default or phoebe specific implementations for certain
 * methods. It knows what type of phoebe interface it is managing
 */
abstract class PhoebeClassBase extends PhoebeBase
implements
	IPhoebeClass
{
	/** ------------------------------------------- **/
	/** ---------- IPhoebe Class Methods ---------- **/

	/**
	 * @inheritdoc
	 */
	public function getObjectGridDefinition($additionalColumns = [])
	{
		// return a grid definition based on the PhobeObject table only
		$definition = [
			'uuid' => [
				'class' => 'neon\core\grid\column\DefinitionColumn',
				'title' => 'Unique Id',
				'member' => new Text('uuid'),
			],
			'version' => [
				'title' => 'Version',
				'class' => 'neon\core\grid\column\DefinitionColumn',
				'member' => new Integer('uuid'),
			],
			'created' => [
				'class' => 'neon\core\grid\column\DefinitionColumn',
				'title' => 'Date Created',
				'member' => new Date('created'),
			],
			'updated' => [
				'class' => 'neon\core\grid\column\DefinitionColumn',
				'title' => 'Last Updated',
				'member' => new Date('updated'),
			]
		];
		foreach ($additionalColumns as $column) {
			switch($column) {
				case 'data':
					$definition['serialised_data'] = [
						'class' => 'neon\core\grid\column\DefinitionColumn',
						'title' => 'Data',
						'member' => new Json('serialised_data')
					];
				break;
				case 'deleted':
					$definition['deleted'] = [
						'class' => 'neon\core\grid\column\IntColumn',
						'title' => 'Deleted'
					];
				break;
			}
		}
		return $definition;
	}

	/**
	 * @inheritdoc
	 */
	public function editClass($changes)
	{
		$allowedChanges = [
			'label', 'description', 'version', 'version_locked', 'object_history', 'volatile', 'definition'
		];
		$changes = array_intersect_key($changes, array_flip($allowedChanges));

		// work out whether or not we are actually going to make an edit
		$model = $this->getModel();
		$oldAttrs = $model->attributes;

		// if we are locking objects to versions then an increase in version needs to
		// result in a new row in the phoebeClass relation regardless of changes
		$hasChanges = false;
		$newModel=false;
		try {
			if ($model->version_locked && isset($changes['version']) && $changes['version'] != $model->version) {
				$newModel = true;
				$model = new ClassModel();
				$model->attributes = array_merge($oldAttrs, $changes);
				$model->updated = date('Y-m-d H:i:s');
				if (!$model->save())
					throw new \RuntimeException('Save failed with the following errors '.print_r($model->errors,true));
				$this->setModel($model);
				$hasChanges = true;
			} else {
				// so we are updating our existing model
				foreach ($changes as $field=>$change) {
					if ($model->$field != $change) {
						$model->$field = $change;
						$hasChanges = true;
					}
				}
				if ($hasChanges) {
					$model->updated = date('Y-m-d H:i:s');
					if (!$model->save()) {
						throw new \RuntimeException('Save failed with the following errors: '.print_r($model->errors,true));
					}
				}
			}
		} catch (\Exception $e) {
			$errors = $model->errors;
			$model->attributes = $oldAttrs;
			return $errors;
		}
		if ($hasChanges)
			$this->createEditClassMigration($model->attributes, $oldAttrs, $newModel);
		return $hasChanges;
	}

	/**
	 * @inheritdoc
	 */
	public function deleteClass()
	{
		$model = $this->getModel();
		$oldAttrs = $model->attributes;
		if (($result=$this->modelDelete()) === true) {
			$newAttrs = $model->attributes;
			$this->createEditClassMigration($newAttrs, $oldAttrs, false);
		}
		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function undeleteClass()
	{
		$model = $this->getModel();
		$oldAttrs = $model->attributes;
		if (($result=$this->modelUndelete()) === true) {
			$newAttrs = $model->attributes;
			$this->createEditClassMigration($newAttrs, $oldAttrs, false);
		}
		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function destroyClass()
	{
		// return false;
		$classes = ClassModel::find()
			->where([
				'phoebe_type'=>$this->getPhoebeType(),
				'class_type'=>$this->getClassType(),
			])
			->asArray()
			->all();
		if (count($classes)) {
			$objectCount = $this->destroyClassObjects();
			$result = ClassModel::deleteAll([
				'phoebe_type'=>$this->getPhoebeType(),
				'class_type'=>$this->getClassType()
			]);
			if ($result) {
				$up = "DELETE FROM `phoebe_class` WHERE `phoebe_type`='".$this->getPhoebeType()."' AND `class_type`='".$this->getClassType()."';";
				$downs = [];
				foreach ($classes as $class) {
					$downs[] = $this->getTableRowReplaceSql('phoebe_class', $class);
				}
				$this->storeMigration($up, $downs);
			}
			return ($result + $objectCount);
		}
		return 0;
	}

	/** ----------------------------------------- **/
	/** -------- Applying Class Overrides ------- **/

	/**
	 * Set any overrides onto the class
	 * @var array
	 */
	protected $_overrides=null;

	/**
	 * @inheritdoc
	 */
	public function getOverrides()
	{
		return $this->_overrides;
	}

	/**
	 * @inheritdoc
	 */
	public function applyClassOverrideByUuid($overrideUuid)
	{
		if ($overrideUuid) {
			$override = $this->getOverride($overrideUuid);
			if (isset($override['overrides']))
				$this->applyClassOverrideByDefinition($override['overrides']);
			else
				$this->applyClassOverrideByDefinition([]);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function applyClassOverrideByDefinition($overrides)
	{
		$this->_overrides = $overrides;
	}


	/** -------------------------------------------------- **/
	/** -------- Class Override Definition Methods ------- **/

	/**
	 * @inheritdoc
	 */
	public function listOverrides()
	{
		$class = $this->getModel();
		$overrides = OverridesModel::find()
			->select(['uuid', 'label'])
			->where([
				'phoebe_type' => $class->phoebe_type,
				'class_type' => $class->class_type,
				'version' => $class->version,
				'deleted' => 0
			])->asArray()->all();
		$list = [];
		foreach ($overrides as $o)
			$list[$o['uuid']] = $o['label'];
		return $list;
	}

	/**
	 * @inheritdoc
	 */
	public function findOverride($filters=[])
	{
		return $this->findClassOverride($filters);
	}

	/**
	 * @inheritdoc
	 */
	public function getOverride($uuid)
	{
		// get an object first to let any conversions be made
		$override = $this->getClassOverrideModel($uuid);
		if ($override) {
			// then extract out into an array
			return array_intersect_key(
				$override->toArray(),
				array_flip([
					'uuid', 'label', 'selector', 'is_active',
					'active_from', 'active_to', 'overrides'
				]
			));
		}
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function addOverride($label, $selector='', $activeFrom=null, $activeTo=null, $isActive=true)
	{
		$uuid = Hash::uuid64();
		$model = $this->getModel();
		$override = new OverridesModel;
		$override->uuid = $uuid;
		$override->phoebe_type = $model->phoebe_type;
		$override->class_type = $model->class_type;
		$override->version = $model->version;
		$override->label = $label;
		$override->selector = $selector;
		$override->active_from = $this->setDateToNowOnEmpty($activeFrom);
		$override->active_to = $activeTo;
		$override->is_active = $isActive;
		$override->created = date('Y-m-d H:i:s');
		$override->deleted = 0;
		$override->overrides = [];
		if ($override->save())
			return $uuid;
		return $override->errors;
	}

	/**
	 * @inheritdoc
	 */
	public function editOverride($uuid, $changes)
	{
		$changes = array_intersect_key($changes, array_flip(
			[
				'label', 'selector', 'active_from', 'active_to', 'is_active', 'overrides'
			])
		);

		// nothing to see here move along
		if (count($changes)===0)
			return true;
		// ok now do the edit
		$override = $this->getClassOverrideModel($uuid);
		if ($override) {
			foreach ($changes as $key=>$value) {
				if ($key == 'active_from')
					$value = $this->setDateToNowOnEmpty($value);
				$override->$key = $value;
			}
			if ($override->save())
				return true;
			return $override->errors;
		}
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function deleteOverride($uuid)
	{
		$override = $this->getClassOverrideModel($uuid);
		if ($override) {
			$override->deleted = 1;
			$override->save();
		}
	}

	/**
	 * @inheritdoc
	 */
	public function undeleteOverride($uuid)
	{
		$override = $this->getClassOverrideModel($uuid);
		if ($override) {
			$override->deleted = 0;
			$override->save();
		}
	}

	/**
	 * @inheritdoc
	 */
	public function destroyOverride($uuid)
	{
		$override = $this->getClassOverrideModel($uuid);
		if ($override)
			$override->delete();
	}


	/** --------------------------------------- **/
	/** -------- Implementation Methods ------- **/

	/**
	 * Gets the definition that is a pseudo parameter on the model so not found
	 * with the phoebeBase __get method nor Yii's
	 * @return array
	 */
	public function getDefinition()
	{
		$definition = $this->getModel()->definition;
		if ($this->_overrides)
			$definition = array_replace_recursive($definition, $this->_overrides);
		return $definition;
	}

	/**
	 * Destroy all objects associated with this class including
	 * any historical versions of them
	 * @return int  the number of objects deleted (excludes history)
	 */
	protected function destroyClassObjects()
	{
		$phoebeType = $this->getPhoebeType();
		$classType = $this->getClassType();

		// don't do this unless we know definitely what we are
		if (!$phoebeType || !$classType)
			return;

		// clear out any override objects
		OverridesModel::deleteAll([
			'phoebe_type'=>$phoebeType,
			'class_type'=>$classType
		]);

		// clear all of the history objects
		ObjectHistoryModel::deleteAll([
			'phoebe_type'=>$phoebeType,
			'class_type'=>$classType
		]);

		// and clear all of the objects
		return ObjectModel::deleteAll([
			'phoebe_type'=>$phoebeType,
			'class_type'=>$classType
		]);
	}

	/**
	 * Create a migration because of an edit on a phoebeClass
	 * @param array $newAttrs
	 * @param array $oldAttrs
	 * @param boolean $newModel
	 */
	protected function createEditClassMigration($newAttrs, $oldAttrs, $newModel)
	{
		// check to make sure the changes are real and need to be saved as a migration
		if (!($newModel || $this->hasChanges($newAttrs, $oldAttrs)))
			return;

		$up = $this->getTableRowReplaceSql('phoebe_class', $newAttrs);
		$down = '';
		if ($newModel) {
			$down = "DELETE FROM `phoebe_class` WHERE `phoebe_type`='$newAttrs[phoebe_type]' AND `class_type`='$newAttrs[class_type]' AND `version`=$newAttrs[version];";
		} else {
			$down = $this->getTableRowReplaceSql('phoebe_class', $oldAttrs);
		}
		$this->storeMigration($up, $down);
	}

	/**
	 * Check to see if the changes warrant creating a migration.
	 * Override this in cases where you need to do more than a simple check and
	 * use this for the initial simple test
	 *
	 * @param array $newAttrs  the new values
	 * @param array $oldAttrs  the old values
	 * @return boolean
	 */
	protected function hasChanges($newAttrs, $oldAttrs)
	{
		// do an overall check on values for simple cases
		$newAttrsMd5 = md5(json_encode($newAttrs));
		$oldAttrsMd5 = md5(json_encode($oldAttrs));
		return ($newAttrsMd5 != $oldAttrsMd5);
	}

	/**
	 * Set a date to now if it is passed in null
	 * @param datetime $date
	 * @return datetime
	 */
	private function setDateToNowOnEmpty($date)
	{
		return (empty($date) ? date('Y-m-d H:i:s') : $date);
	}

}