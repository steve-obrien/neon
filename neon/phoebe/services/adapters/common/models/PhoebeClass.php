<?php

namespace neon\phoebe\services\adapters\common\models;

use Yii;

/**
 * This is the model class for table "phoebe_class".
 *
 * @property string $phoebe_type - the phoebe type e.g. 'daedalus'
 * @property string $class_type - the class type e.g. table name
 * @property string $module - a module to further classify the classes
 * @property integer $version - what version of class
 * @property integer $version_locked - whether or not objects are locked to
 *   their creation version
 * @property integer $volatile - whether or not objects always get their data from
 *   the database or from the phoebe saved representation
 * @property string $label - a user facing label for the class
 * @property string $description - a user facing description for the class
 * @property boolean $object_history - whether or not object_history is required
 *   note: some adapters will ignore this (e.g. daedalus)
 * @property datetime $created - when the object was created
 * @property datetime $updated - when the object was last updated
 * @property boolean $deleted - if the object has been deleted
 * @property array $definition - the definition of the class
 * @property string $serialised_definition - serialised version of the definition
 */
class PhoebeClass extends \yii\db\ActiveRecord
{
	/**
	 * @var array  the class definition
	 */
	public $definition = [];

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'phoebe_class';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['phoebe_type', 'class_type', 'version'], 'required'],
			[['version'], 'integer'],
			[['created', 'updated'], 'safe'],
			[['definition'], 'safe'],
			[['phoebe_type', 'class_type', 'module'], 'string', 'max' => 50],
			[['label'], 'string', 'max' => 300],
			[['description'], 'string', 'max' => 2000],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'phoebe_type' => 'Phoebe Type',
			'class_type' => 'Class Type',
			'version' => 'Version',
			'module' => 'Module',
			'label' => 'Label',
			'description' => 'Description',
			'version_locked' => 'Version Locked',
			'volatile' => 'Volatile',
			'object_history' => 'Object History',
			'created' => 'Created',
			'updated' => 'Updated',
			'deleted' => 'Deleted',
			'definition' => 'Definition',
		];
	}

	/**
	 * @inheritdoc
	 */
	public function beforeSave($insert)
	{
		// definition can only be an array and are stored as JSON or already JSON
		if (is_array($this->definition) && count($this->definition))
			$this->serialised_definition = json_encode($this->definition);
		else {
			$this->serialised_definition = null;
		}
		return parent::beforeSave($insert);
	}

	/**
	 * @inheritdoc
	 */
	public function afterFind()
	{
		// return definition as decoded json or null
		if (is_string($this->serialised_definition))
			$this->definition = json_decode($this->serialised_definition, true);
		parent::afterFind();
	}

	public function toArray(array $fields=[], array $expand=[], $recursive=true)
	{
		$data = parent::toArray($fields, $expand, $recursive);
		unset($data['serialised_definition']);
		$data['definition'] = $this->definition;
		return $data;
	}

	/**
	 * List the classTypes available.
	 * @param string $query - a string based search
	 * @param array $filters - a set of field=>value filters
	 * @return array  the results
	 */
	public static function listClassTypes($query, $filters)
	{
		$query = PhoebeClass::find()->select(['class_type', 'label']);
		if (!empty($query))
			$query->where(['like', 'label', $query]);
		if (count($filters))
			$query->where($filters);
		$classes = $query->orderBy('label')->asArray()->all();
		$results = [];
		foreach ($classes as $class)
			$results[$class['class_type']] = $class['label'];
		return $results;
	}
}
