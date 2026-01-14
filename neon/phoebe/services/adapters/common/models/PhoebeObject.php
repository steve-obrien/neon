<?php

namespace neon\phoebe\services\adapters\common\models;

/**
 * This is the model class for table "phoebe_object".
 *
 * @property string $uuid  - an identifier for the object
 * @property string $phoebe_type  - the phoebe_type it belongs to (e.g. daedalus)
 * @property string $class_type - the class_type it belong to (e.g. table name)
 * @property integer $version - which class version it was created with
 * @property string $override_uuid - the uuid of any override applied
 * @property datetime $created - when it was created
 * @property datetime $updated - when it was last updated
 * @property boolean $deleted - whether or not it is deleted
 * @property array $data - the specific data for the object type
 * @property string $serialised_data - serialised version of $data (use $data)
 */
class PhoebeObject extends \yii\db\ActiveRecord
{
	/**
	 * @var data  the object's unserialised data (not stored in database)
	 */
	public $data = [];

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'phoebe_object';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['uuid', 'phoebe_type', 'class_type'], 'required'],
			[['version', 'deleted'], 'integer'],
			[['created', 'updated'], 'safe'],
			[['data'], 'safe'],
			[['uuid', 'override_uuid'], 'string', 'max' => 22],
			[['phoebe_type', 'class_type'], 'string', 'max' => 50],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'uuid' => 'Uuid',
			'phoebe_type' => 'Phoebe Type',
			'class_type' => 'Class Type',
			'version' => 'Version',
			'override_uuid' => 'Override Uuid',
			'created' => 'Created',
			'updated' => 'Updated',
			'deleted' => 'Deleted',
			'data' => 'Data',
		];
	}

	/**
	 * @inheritdoc
	 */
	public function beforeSave($insert)
	{
		// data can only be an array and are stored as JSON
		if (is_array($this->data) && count($this->data))
			$this->serialised_data = json_encode($this->data);
		else
			$this->serialised_data = null;
		return parent::beforeSave($insert);
	}

	/**
	 * @inheritdoc
	 */
	public function afterFind()
	{
		// return data as decoded json or null
		if (is_string($this->serialised_data))
			$this->data = json_decode($this->serialised_data, true);
		parent::afterFind();
	}

	/**
	 * List the available filters
	 * @param array $filters  any field=>values to be searched
	 * @return array  the results
	 */
	public static function listObjects($query, $filters)
	{
		foreach ($filters as $key=>$filter) {
			$filters['phoebe_class.'.$key] = $filter;
			unset($filters[$key]);
		}
		$query = PhoebeObject::find()->select(['phoebe_object.uuid', 'phoebe_class.label'])
			->leftJoin('phoebe_class', 'phoebe_class.phoebe_type=phoebe_object.phoebe_type AND phoebe_class.class_type=phoebe_object.class_type');
		if (!empty($query))
			$query->where(['like', 'phoebe_class.label', $query]);
		if (count($filters))
			$query->andWhere($filters);
		$objects = $query->orderBy('phoebe_class.label')->asArray()->all();
		$results = [];
		foreach ($objects as $object)
			$results[$object['uuid']] = "$object[label]: $object[uuid]";
		return $results;
	}
}
