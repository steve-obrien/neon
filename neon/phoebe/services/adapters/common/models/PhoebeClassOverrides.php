<?php

namespace neon\phoebe\services\adapters\common\models;

use Yii;

/**
 * This is the model class for table "phoebe_class_overrides".
 *
 * @property string $uuid  - A unique uuid for the override
 * @property string $phoebe_type - The phoebe_type of the phoebe_class being overridden
 * @property string $class_type - The class_type of the phoebe_class being overridden
 * @property integer $version - The version number of the phoebe_class being overridden
 * @property string $label - A user facing label for this override
 * @property string $selector - A selector that may be used to further classify this override, e.g. user role
 * @property boolean $is_active - Whether or not the overrides are currently active
 * @property datetime $active_from - When the override is active from
 * @property datetime $active_to - When the override is active until
 * @property array $overrides - the overrides of the class
 * @property string $serialised_overrides - The serialised set of overrides which is type specific
 * @property datetime $created - When this was created
 * @property datetime $updated - Time of last change of the overrides
 * @property boolean $deleted - If true then the type has marked deleted. Overrides are kept for data integrity.
 */
class PhoebeClassOverrides extends \yii\db\ActiveRecord
{
	/**
	 * @var array  the class overrides
	 */
	public $overrides = [];

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'phoebe_class_overrides';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['phoebe_type', 'class_type', 'version', 'active_from'], 'required'],
			[['version'], 'integer'],
			[['created', 'updated'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
			[['overrides'], 'safe'],
			[['active_from', 'active_to'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
			[['is_active', 'deleted'], 'boolean'],
			[['phoebe_type', 'class_type'], 'string', 'max' => 50],
			[['label'], 'string', 'max' => 300],
			[['selector'], 'string', 'max' => 100],
			[['selector'], 'default', 'value'=>'']
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
			'label' => 'Label',
			'selector' => 'Override Selector',
			'is_active' => 'Is Override Active',
			'active_from' => 'Override active from date',
			'active_to' => 'Override active until date',
			'overrides' => 'The set of Overrides',
			'created' => 'Created',
			'updated' => 'Updated',
			'deleted' => 'Deleted',
		];
	}

	/**
	 * @inheritdoc
	 */
	public function save($runValidation = true, $attributeNames = null)
	{
		try {
			return parent::save($runValidation, $attributeNames);
		} catch (\Exception $e) {
			\Neon::error('Error during saving of override: '.print_r($e->getMessage()));
			$this->addError('save', ((neon()->debug) ? $e->getMessage() : 'Error during save of override'));
			return false;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function beforeSave($insert)
	{
		// active_from must be set
		// overrides can only be an array and are stored as JSON or already JSON
		if (is_array($this->overrides) && count($this->overrides))
			$this->serialised_overrides = json_encode($this->overrides);
		else {
			$this->serialised_overrides = null;
		}
		return parent::beforeSave($insert);
	}

	/**
	 * @inheritdoc
	 */
	public function afterFind()
	{
		// return overrides as decoded json or null
		if (is_string($this->serialised_overrides))
			$this->overrides = json_decode($this->serialised_overrides, true);
		parent::afterFind();
	}

	/**
	 * @inheritdoc
	 */
	public function toArray(array $fields=[], array $expand=[], $recursive=true)
	{
		$data = parent::toArray($fields, $expand, $recursive);
		unset($data['serialised_overrides']);
		$data['overrides'] = $this->overrides;
		return $data;
	}
}
