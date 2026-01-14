<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2018- Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\phoebe\services\adapters\common;

use yii\base\Component;
use neon\phoebe\interfaces\IPhoebeBase;
use neon\phoebe\services\adapters\common\models\PhoebeClass as ClassModel;
use neon\phoebe\services\adapters\common\models\PhoebeClassOverrides as ClassOverridesModel;
use neon\core\helpers\Hash;

/**
 * The PhoebeBase provides default or phoebe specific implmentations for certain
 * methods. It knows what type of phoebe interface it is managing
 */
class PhoebeBase extends Component
implements
	IPhoebeBase
{
	/**
	 * Maximum number of classes that will be listed in one go
	 */
	const CLASS_LIST_LIMIT = 100;

	/**
	 * @var string  the phoebeClass class to create
	 */
	public $phoebeClass = null;

	/**
	 * @var string  the PhoebeObject class to create
	 */
	public $phoebeObject = null;

	/**
	 * @var string  the associated phoebe type if one
	 */
	public $phoebeType = null;

	/**
	 * @var string  the associated class reference if one
	 */
	public $classType = null;

	/**
	 * @var integer  the associated class version if one
	 */
	public $classVersion = null;

	/**
	 * @var object  the phoebe model for this class
	 */
	private $_model = null;


	/** ------------------------------------------ **/
	/** ---------- Phoebe Base Methods ----------- **/

	/**
	 * @inheritdoc
	 */
	public function toArray()
	{
		$model =  $this->getModel();
		return $model ? $model->toArray() : [];
	}

	/**
	 * @inheritdoc
	 */
	public function canonicaliseRef($ref)
	{
		return preg_replace("/[^a-z0-9_]/", '', strtolower(preg_replace("/ +/", '_', trim($ref))));
	}

	/** -------------------------------------------- **/
	/** ---------- Implementation Methods ---------- **/

	public function __get($name)
	{
		$attrs = $this->getModelAttributes();
		if (array_key_exists($name, $attrs))
			return $attrs[$name];
		return parent::__get($name);
	}

	public function __set($name, $value)
	{
		$model = $this->getModel();
		$attrs = $model->attributes();
		if (in_array($name, $attrs))
			$model->$name = $value;
		else
			parent::__set($name, $value);
	}

	/**
	 * Get the phoebe type. This will throw an exception if not yet set
	 *
	 * @return string
	 * @throws \InvalidConfigException
	 */
	protected function getPhoebeType()
	{
		if (empty($this->phoebeType))
			throw new \RuntimeException('The phoebeType needs to be set during construction');
		return $this->phoebeType;
	}

	/**
	 * Get the classType. This will throw an exception if not yet set
	 *
	 * @return string
	 * @throws \InvalidConfigException
	 */
	protected function getClassType()
	{
		if (empty($this->classType))
			throw new \RuntimeException('The classType needs to be set during construction');
		return $this->classType;
	}

	/**
	 * Get the classType. This will throw an exception if not yet set
	 *
	 * @return string
	 * @throws \InvalidConfigException
	 */
	protected function getClassVersion()
	{
		if (empty($this->classVersion))
			throw new \RuntimeException('The classVersion needs to be set during construction');
		return $this->classVersion;
	}

	/**
	 * Create a limit array from an iterator
	 * @param array $paginator  contains one or more from
	 *   'start', 'length', and 'total'
	 * @return array
	 */
	protected function createPaginator($paginator=[])
	{
		return [
			'start'=> (isset($paginator['start']) ? max(0, (int)$paginator['start']) : 0),
			'length'=> (isset($paginator['length']) ? max(1,min(self::CLASS_LIST_LIMIT,(int)$paginator['length'])) : self::CLASS_LIST_LIMIT),
			'total'=> (isset($paginator['total']) ? (int)$paginator['total'] : null)
		];
	}

	/**
	 * Set the phoebe model for this object
	 * @param object $model
	 */
	protected function setModel($model)
	{
		if ($model) {
			$this->_model = $model;
			$this->phoebeType = $model->phoebe_type;
			$this->classType = $model->class_type;
			$this->classVersion = $model->version;
		}
	}

	/**
	 * get the phoebe model for this object
	 * @return type
	 */
	protected function getModel()
	{
		if (!$this->_model)
			throw new \RuntimeException('The model has not yet been set');
		return $this->_model;
	}

	/**
	 * Get hold of the attributes on the model
	 * @return array  key=>value pairs
	 */
	protected function getModelAttributes()
	{
		if ($this->_model)
			return $this->_model->attributes;
		return [];
	}

	/**
	 * Mark a model as deleted
	 * @return boolean | array
	 *   Returns true on success and errors on failure
	 */
	protected function modelDelete()
	{
		$model = $this->getModel();
		$model->deleted = 1;
		if ($model->save())
			return true;
		return $model->errors;
	}

	/**
	 * Unmark a model as deleted
	 * @return boolean | array
	 *   Returns true on success and errors on failure
	 */
	protected function modelUndelete()
	{
		$model = $this->getModel();
		$model->deleted = 0;
		if ($model->save())
			return true;
		return $model->errors;
	}

	/**
	 * Get hold of a class model given the class type
	 * @param string $classType
	 * @param string $version
	 * @return \neon\phoebe\services\adapters\common\models\PhoebeClass
	 */
	protected function getClassModel($classType, $version=null)
	{
		$query = ClassModel::find()->where([
			'phoebe_type'=>$this->getPhoebeType(),
			'class_type'=>$classType
		]);
		if ($version)
			$query->andWhere(['version'=>$version]);
		else
			$query->limit(1)->orderBy('version DESC');

		return $query->one();
	}

	/**
	 * Create an IPhoebeClass and associate the model with it
	 * @param neon\phoebe\services\adapters\common\models\PhoebeClass $model
	 * @return neon\phoebe\interfaces\IPhoebeClass
	 */
	protected function createIPhoebeClass($model)
	{
		$object = new $this->phoebeClass([
			'phoebeClass'=>$this->phoebeClass,
			'phoebeObject'=>$this->phoebeObject
		]);
		$object->setModel($model);
		return $object;
	}

	/**
	 * Get hold of a class model given the class type
	 * @param array $classTypes  an array of classTypes
	 * @return [class_type] => $model
	 */
	protected function getClassModels(array $classTypes)
	{
		$classTypes = array_unique($classTypes);
		$phoebeType = $this->getPhoebeType();
		$models = ClassModel::find()
			->leftJoin('phoebe_class b',
				'phoebe_class.phoebe_type=b.phoebe_type AND phoebe_class.class_type=b.class_type AND phoebe_class.version < b.version')
			->where(['phoebe_class.phoebe_type' => $phoebeType])
			->andWhere(['in', 'phoebe_class.class_type', array_values($classTypes)])
			->andWhere('b.version IS NULL')
			->all();
		$classes = [];
		foreach ($models as $model)
			$classes[$model->class_type] = $model;
		return $classes;
	}

	/**
	 * store a set of migrations
	 * @param string $up
	 * @param string $down
	 */
	protected function storeMigration($up, $down)
	{
		neon()->dds->getIDdsAppMigrator()->storeMigration($up, $down);
	}

	/**
	 * Converts an array of field=>values from a database row into a REPLACE SQL statement
	 * @param string $table
	 * @param string $row  the
	 * @return string
	 */
	protected function getTableRowReplaceSql($table, $row)
	{
		if (!is_array($row))
			$row = $row->toArray();
		$fields = [];
		$values = [];
		foreach ($row as $f=>$v) {
			$fields[]=$f;
			$values[] = $this->pdoQuote($v);
		}
		if (count($fields))
			return "REPLACE INTO `$table` (`".(implode('`,`',$fields)).'`) VALUES ('.(implode(",",$values)).");";
		return null;
	}

	/**
	 * Get hold of an class override model that is connected with the class
	 * @param type $uuid
	 * @return type
	 */
	protected function getClassOverrideModel($uuid)
	{
		return ClassOverridesModel::find()
			->where([
				'uuid'=>$uuid,
				'phoebe_type' => $this->getPhoebeType(),
				'class_type' => $this->getClassType(),
				'version' => $this->getClassVersion()
			])
			->one();
	}

	/**
	 * Find the most appropriate override for the class and version
	 *
	 * @param array $filters  any of 'selector' and 'date'. If none is provided
	 *  then find the most appropriate now
	 * @param array $fields  use these to select a subset of fields returned.
	 * @return array  the override
	 */
	protected function findClassOverride($filters, $fields=[])
	{
		$selectFields = [
			'uuid', 'label', 'selector', 'is_active', 'active_from', 'active_to', 'serialised_overrides'
		];
		// convert a request for overrides to serialised_overrides
		if ($fields) {
			if (in_array('overrides', $fields))
				$fields[] ='serialised_overrides';
			$selectFields = array_intersect($selectFields, $fields);
		}
		$selector = !empty($filters['selector']) ? $filters['selector'] : '';
		$date = !empty($filters['date']) ? $filters['date'] : date('Y-m-d H:i:s');
		$result = ClassOverridesModel::find()
			->select($selectFields)
			->where([
				'phoebe_type' => $this->getPhoebeType(),
				'class_type' => $this->getClassType(),
				'version' => $this->getClassVersion(),
				'deleted'=>0,
				'selector'=>$selector,
				'is_active'=>1
			])
			->andWhere('active_from<=:activeFromMax', [':activeFromMax'=>$date])
			->andWhere('(active_to IS NULL OR active_to >= :activeToMin)', [':activeToMin'=>$date])
			->orderBy(['active_from'=>SORT_DESC])
			->limit(1)
			->one();
		return $result ? $result->toArray() : null;
	}

	/**
	 * protect the values for PDO insertion
	 * @param type $value
	 */
	private function pdoQuote($value)
	{
		if (is_array($value))
			$value = json_encode($value);
		if (is_null($value))
			return 'null';
		return neon()->db->pdo->quote($value);
	}

}