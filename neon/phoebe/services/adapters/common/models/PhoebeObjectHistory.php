<?php

namespace neon\phoebe\services\adapters\common\models;

use Yii;

/**
 * This is the model class for table "phoebe_object_history".
 *
 * @property string $uuid
 * @property string $phoebe_type  - the phoebe_type it belongs to (e.g. daedalus)
 * @property string $class_type - the class_type it belong to (e.g. table name)
 * @property integer $version - which class version it was created with
 * @property string $override_uuid
 * @property string $object_uuid
 * @property string $archived
 * @property string $serialised_data
 */
class PhoebeObjectHistory extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'phoebe_object_history';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['uuid', 'phoebe_type', 'class_type', 'object_uuid'], 'required'],
			[['version'], 'integer'],
			[['archived'], 'safe'],
			[['serialised_data'], 'safe'],
			[['uuid', 'object_uuid', 'override_uuid'], 'string', 'max' => 22],
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
			'object_uuid' => 'Object Uuid',
			'archived' => 'Archived',
			'serialised_data' => 'Serialised Data',
		];
	}
}
