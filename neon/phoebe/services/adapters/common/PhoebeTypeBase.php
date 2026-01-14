<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2018- Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\phoebe\services\adapters\common;

use neon\phoebe\interfaces\IPhoebeType;
use neon\phoebe\services\adapters\common\PhoebeBase;
use neon\phoebe\services\adapters\common\models\PhoebeClass as ClassModel;
use neon\phoebe\services\adapters\common\models\PhoebeObject as ObjectModel;
use neon\core\helpers\Hash;

/**
 * The PhoebeBase provides default or phoebe specific implmentations for certain
 * methods. It knows what type of phoebe interface it is managing
 */
class PhoebeTypeBase extends PhoebeBase
implements
	IPhoebeType
{
	/** ------------------------------------------ **/
	/** ---------- Phoebe Type Methods ---------- **/

	/** -------------------------------------- **/
	/** ------------ Class Methods ----------- **/

	/**
	 * @inheritdoc
	 */
	public function listClasses(&$paginator=[], $filters=[], $orderBy=[], $includeDeleted=false)
	{
		// check the inputs
		$paginator = $this->createPaginator($paginator);
		$orderBy = count($orderBy)>0 ? $orderBy : 'label ASC';
		$filters = array_intersect_key($filters, array_flip(['class_type','label','description','module']));
		if (isset($filters['class_type']))
			$filters['class_type'] = $this->canonicaliseRef($filters['class_type']);

		/*
		 * This slightly unusual query according to MySQL is an efficient way to find the highest version of
		 * something where there may be multiple related rows in the database. By joining on itself and
		 * searching for p1.version < p2.version then for all rows except the one with the highest version
		 * there will be a row with a higher value. Therefore p2.version is not null. However only on the
		 * highest version will p2.version be null (none higher) therefore this condition yields the
		 * right row.
		 *
		 * Efficiency of this query is claimed to be better than a subquery but if not then another approach
		 * is needed.
		 */
		$query = new \yii\db\Query();
		$query->from('phoebe_class p1')
			->leftJoin('phoebe_class p2', "p1.phoebe_type=p2.phoebe_type AND p1.class_type=p2.class_type AND p1.version<p2.version")
			->where('p2.version IS NULL AND p1.phoebe_type=\''.$this->getPhoebeType().'\'');
		$select = ['p1.`phoebe_type`', 'p1.`class_type`', 'p1.`label`', 'p1.`description`', 'p1.`module`', 'p1.`version`'];
		if ($includeDeleted) {
			$select[] = 'p1.`deleted`';
		} else {
			$query->andWhere(['p1.deleted'=>0]);
		}
		$query->select($select);
		foreach ($filters as $key=>$value)
			$query->andWhere(['like', "p1.$key", $value]);
		if ($paginator['start']==0)
			$paginator['total'] = $query->count();

		// and get the results
		$results = $query->offset($paginator['start'])
			->limit($paginator['length'])
			->orderBy($orderBy)
			->all();

		// finally index them by the class_type as this is useful for changes in other classes
		$return = [];
		foreach ($results as $r)
			$return[$r['class_type']] = $r;
		return $return;
	}

	/**
	 * @inheritdoc
	 */
	public function addClass(&$classType, $module='')
	{
		$classType = $this->canonicaliseRef($classType);
		if (empty($classType))
			throw new \RuntimeException("Invalid classType $classType");

		// create a model for the class
		$model = $this->createPhoebeClassModel($classType, $module);
		$class = null;
		try {
			if ($model->save()) {
				$class = $this->createIPhoebeClass($model);
			} else
				throw new \Exception('Errors on save');
		} catch (\Exception $e) {
			throw new \RuntimeException('Failed to create model. Exception was "'.$e->getMessage().'". Model errors were '.print_r($model->errors, true));
		}
		$this->createAddClassMigration($model);
		return $class;
	}

	/**
	 * @inheritdoc
	 */
	public function getClass($classType, $version=null, $classOverrideCriteria=[])
	{
		$classType = $this->canonicaliseRef($classType);
		if (empty($classType))
			return null;

		$model = $this->getClassModel($classType, $version);
		if ($model) {
			$class = $this->createIPhoebeClass($model);
			if ($classOverrideCriteria !== false) {
				$override = $class->findClassOverride($classOverrideCriteria);
				if ($override)
					$class->applyClassOverrideByDefinition($override['overrides']);
			}
			return $class;
		}
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function getClasses($classTypes)
	{
		$models = $this->getClassModels($classTypes);
		$classes = array();
		foreach ($models as $type => $model)
			$classes[$type] = $model ? $this->createIPhoebeClass($model) : null;
		return $classes;
	}

	/**
	 * @inheritdoc
	 */
	public function listClassVersions($classType)
	{
		$classType = $this->canonicaliseRef($classType);
		if (!empty($classType)) {
			return ClassModel::find()
				->where([
					'phoebe_type'=>$this->getPhoebeType(),
					'class_type'=>$classType
				])->orderBy(['version'=>SORT_DESC])
				->asArray()
				->all();
		}
		return [];
	}

	/** -------------------------------------- **/
	/** ---------- Instance Methods ---------- **/

	/**
	 * @inheritdoc
	 */
	public function listObjects($classType, &$paginator=[], $filters=[], $orderBy=[], $additionalFields=[])
	{

		// check the inputs
		$classType = $this->canonicaliseRef($classType);
		$paginator = $this->createPaginator($paginator);
		$orderBy = count($orderBy)>0 ? $orderBy : 'created DESC';
		$filters = array_intersect_key($filters, array_flip(['version','created','updated']));

		$query = ObjectModel::find()->where([
			'phoebe_type' => $this->getPhoebeType(),
			'class_type' => $classType
		]);
		$select = ['uuid','phoebe_type','class_type','version','created','updated'];

		if (in_array('data', $additionalFields))
			$select[] = 'serialised_data';
		if (in_array('deleted', $additionalFields)) {
			$select[] = 'deleted';
		} else {
			$query->andWhere(['deleted'=>0]);
		}
		$query->select($select);

		foreach ($filters as $key=>$value)
			$query->andWhere(['=', $key, $value]);
		if ($paginator['start']==0)
			$paginator['total'] = $query->count();
		// and get the results
		return $query->offset($paginator['start'])
			->limit($paginator['length'])
			->orderBy($orderBy)
			->asArray()
			->all();
	}

	/**
	 * @inheritdoc
	 */
	public function addObject($classType, $classOverrideCriteria=[])
	{
		$class = $this->getClassFromType($classType);

		// create a model for the object
		$model = $this->createPhoebeObjectModel($classType);
		$model->version = $class->version;

		// ensure all save failures result in an exception
		try {
			if ($model->save()) {
				$phoebeObject = $this->createIPhoebeObject($model);
				// work out what class override needs to be applied
				if ($classOverrideCriteria !== false) {
					$override = $phoebeObject->findClassOverride($classOverrideCriteria, ['uuid']);
					if ($override)
						$model->override_uuid = $override['uuid'];
					$model->save();
				}
				return $phoebeObject;
			} else
				throw new \Exception('Errors on save');
		} catch (\Exception $e) {
			throw new \RuntimeException('Failed to create model. Exception was "'.$e->getMessage().'". Model errors were '.print_r($model->errors, true));
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getObject($objectId)
	{
		$query = ObjectModel::find()->where([
			'phoebe_type'=>$this->getPhoebeType(),
			'uuid'=>$objectId
		]);
		$model = $query->one();

		return $model ? $this->createIPhoebeObject($model) : null;
	}

	/** --------------------------------------- **/
	/** ---------- Protected Methods ---------- **/

	/**
	 * Check that a class type is valid and get one if so
	 * @param type $classType
	 * @throws \RuntimeException
	 */
	protected function getClassFromType($classType)
	{
		$classType = $this->canonicaliseRef($classType);
		if (empty($classType))
			throw new \RuntimeException("Invalid classType '$classType'");

		// check a class of this classType exists
		$class = $this->getClassModel($classType);
		if (!$class)
			throw new \RuntimeException("No class of type '$classType' available");
		return $class;
	}

	/**
	 * Create a partially filled in PhoebeClass active record model.
	 * Override this if you want to change the default values created
	 *
	 * @param string $classType  the class type to create
	 * @param string $module  the associated module if one
	 * @return neon\phoebe\services\adapters\common\models\PhoebeClass
	 */
	protected function createPhoebeClassModel($classType, $module)
	{
		$model = new ClassModel();
		$model->phoebe_type = $this->getPhoebeType();
		$model->class_type = $classType;
		$model->version = 1;
		$model->module = $module;
		$model->version_locked = 0;
		$model->volatile = 0;
		$model->object_history = 0;
		$model->deleted = 0;
		$model->created = date('Y-m-d H:i:s');
		return $model;
	}

	/**
	 * Create an IPhoebObject and associate the model with it
	 * @param neon\phoebe\services\adapters\common\models\PhoebeObject $model
	 * @return neon\phoebe\interfaces\IPhoebeObject
	 */
	protected function createIPhoebeObject($model)
	{
		$object = new $this->phoebeObject([
			'phoebeClass'=>$this->phoebeClass,
			'phoebeObject'=>$this->phoebeObject
		]);
		$object->setModel($model);
		return $object;
	}

	/**
	 * Create a partially filled in PhoebObject active record model
	 * @param string $classType  the class type to create
	 * @param string $uuid  the uuid if already generated otherwise if null, one
	 *   created automatically or if false then it is left as null.
	 * @return neon\phoebe\services\adapters\common\models\PhoebeObject
	 */
	protected function createPhoebeObjectModel($classType, $uuid=null)
	{
		// create a model for the class
		$model = new ObjectModel();
		$model->uuid = ($uuid ? $uuid : ($uuid !== false ? Hash::uuid64() : null));
		$model->phoebe_type = $this->getPhoebeType();
		$model->class_type = $classType;
		$model->version = 1;
		$model->created = date('Y-m-d H:i:s');
		$model->deleted = 0;
		$model->override_uuid = null;
		return $model;
	}


	/**
	 * Create a migration because of creation of a phoebeClass
	 * @param object $model
	 */
	protected function createAddClassMigration($model)
	{
		// get the whole entry for the model for full migration
		$phoebeType = $model->phoebe_type;
		$classType = $model->class_type;
		$version = $model->version;
		$data = ClassModel::find()
			->where([
				'phoebe_type'=>$phoebeType,
				'class_type'=>$classType,
				'version'=>$version
			])
			->asArray()
			->one();
		$up = $this->getTableRowReplaceSql('phoebe_class', $data);
		$down = "DELETE FROM `phoebe_class` WHERE `phoebe_type`='$phoebeType' AND `class_type`='$classType' AND `version`=$version;";
		$this->storeMigration($up, $down);
	}

}